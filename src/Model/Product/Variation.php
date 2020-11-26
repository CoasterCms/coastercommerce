<?php

namespace CoasterCommerce\Core\Model\Product;

use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Model\Tax\TaxRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Variation
 * @package CoasterCommerce\Core\Model\Product
 * @property Product $product
 */
class Variation extends Model
{

    public $table = 'cc_product_variations';

    /**
     * @var array
     */
    // product needed to stop recursion in toArray() as product has variation model relation
    // others used to cleanup frontend array
    protected $hidden = ['variation', 'product', 'product_id', 'enabled', 'price', 'fixed_price', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return array
     */
    public function variationArray()
    {
        return @json_decode($this->variation, true) ?: [];
    }

    /**
     * Gets admin price
     * @param int $qty
     * @return float
     */
    public function basePrice($qty = 0)
    {
        $productBasePrice = $this->fixed_price ? 0 : $this->product->basePrice($qty);
        return $productBasePrice + $this->price;
    }

    /**
     * Gets the display price for a product (with discount by default)
     * @param int $qty
     * @param string $vat for result - 'ex' or 'inc'
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
        $price = TaxRule::calculatePrice($this->product->tax_class_id, $price, Setting::getValue('vat_catalogue_price'), $vat);
        return round($price, 2);
    }

    /**
     * @param int $qty
     * @param int $price
     * @return float
     */
    public function getDiscount($qty = 0, $price = null)
    {
        $price = is_null($price) ? $this->basePrice($qty) : $price;
        return $this->product->getDiscount($qty, $price);
    }

    /**
     * @return bool
     */
    public function inStock()
    {
        if ($this->product->stock_managed) {
            return is_null($this->stock_qty) ?: !!$this->stock_qty;
        }
        return true;
    }

    /**
     * When converting model to array (for frontend data - json)
     * @return array
     */
    public function attributesToArray()
    {
        $attributesArray = parent::attributesToArray();
        if ($this->product) {
            $attributesArray += [
                'item_base_price' => $this->basePrice(),
                'item_price' => $this->getPrice(),
                'item_discount' => $this->getDiscount(),
                'variation' => $this->variationArray() // converts text to array
            ];
            foreach (['stock_qty', 'weight', 'sku'] as $field) {
                $attributesArray[$field] = is_null($attributesArray[$field]) ? $this->product->$field : $attributesArray[$field];
            }
        }
        return $attributesArray;
    }

}
