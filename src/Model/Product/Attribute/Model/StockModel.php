<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockModel extends AbstractModel
{

    /**
     * @var array
     */
    protected $_variationStockLevels;

    /**
     * @param array $productAttributes
     * @param Product $product
     * @return mixed
     */
    public function processVirtual($productAttributes, $product)
    {
        if ($productAttributes['stock_managed']) {
            if ($variationStockLevel = $this->_variationStockLevels($product->id)) {
                $stockStatus = 'Partial Stock';
                if ($variationStockLevel['in_stock'] == $variationStockLevel['total']) {
                    $stockStatus = 'In Stock';
                } elseif ($variationStockLevel['out_of_stock'] == $variationStockLevel['total']) {
                    $stockStatus = 'Out of Stock';
                }
                return $stockStatus . ' (' . $variationStockLevel['in_stock'] . '/' . $variationStockLevel['total'] . ')';
            } elseif ($productAttributes['stock_qty'] === 0) {
                return 'Out of Stock';
            } elseif ($productAttributes['stock_qty'] > 0) {
                return 'In Stock (' . $productAttributes['stock_qty'] . ')';
            }
        }
        return 'In Stock';
    }

    /**
     * @return array
     */
    public function columnsForVirtual()
    {
        return ['stock_managed', 'stock_qty'];
    }

    /**
     * @param Product\Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        if ($filterValue == 2) { // Partial stock
            $variationStockLevels = array_filter($this->_variationStockLevels(), function ($variationStockLevel) {
                return $variationStockLevel['in_stock'] > 0 && $variationStockLevel['in_stock'] != $variationStockLevel['total'];
            });
            return $query->has('variations')->whereIn('id', array_keys($variationStockLevels));
        } elseif ($filterValue == 1) { // In stock
            return $query->where(function ($qO) {
                return $qO->where('stock_managed', 0)->orwhere(function ($q) {
                    $q->doesnthave('variations')->where(function ($q) {
                        $q->where('stock_qty', '>', 0)->orWhereNull('stock_qty');
                    });
                })->orWhere(function ($q) {
                    $variationStockLevels = array_filter($this->_variationStockLevels(), function ($variationStockLevel) {
                        return $variationStockLevel['in_stock'] == $variationStockLevel['total'];
                    });
                    $q->whereIn('id', array_keys($variationStockLevels))->has('variations');
                });
            });
        } else { // Out of stock
            return $query->where(function ($qO) {
                $qO->where('stock_managed', 1)->where(function ($q) {
                    $q->where('stock_qty', 0)->doesnthave('variations');
                })->orWhere(function ($q) {
                    $variationStockLevels = array_filter($this->_variationStockLevels(), function ($variationStockLevel) {
                        return $variationStockLevel['out_of_stock'] == $variationStockLevel['total'];
                    });
                    $q->whereIn('id', array_keys($variationStockLevels))->has('variations');
                });
            });
        }
    }

    /**
     * @param int $productId
     * @return array
     */
    protected function _variationStockLevels($productId = null)
    {
        if (is_null($this->_variationStockLevels)) {
            $this->_variationStockLevels = [];
            $outOfStockVariations = Product\Variation::where('stock_qty', 0)->select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->pluck('total', 'product_id')->toArray();
            $totalVariations = Product\Variation::select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->pluck('total', 'product_id');
            foreach ($totalVariations as $vProductId => $variationCount) {
                $outOfStock = array_key_exists($vProductId, $outOfStockVariations) ? $outOfStockVariations[$vProductId] : 0;
                $this->_variationStockLevels[$vProductId] = [
                    'in_stock' => $variationCount - $outOfStock,
                    'out_of_stock' => $outOfStock,
                    'total' => $variationCount,
                ];
            }
        }
        return $productId
            ? (array_key_exists($productId, $this->_variationStockLevels) ? $this->_variationStockLevels[$productId] : null)
            : $this->_variationStockLevels;
    }

}
