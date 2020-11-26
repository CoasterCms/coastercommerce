<?php

namespace CoasterCommerce\Core\Model;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\Database\AttributeBuilder;
use CoasterCommerce\Core\Model\Order\Status;
use CoasterCommerce\Core\Model\Product\AdvancedPricing;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use CoasterCommerce\Core\Model\Product\Related;
use CoasterCommerce\Core\Model\Product\Variation;
use CoasterCommerce\Core\Model\Tax\TaxRule;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{

    public $table = 'cc_products';

    public $with = ['advancedPricing'];

    protected static $_indexPriceGroup;

    /**
     * @return BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'cc_category_products');
    }

    /**
     * @return HasMany
     */
    public function advancedPricing()
    {
        return $this->hasMany(AdvancedPricing::class);
    }

    /**
     * @return HasMany
     */
    public function variations()
    {
        return $this->hasMany(Variation::class)->orderBy('sort_value');
    }

    /**
     * @return BelongsToMany
     */
    public function relatedProducts()
    {
        return $this->belongsToMany(Product::class, (new Related())->getTable(), 'product_id', 'related_product_id')->withPivot(['related', 'up_sell', 'cross_sell']);
    }

    /**
     * Gets admin price
     * @param int $qty
     * @return float
     */
    public function basePrice($qty = 0)
    {
        $price = 0;
        foreach ($this->getTieredPricing() as $minQty => $tierPrice) {
            if ($minQty > $qty) {
                break;
            }
            $price = $tierPrice;
        }
        return $price;
    }

    /**
     * Gets display price for a product (with discount by default)
     * @param int $qty
     * @param string $vat for result - 'ex' or 'inc' (returns display price by default)
     * @param bool $withDiscount
     * @return float
     */
    public function getPrice($qty = 0, $vat = null, $withDiscount = true)
    {
        $price = $this->basePrice($qty);
        if ($withDiscount) {
            $price -= $this->getDiscount($qty);
        }
        $vat = $vat ?: Setting::getValue('vat_catalogue_display');
        $price = TaxRule::calculatePrice($this->tax_class_id, $price, Setting::getValue('vat_catalogue_price'), $vat);
        return round($price, 2);
    }

    /**
     * @param int $qty
     * @param float $price overrides standard base price value which discount is calculated on by default
     * @return float
     */
    public function getDiscount($qty = 0, $price = null)
    {
        $price = is_null($price) ? $this->basePrice($qty) : $price;
        /** @var Cart $cart */
        $cart = app(Cart::class);
        $promotions = Promotion::getActivePromotions($this, $cart->getCustomer(), $cart->order_coupon, $cart->getIgnoredPromotionIds());
        if ($promotions->count()) {
            $priceAfterVAT = TaxRule::calculatePrice($this->tax_class_id, $price, Setting::getValue('vat_catalogue_price'), Setting::getValue('vat_catalogue_discount_calculation'));
            $priceAfterPromotionsVAT = $priceAfterVAT;
            foreach ($promotions as $promotion) {
                $priceAfterPromotionsVAT = $promotion->applyDiscount($priceAfterPromotionsVAT);
            }
            $priceAfterPromotions = TaxRule::calculatePrice($this->tax_class_id, $priceAfterPromotionsVAT, Setting::getValue('vat_catalogue_discount_calculation'), Setting::getValue('vat_catalogue_price'));
            return max($price - $priceAfterPromotions, 0);
        }
        return 0;
    }

    /**
     * Will return true if any variations are in stock, use stock_status for more detail
     * @return bool
     */
    public function inStock()
    {
        if ($this->stock_managed) {
            return stripos($this->stock_status,'Out of stock') === false;
        }
        return true;
    }

    /**
     * override customer group when retrieving base price
     * used in search price indexing
     * @param int $groupId
     */
    public static function setIndexPriceGroup($groupId)
    {
        static::$_indexPriceGroup = $groupId;
    }

    /**
     * @return array
     */
    public function getTieredPricing()
    {
        // load standard price
        $tieredPrices = [0 => $this->getAttribute('price')];
        // load advanced pricing
        if ($advancedPricing = $this->advancedPricing) {
            if (is_null(static::$_indexPriceGroup)) {
                /** @var Cart $cart */
                $cart = app(Cart::class);
                $groupId = $cart->getCustomer() ? $cart->getCustomer()->group_id : 0;
            } else {
                $groupId = static::$_indexPriceGroup;
            }
            foreach ($advancedPricing->whereIn('group_id', [$groupId, null]) as $advancedPrice) {
                if (array_key_exists($advancedPrice->min_quantity, $tieredPrices) && $tieredPrices[$advancedPrice->min_quantity] < $advancedPrice->price) {
                    continue;
                }
                $tieredPrices[$advancedPrice->min_quantity] = $advancedPrice->price;
            }
        }
        // sort by min quantity
        ksort($tieredPrices);
        // make sure tierPrices always decrease as quantity increases
        if (count($tieredPrices) > 1) {
            $bestPrice = $tieredPrices[0];
            $advancedPrices = array_slice($tieredPrices, 1);
            foreach ($advancedPrices as $minQty => $tierPrice) {
                if ($tierPrice > $bestPrice) {
                    unset($tieredPrices[$minQty]);
                    continue;
                }
                $bestPrice = $tierPrice;
            }
        }
        return $tieredPrices;
    }

    /**
     * Returns lowest price variation
     * @return float|mixed
     */
    public function fromPrice()
    {
        if ($this->variations->count()) {
            foreach ($this->variations as $variation) {
                /** @var Variation $variation */
                $minPrice = isset($minPrice) ? min($minPrice, $variation->getPrice()) : $variation->getPrice();
            }
            return $minPrice;
        }
        return $this->getPrice();
    }

    /**
     * Uses images attribute, also returns default
     * @return string
     */
    public function getImage()
    {
        $firstImage = $this->images ? $this->images->getFile(0) : null;
        if ($firstImage) {
            return $firstImage;
        }
        $defaultImages = [
            '/uploads/catalogue/product/_default/image.jpg',
            '/uploads/catalogue/product/_default/image.png'
        ];
        foreach ($defaultImages as $defaultImage) {
            if (file_exists(public_path($defaultImage))) {
                return $defaultImage;
            }
        }
        return 'https://dummyimage.com/600x400/000/fff&text=NoImage';
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getUrl($category = null)
    {
        return (($category && $category->exists) ? $category->getUrl() : null) . '/'  . $this->url_key;
    }

    /**
     * @return string
     */
    public function generateUniqueKey()
    {
        $categories = $this->categories->pluck('id')->toArray();
        $base_url_key = strtolower(preg_replace('/[^\da-z]+/i', '-', $this->name));
        $url_key = $base_url_key;

        $i = 0;

        /** @var CatalogueUrls $catalogueUrls */
        $catalogueUrls = app('coaster-commerce.catalog-urls');

        while ($conflicts = $catalogueUrls->productUrlConflicts($this->id, $url_key, $categories)) {
            $url_key = $base_url_key . '-' . ++$i;
        }

        return $url_key;
    }

    /**
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function save(array $options = [])
    {
        if (!$this->url_key) {
            $this->url_key = $this->generateUniqueKey();
        }

        $did_save = parent::save($options);
        /** @var CatalogueUrls $catalogueUrls */
        $catalogueUrls = app('coaster-commerce.catalog-urls');
        
        $catalogueUrls->setProductUrl($this->id, $this->url_key);

        if (!array_key_exists('reindex', $options) || $options['reindex']) {
            (new Product\SearchIndex())->reindexProduct($this);
        }

        return $did_save;
    }

    /**
     * Add null product attribute values to attribute array
     * @param array $options
     */
    public function finishSave(array $options)
    {
        $this->setRawAttributes($this->attributes, true);
        parent::finishSave($options);
    }

    /**
     * @param array $attributes
     * @param bool $sync
     * @return Model
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $productAttributes = AttributeCache::getProductAttributeNullArray();
        return parent::setRawAttributes($attributes + $productAttributes, $sync);
    }

    /**
     * @param array $columns
     * @throws Exception
     */
    public function loadProductAttributes($columns = ['*'])
    {
        $productAttributes = [];
        // only load attribute codes based on columns
        $loadAll = $columns == ['*'];
        $loadAttributes = AttributeCache::getProductAttributesArray();
        if (!$loadAll) {
            $columns = AttributeCache::getRequiredAttributes($columns);
            $loadAttributes = array_intersect_key($loadAttributes, array_fill_keys($columns, null));
        }
        // get value depending on attribute type
        $eavValues = $this->_prepareEavTypeValues();
        $virtualAttributes = [];
        foreach ($loadAttributes as $productAttribute) {
            if ($productAttribute['type'] == 'virtual') {
                $virtualAttributes[$productAttribute['code']] = $productAttribute['model'];
                continue;
            } elseif ($productAttribute['type'] == 'eav') {
                $value = array_key_exists($productAttribute['eav']['datatype'], $eavValues) && array_key_exists($productAttribute['id'], $eavValues[$productAttribute['eav']['datatype']]) ?
                    $eavValues[$productAttribute['eav']['datatype']][$productAttribute['id']] : null;
            } else {
                $value = array_key_exists($productAttribute['code'], $this->attributes) ?
                    $this->attributes[$productAttribute['code']] : null;
            }
            // run mutation function on value if an attribute model has been set
            $productAttributes[$productAttribute['code']] = AttributeCache::$modelTypes
                ->databaseToCollection($productAttribute['model'], $value);
        }
        // process virtual last so that default and eav values are populated by this point
        $productAttributesWithNull = $productAttributes + AttributeCache::getProductAttributeNullArray();
        foreach ($virtualAttributes as $virtualAttribute => $virtualModel) {
            $productAttributes[$virtualAttribute] = AttributeCache::$modelTypes->processVirtual($virtualModel, $this, $productAttributesWithNull);
        }
        // load null values and sync original
        if ($loadAll) {
            $this->setRawAttributes($productAttributes, true);
        } else {
            $this->attributes = [];
            parent::setRawAttributes($productAttributes, true);
        }
    }

    /**
     * @return array
     */
    protected function _prepareEavTypeValues()
    {
        $eavAttributes = [];
        $loadedEavRelations = array_intersect_key(AttributeCache::$eavTypes->relationClasses(), $this->getRelations());
        foreach ($loadedEavRelations as $relation => $eavModel) {
            $type = AttributeCache::$eavTypes->relationType($relation);
            // load into datatype array, used when loading attribute values,
            // makes sure attribute value is loaded from correct datatype table (may be leftover data if a datatype has been changed)
            $eavAttributes[$type] = [];
            foreach ($this->getRelation($relation) as $eavRow) {
                $eavAttributes[$type][$eavRow->attribute_id] = $eavRow->value;
            }
        }
        return $eavAttributes;
    }

    /**
     * @param  Builder  $query
     * @return AttributeBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new AttributeBuilder($query);
    }

    /**
     * @param array $models
     * @return Product\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Product\Collection($models);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key == 'price') {
            return $this->getPrice();
        }
        return parent::__get($key);
    }

    /**
     * @return array
     */
    public function toAttributeArray()
    {
        return $this->attributesToArray();
    }

    /**
     * @param string $relation
     * @param mixed $value
     * @return Model
     */
    public function setRelation($relation, $value)
    {
        if ($relation == 'variations') {
            foreach ($value as $item) {
                $item->setRelation('product', $this); // helps on price checks as variation has to access product model
            }
        }
        return parent::setRelation($relation, $value);
    }

    /**
     * Dynamic eav relations (used when non eager loaded)
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $parameters)
    {
        $eavRelations = AttributeCache::$eavTypes->relationClasses();
        if (array_key_exists($method, $eavRelations)) {
            return $this->hasMany($eavRelations[$method]);
        }
        return parent::__call($method, $parameters);
    }

    /**
     * Return all configured options for product variations by attribute
     * @return array
     */
    public function variationAttributeValues()
    {
        $attributeValues = [];
        foreach ($this->variations as $variation) {
            /** @var Variation $variation */
            foreach ($variation->variationArray() as $attribute => $value) {
                $attributeValues[$attribute][] = $value;
            }
        }
        foreach ($attributeValues as $attribute => $values) {
            $attributeValues[$attribute] = array_unique($values);
        }
        return $attributeValues;
    }

    /**
     * @param int $limit
     * @return static[]|Collection
     */
    public static function bestSellers($limit = 10)
    {
        $completeCodes = Status::completeStatuses();
        $orderIds = Order::whereIn('order_status', $completeCodes)->pluck('id')->toArray();
        $productCounts = DB::table('cc_order_items')->whereIn('order_id', $orderIds)->select(DB::raw('count(*) as cart_count, product_id'))->groupBy('product_id')->orderBy('cart_count', 'desc')->get();
        $productIds = $productCounts->map(function($productCount) {
            return $productCount->product_id;
        })->toArray();
        return  (new static)->whereIn('id', $productIds)->limit($limit)->get()->sortBy(function() use($productIds) {
            return array_search('id', $productIds);
        });
    }

}
