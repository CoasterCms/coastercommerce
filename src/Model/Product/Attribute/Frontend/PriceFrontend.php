<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Currency\Format;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Variation;
use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PriceFrontend extends NumberFrontend
{

    protected $_inputView = 'price';

    protected $_filterView = 'number';

    protected $_variationFixedPrices;

    protected $_variationAdjustedPrices;

    /**
     * @param Attribute $attribute
     * @return array
     */
    public function dataTableColumnConf($attribute)
    {
        return parent::dataTableColumnConf($attribute) + ['type' => 'price'];
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        // returns product price if no variations, or min price if variations exist
        return (string) new Format($this->_getVariationPrice($value, $id));
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        if (is_array($filterValue) && (!is_null($filterValue['from']) || !is_null($filterValue['to']))) {
            $query->where(function (Builder $q) use($attribute, $filterValue) {
                // use product price (if no variations)
                $q->where(function (Builder $q2) use($attribute, $filterValue) {
                    $q2->whereDoesntHave('variations');
                    if (!is_null($filterValue['from']) && !is_null($filterValue['to'])) {
                        $q2->whereBetween($attribute->code, [$filterValue['from'], $filterValue['to']]);
                    } elseif (!is_null($filterValue['from'])) {
                        $q2->where($attribute->code, '>', $filterValue['from']);
                    } elseif (!is_null($filterValue['to'])) {
                        $q2->where($attribute->code, '<', $filterValue['to']);
                    }
                });
                // use variation prices
                if ($pdIds = $this->_filterQueryVariationsProductIds($filterValue)) {
                    $q->orWhereIn('id', $pdIds);
                }
            });
        }
        return $query;
    }

    /**
     * Returns all products ids where at least one variation matches price query
     * @param array $filterValue
     * @return array
     */
    protected function _filterQueryVariationsProductIds($filterValue)
    {
        $productTable = (new Product())->getTable();
        $variationTable = (new Variation())->getTable();
        $pIdsForFixedVariationsQ = DB::table($variationTable)
            ->select('product_id')
            ->where('fixed_price', 1)
            ->groupBy('product_id');
        $pIdsForAdjustedVariationsQ = DB::table($variationTable . ' as v')
            ->select(DB::raw('v.price+p.price as c_price, v.product_id'))
            ->where('v.fixed_price', 0)
            ->leftJoin($productTable . ' as p', 'p.id', '=', 'v.product_id');
        if (!is_null($filterValue['from'])) {
            $pIdsForFixedVariationsQ->where('price', '>=', $filterValue['from']);
            $pIdsForAdjustedVariationsQ->having('c_price', '>=', $filterValue['from']);
        }
        if (!is_null($filterValue['to'])) {
            $pIdsForFixedVariationsQ->where('price', '<=', $filterValue['to']);
            $pIdsForAdjustedVariationsQ->having('c_price', '<=', $filterValue['to']);
        }
        return array_unique(array_merge(
            $pIdsForFixedVariationsQ->pluck('product_id')->toArray(),
            $pIdsForAdjustedVariationsQ->pluck('v.product_id')->toArray()
        ));
    }

    /**
     * Returns minimum variation price (or base price ie. $value if no variations)
     * @param string $value
     * @param int $id
     * @return float
     */
    protected function _getVariationPrice($value, $id)
    {
        if (is_null($this->_variationFixedPrices)) {
            $this->_variationFixedPrices = (new Variation())->newModelQuery()->select(DB::raw('min(price) as min_price, product_id'))
                ->where('fixed_price', 1)->groupBy('product_id')->pluck('min_price', 'product_id')->toArray();
            $this->_variationAdjustedPrices = (new Variation())->newModelQuery()->select(DB::raw('min(price) as min_price, product_id'))
                ->where('fixed_price', 0)->groupBy('product_id')->pluck('min_price', 'product_id')->toArray();
        }
        if (array_key_exists($id, $this->_variationAdjustedPrices)) {
            $vValue = floatval($value) + $this->_variationAdjustedPrices[$id];
        }
        if (array_key_exists($id,  $this->_variationFixedPrices)) {
            $vValue = isset($vValue) ? min($vValue, $this->_variationFixedPrices[$id]) : $this->_variationFixedPrices[$id];
        }
        return $vValue ?? $value;
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @return string
     */
    public function submissionRules($attribute, $value)
    {
        $rules = explode('|', parent::submissionRules($attribute, $value));
        if (strpos($value, 'min:') === false) {
            $rules[] = 'min:0';
        }
        return implode('|', $rules);
    }

}
