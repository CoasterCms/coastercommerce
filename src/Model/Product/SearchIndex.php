<?php

namespace CoasterCommerce\Core\Model\Product;

use CoasterCommerce\Core\Contracts\Cart;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\CategoryProducts;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SearchIndex extends Model
{

    public $table = 'cc_product_search_index';

    public $timestamps = false;

    public static $indexableAttributes;

    public static $indexableSkuAttributeId;

    public static $validSearchParams;

    public static $numericFilters;

    /**
     * Returns sorted array with product ids and weights
     * Double weight is given for an exact match
     * @param string $term
     * @return array
     */
    public function findTermProductWeights($term)
    {
        if (is_null($term) || $term === '') {
            return [];
        }

        $coasterCmsSearchLog = '\CoasterCms\Models\PageSearchLog';
        if (class_exists($coasterCmsSearchLog)) {
            $coasterCmsSearchLog::add_term($term);
        }

        $indexTable = $this->getTable();
        $attributeTable = (new Attribute)->getTable();

        $termParts = array_filter(array_map('trim', explode(' ', $term)));
        $termParts = array_slice($termParts, 0, 5); // stop long queries
        $termQuery = [];
        $termValues = [];
        if (count($termParts) > 1) {
            $termQuery = ['(SELECT si.product_id, SUM(a.search_weight) AS weight FROM ' . $indexTable . '  si
                    LEFT JOIN ' . $attributeTable . ' a ON a.id = si.attribute_id AND a.search_weight > 0 
                    WHERE si.`value` LIKE ?
                    GROUP BY si.product_id)'];
            $termValues = ['%' . $term . '%'];
        }
        foreach ($termParts as $termPart) {
            $termQuery[] = '(
                    SELECT si.product_id, SUM(a.search_weight) AS weight FROM ' . $indexTable . ' si
                    LEFT JOIN ' . $attributeTable . ' a ON a.id = si.attribute_id AND a.search_weight > 0 
                    WHERE si.`value` LIKE ?
                    GROUP BY si.product_id)';
            $termValues[] = '%' . $termPart . '%';
        }

        /** @var DatabaseManager $db */
        $db = app('db');
        return $db->select(
            'SELECT u.product_id, SUM(u.weight) AS weight FROM (' .
            implode(' UNION ALL ', $termQuery) .') AS u
            GROUP BY u.product_id HAVING COUNT(*) >= ' .  count($termParts) . '
            ORDER BY weight DESC',
            $termValues
        );
    }

    /**
     * @param array $productIds
     * @param array $searchParams
     * @return array
     */
    public function filterResults($productIds, $searchParams)
    {
        if ($productIds) {
            $searchParams = array_diff_key($searchParams, ['q' => null, 'o' => null, 'd' => null]); // search query, order by and direction defaults
            if ($verifiedParams = array_intersect_key($searchParams, static::validSearchAttributeNames())) {
                foreach ($verifiedParams as $filterField => $filterValue) {
                    // remove empty filter params
                    $filterValue = is_array($filterValue) ? array_filter($filterValue, function ($filterValueEl) {
                        return !is_null($filterValueEl);
                    }) : $filterValue;
                    if (is_null($filterValue) || $filterValue === []) {
                        continue;
                    }
                    // then filter
                    if ($filterField == 'price') {
                        $matchingFilteredProductIds = $this->_filterPrice($filterValue);
                    } else if ($filterField == 'category') {
                        $matchingFilteredProductIds = $this->_filterCategory($filterValue);
                    } else {
                        $matchingFilteredProductIds = $this->_filterStandardAttribute($filterField, $filterValue);
                    }
                    $productIds = array_intersect($productIds, $matchingFilteredProductIds);
                }
            }
        }
        return $productIds;
    }

    /**
     * @return array
     */
    public static function validSearchAttributeNames()
    {
        if (is_null(static::$validSearchParams)) {
            static::$validSearchParams = ['price' => 'Price', 'category' => 'Category'] +
                static::indexableAttributes()->pluck('name','code')->toArray();
        }
        return static::$validSearchParams;
    }

    /**
     * @return array
     */
    public static function numericFilters()
    {
        if (is_null(static::$numericFilters)) {
            static::$numericFilters = array_merge(
                ['price'],
                static::indexableAttributes()->where('type', 'number')->pluck('code')->toArray()
            );
        }
        return static::$numericFilters;
    }

    /**
     * @param string $filterField
     * @param string $filterValue
     * @return array
     */
    public static function convertMinMaxStringToArray($filterField, $filterValue)
    {
        if (in_array($filterField, static::numericFilters()) && is_string($filterValue)) {
            if (preg_match('/([\d.]*)-([\d.]*)/', $filterValue, $matches)) {
                list($min, $max) = array_map('floatval', array_slice($matches,1));
                $filterValue = ['min' => $min, 'max' => $max];
                if ($matches[1] === '') {
                    unset($filterValue['min']);
                }
                if ($matches[2] === '') {
                    unset($filterValue['max']);
                }
            }
        }
        return $filterValue;
    }

    /**
     * @param string $filterValue
     * @return array
     */
    protected function _filterPrice($filterValue)
    {
        $priceIndexTable = (new Product\SearchIndex\Price())->getTable();
        $searchQuery = (new Product\SearchIndex\Price())->newQuery()
            ->whereNull($priceIndexTable . '.group_id')->whereNull($priceIndexTable . '.customer_id');
        if ($customer = app(Cart::class)->getCustomer()) {
            $searchQuery->leftJoin($priceIndexTable . ' as p_ig', function ($join) use($priceIndexTable, $customer) {
                $join->on('p_ig.product_id', '=', $priceIndexTable . '.product_id')
                    ->on('p_ig.group_id', '=', DB::raw($customer->group_id));
            })->whereNull('p_ig.customer_id')->leftJoin($priceIndexTable . ' as p_ic', function ($join) use($priceIndexTable, $customer) {
                $join->on('p_ic.product_id', '=', $priceIndexTable . '.product_id')
                    ->on('p_ic.customer_id', '=', DB::raw($customer->id));
            });
            $minPriceColumn = DB::raw('IFNULL(p_ic.min_price, IFNULL(p_ig.min_price, '.$priceIndexTable.'.min_price))');
            $maxPriceColumn = DB::raw('IFNULL(p_ic.max_price, IFNULL(p_ig.max_price, '.$priceIndexTable.'.max_price))');
        } else {
            $minPriceColumn = 'min_price';
            $maxPriceColumn = 'max_price';
        }
        $filterValue = static::convertMinMaxStringToArray('price', $filterValue);
        if (!is_array($filterValue)) {
            $filterValue = [];
        }
        if (array_key_exists('min', $filterValue)) {
            $searchQuery->where($maxPriceColumn, '>=', $filterValue['min']);
        }
        if (array_key_exists('max', $filterValue)) {
            $searchQuery->where($minPriceColumn, '<=', $filterValue['max']);
        }
        return $searchQuery->pluck($priceIndexTable . '.product_id')->toArray();
    }

    /**
     * @param string $filterValue
     * @return array
     */
    protected function _filterCategory($filterValue)
    {
        $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];
        $categories = Category::whereIn('id', $filterValue)->get();
        $categoryIds = []; // get all category ids to search, this will include anchored cats
        foreach ($categories as $category) {
            /** @var Category $category  */
            $categoryIds = array_merge($categoryIds, $category->getAnchoredCategoryIds());
        }
        return CategoryProducts::whereIn('category_id', array_unique($categoryIds))->pluck('product_id')->toArray();
    }

    /**
     * @param string $filterField
     * @param string $filterValue
     * @return array
     */
    protected function _filterStandardAttribute($filterField, $filterValue)
    {
        $searchQuery = (new static)->newQuery();
        $searchQuery->where('attribute_id', static::indexableAttributes()->where('code', $filterField)->first()->id);
        $filterValue = static::convertMinMaxStringToArray($filterField, $filterValue);
        if (is_array($filterValue)) {
            if (array_key_exists(0, $filterValue)) {
                // multiple options
                $searchQuery->whereIn('value', $filterValue);
            } else {
                // min/max
                if (array_key_exists('min', $filterValue)) {
                    $searchQuery->where('value', '>=', $filterValue['min']);
                }
                if (array_key_exists('max', $filterValue)) {
                    $searchQuery->where('value', '<=', $filterValue['max']);
                }
            }
        } else {
            $searchQuery->where('value', 'LIKE', '%' . $filterValue . '%');
        }
        return $searchQuery->pluck('product_id')->toArray();
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function filterOptions($productIds)
    {
        $filterOptions = [];
        $filters = static::indexableAttributes()->where('search_filter', 1)->pluck('id','code')->toArray();
        foreach ($filters as $filterCode => $attributeId) {
            $filterOptionValues = (new static)->whereIn('product_id', $productIds)->where('attribute_id', $attributeId)
                ->select(\DB::raw('count(*) as product_count, value'))->groupBy('value')->pluck('product_count', 'value')->toArray();
            if ($filterOptionValues) {
                $filterOptions[$filterCode] = $filterOptionValues;
            }
        }
        return $filterOptions;
    }

    /**
     * @param Product $product
     */
    public function reindexProduct($product)
    {
        $searchIndexData = collect();
        $this->_newRows($searchIndexData, $product);
        (new static)->where('product_id', $product->id)->delete();
        (new static)->insert($searchIndexData->toArray());
        (new SearchIndex\Price())->reindexProduct($product);
    }

    /**
     * @var array $productIds
     */
    public function reindexAll($productIds = null)
    {
        $searchIndexData = collect();
        $loadAttributeCodes = array_merge(
            ['id', 'price'],
            static::indexableAttributes()->pluck('code')->toArray()
        );
        $productQuery = Product::with(['variations', 'categories']);
        if ($productIds) {
            $productQuery->whereIn('id', $productIds);
        }
        $products = $productQuery->get($loadAttributeCodes);
        foreach ($products as $product) {
            $this->_newRows($searchIndexData, $product);
        }
        if ($productIds) {
            static::whereIn('product_id', $products->pluck('id')->toArray())->delete();
        } else {
            static::truncate();
        }
        $searchIndexData->chunk(100)->each(function ($searchIndexChunk) {
            static::insert($searchIndexChunk->toArray());
        });
        (new SearchIndex\Price())->reindexAll($products, !$productIds);
    }

    /**
     * @param Collection $searchIndexData
     * @param Product $product
     */
    protected function _newRows($searchIndexData, $product)
    {
        foreach (static::indexableAttributes() as $indexableAttribute) {
            $value = $product->{$indexableAttribute->code};
            if (!is_null($value) && $value !== '') {
                $value = $product->{$indexableAttribute->code};
                if (is_object($value)) {
                    continue;
                }
                if (is_array($value)) {
                    if ($indexableAttribute->code == 'variation_attributes') {
                        $values = [$this->_convertKeysAndValuesToString($value)];
                    } else {
                        $values = $this->_convertToStringAtOneDeep($value);
                    }
                } else {
                    $values = [$value];
                }
                foreach ($values as $value) {
                    if ($value) {
                        $searchIndexData->push([
                            'product_id' => $product->id,
                            'attribute_id' => $indexableAttribute->id,
                            'value' => strip_tags($value)
                        ]);
                    }
                }
            }
        }
        // add variations skus to search index (uses sku attribute weight)
        $skuAttributeId = static::indexableSkuAttributeId();
        if ($product->variations && $skuAttributeId) {
            $variationSkus = array_filter($product->variations->pluck('sku')->toArray());
            foreach ($variationSkus as $variationSku) {
                $searchIndexData->push([
                    'product_id' => $product->id,
                    'attribute_id' => $skuAttributeId,
                    'value' => $variationSku
                ]);
            }
        }
    }

    /**
     * @param array $array
     * @param int $depth
     * @return array|string
     */
    protected function _convertToStringAtOneDeep($array, $depth = 0)
    {
        $return = $depth < 1 ? [] : '';
        foreach ($array as $k => $v) {
            if (is_object($v)) {
                continue;
            }
            if (is_array($v)) {
                $appendToReturn = $this->_convertToStringAtOneDeep($v, $depth + 1);
            } else {
                $appendToReturn = $v;
            }
            if ($depth < 1) {
                $return[] = $appendToReturn;
            } else {
                $return .= $appendToReturn . ' ';
            }
        }
        return $return;
    }

    /**
     * @param array $array
     * @return string
     */
    protected function _convertKeysAndValuesToString($array)
    {
        $string = '';
        foreach ($array as $k => $v) {
            if ($k == 'display') continue; // variation attributes to stop display info being indexed
            $string .= $k . ' ';
            if (is_array($v)) {
                $string .= $this->_convertKeysAndValuesToString($v);
            } elseif (!is_object($v)) {
                $string .= $v . ' ';
            }
        }
        return $string;
    }

    /**
     * @return Collection
     */
    public static function indexableAttributes()
    {
        if (is_null(static::$indexableAttributes)) {
            static::$indexableAttributes = Attribute::where(function ($q) {
                $q->where('search_weight', '>', 0)->orWhere('search_filter', 1);
            })->get();
        }
        return static::$indexableAttributes;
    }

    /**
     * @return int
     */
    public static function indexableSkuAttributeId()
    {
        if (is_null(static::$indexableSkuAttributeId)) {
            $skuAttribute = static::indexableAttributes()->where('code', 'sku')->first();
            static::$indexableSkuAttributeId = $skuAttribute ? $skuAttribute->id : 0;
        }
        return static::$indexableSkuAttributeId;
    }

}
