<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Model\Tax\TaxRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 * @package CoasterCommerce\Core\Model\Order
 * @property Product $product
 * @property Product\Variation $variation
 * @property Order $order
 */
class Item extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_order_items';

    /**
     * @var array
     */
    public static $productTaxClassIds;

    /**
     * Limits the total value of an order item (qty * price)
     * @var int
     */
    protected $_priceLimit = 1000000;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variation()
    {
        return $this->belongsTo(Product\Variation::class, 'variation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @param Product $product
     */
    public function loadProduct(Product $product = null)
    {
        if ($product) {
            $this->setRelation('product', $product);
            $this->product_id = $product->id;
        }
    }

    /**
     * @param Product\Variation $variation
     */
    public function loadVariation(Product\Variation $variation = null)
    {
        if ($variation) {
            $this->setRelation('variation', $variation);
            $this->variation_id = $variation->id;
        }
    }

    /**
     * @return array
     */
    public function getDataArray($convertImages = false)
    {
        $data = $this->item_data ? json_decode($this->item_data, true) : [];
        if ($convertImages) {
            $fn = ($convertImages === 'src') ? 'getDataFileSrc' : 'getDataFileLink';
            foreach ($data as $option => $value) {
                if (stripos($value, 'file:') === 0) {
                    $data[$option] = $this->$fn($value);
                }
            }
        }
        return $data;
    }

    /**
     * @param bool $convertImages
     * @param string $key
     * @return string
     */
    public function getDataValue($key, $convertImages = false)
    {
        $dataArray = $this->getDataArray($convertImages);
        return array_key_exists($key, $dataArray) ? $dataArray[$key] : null;
    }

    /**
     * @param string $value
     * @return string
     */
    public function getDataFileLink($value)
    {
        $src = $this->getDataFileSrc($value);
        $parts = explode('/', $src);
        return '<a href="'.$src.'" target="_blank">file: ' . end($parts) . '</a>';
    }

    /**
     * @param string $value
     * @return string
     */
    public function getDataFileSrc($value)
    {
        return str_replace('file:', '', $value);
    }

    /**
     * @return int
     */
    public function getProductStock()
    {
        $productStock = null;
        if ($this->product_id && $this->product->stock_managed) {
            $productStock = $this->variation_id ? $this->variation->stock_qty : $this->product->stock_qty;
        }
        return $productStock;
    }

    /**
     * Requires order_id or order relation to be set to properly work with options and variants
     * @return int
     */
    public function getMaxStock()
    {
        if ($maxStock = $this->getProductStock()) {
            $otherBasketItems = $this->order ? $this->order->items->where('id', '!=', $this->id) : collect([]);
            if ($this->variation_id) {
                $maxStock -= $otherBasketItems->where('variation_id', $this->variation_id)->sum('item_qty');
            } else {
                $otherBasketItems = $otherBasketItems->where('product_id', $this->product_id);
                foreach ($otherBasketItems as $otherBasketItem) {
                    if ($otherBasketItem->variation_id && !is_null($otherBasketItem->variation->stock_qty)) {
                        continue;
                    }
                    $maxStock -= $otherBasketItem->item_qty;
                }
            }
        }
        return $maxStock;
    }

    /**
     * @param int $qty
     * @return $this
     */
    public function setQuantity($qty)
    {
        $this->item_request_qty = min(1000000, $qty);
        $this->item_qty = $this->item_request_qty;
        $maxStock = $this->getMaxStock();
        $this->item_qty = is_null($maxStock) ? $this->item_qty : min($maxStock, $this->item_qty);
        $this->updatePrice(); // recalculate product price based on new qty
        return $this;
    }

    /**
     *
     */
    public function updatePrice()
    {
        if ($this->product_id) {
            if ($this->variation) {
                $price = $this->variation->basePrice($this->item_qty);
                $discount = $this->variation->getDiscount($this->item_qty);
            } else {
                $price = $this->product->basePrice($this->item_qty);
                $discount = $this->product->getDiscount($this->item_qty);
            }
            /** @var ItemPrice $itemPriceModifiers */
            $itemPriceModifiers = app(ItemPrice::class); // allows for easy price modification
            $this->setBasePrice($itemPriceModifiers->getPrice($price, $this));
            $this->setDiscount($itemPriceModifiers->getDiscount($discount, $this), Setting::getValue('vat_catalogue_price'));
        }
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->item_name = $name;
        return $this;
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->item_sku = $sku;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data = [])
    {
        if ($this->variation_id) {
            // add variation options (& make sure they also override custom data with same keys)
            $data = $this->variation->variationArray() + $data;
        }
        $data = array_filter($data, function ($v) {
            return !is_null($v);
        });
        $this->item_data = $data ? json_encode($data) : null;
        return $this;
    }

    /**
     * @param bool $virtual
     * @return $this
     */
    public function setVirtual($virtual = true)
    {
        $this->item_virtual = $virtual;
        return $this;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setBasePrice($price)
    {
        $this->_setPriceField('base_price', $price);
        return $this;
    }

    /**
     * @param float $price
     * @param string $vat
     * @return $this
     */
    public function setDiscount($price, $vat = null)
    {
        $this->_setPriceField('discount', $price, $vat ?: Setting::getValue('vat_catalogue_discount_calculation'));
        return $this;
    }

    /**
     * @param float $price
     * @param $vat = null
     * @return $this
     */
    public function setPrice($price, $vat = null)
    {
        $this->_setPriceField('price', $price, $vat);
        return $this;
    }

    /**
     * @return float
     */
    public function getBasePrice()
    {
        return $this->_getPriceField('base_price');
    }

    /**
     * @param string $vat
     * @return float
     */
    public function getDiscount($vat = null)
    {
        return $this->_getPriceField('discount', $vat ?: Setting::getValue('vat_catalogue_discount_calculation'));
    }

    /**
     * @param string $vat
     * @return float
     */
    public function getPrice($vat = null)
    {
        return $this->_getPriceField('price', $vat);
    }

    /**
     * Returns main product image (will use variation image if available)
     * @return string
     */
    public function getImage()
    {
        $variationImage = $this->variation ? $this->variation->image : null;
        $productImage = $this->product ? $this->product->getImage() : null;
        return $variationImage ?: $productImage;
    }

    /**
     * @param Product $product
     * @param Product\Variation $variation
     * @param int $qty
     * @param array $options
     * @return $this
     */
    public function setProduct(Product $product, Product\Variation $variation = null, $qty = 1, $options = [])
    {
        $this->loadVariation($variation);
        $this->loadProduct($product);
        $this
            ->setQuantity($qty) // also sets price when a product is loaded
            ->setName($product->name)
            ->setData($options)
            ->setSku($variation && $variation->sku ? $variation->sku : $product->sku);
        return $this;
    }

    /**
     * @param string $field
     * @param string $vat
     * @return float
     */
    public function getCost($field, $vat)
    {
        return $this->_getPriceField($field, $vat);
    }

    /**
     * Populates empty qty, base_price, discount, price and total fields
     * @return $this
     */
    public function calculateTotal()
    {
        $this->item_qty = (int) $this->item_qty < 1 ? 1 : $this->item_qty; // set qty to 1 if not set
        $this->setBasePrice($this->getBasePrice() ?: 0); // set base_price to 0 if not already set
        $this->setDiscount(min(max($this->getDiscount(), 0), $this->getBasePrice())); // make sure discounts between 0 and base price or set to 0 if not already set
        $this->setPrice($this->getBasePrice() - $this->getDiscount(Setting::getValue('vat_catalogue_price')));
        if (Setting::getValue('vat_calculate_on') == 'unit') {
            $this->item_total_ex_vat = $this->getPrice('ex') * $this->item_qty;
            $this->item_total_inc_vat = $this->getPrice('inc') * $this->item_qty;
            $this->item_total_vat = $this->item_unit_vat * $this->item_qty;
        } else {
            $this->_setPriceField('total', $this->getPrice('ex') * $this->item_qty, 'ex'); // calc vat & inc vat price from ex vat price
        }
        $this->checkUBound();
        return $this;
    }

    /**
     * Checks price limit and lowers item qty if exceeded
     */
    protected function checkUBound()
    {
        if ($this->item_total_inc_vat >= $this->_priceLimit) {
            $previousQty = $this->item_qty;
            $this->item_qty = floor($this->_priceLimit * $this->item_qty / $this->item_total_inc_vat) ?: 1;
            if ($this->item_qty < $previousQty) {
                $this->calculateTotal();
            }
        }
    }

    /**
     * @param string $field
     * @param string $vat
     * @return float
     */
    protected function _getPriceField($field, $vat = null)
    {
        $vat = $vat ?: Setting::getValue('vat_catalogue_price');
        $fieldFullName = 'item_' . $field . ($vat == 'vat' ? '_vat' : ($vat == 'ex' ? '_ex_vat' : '_inc_vat'));
        return $this->$fieldFullName ?: 0;
    }

    /**
     * @param string $field
     * @param float $price
     * @param string $vat
     */
    protected function _setPriceField($field, $price, $vat = null)
    {
        $taxClassId = static::getTaxClassId($this->product_id);
        $itemClassModifiers = app(ItemTaxClass::class); // allows for easy tax class modification
        $taxClassId = $itemClassModifiers->getClass($taxClassId, $this);
        $vatRate = TaxRule::getRate($taxClassId);
        $vatField = 'item_' . $field . '_vat';
        $exVatField = 'item_' . $field . '_ex_vat';
        $incVatField = 'item_' . $field . '_inc_vat';
        $vat = $vat ?: Setting::getValue('vat_catalogue_price');
        if ($field == 'price') {
            $price = round($price, 2); // removes extra decimals that may have been caused by discounts
            $this->item_unit_vat = round($price * ($vatRate / (1 + ($vat == 'ex' ? 0 : $vatRate))), 2);
        }
        if ($vat == 'ex') {
            $this->$exVatField = $price;
            $this->$incVatField = $price * (1 + $vatRate);
        } else {
            $this->$exVatField = $price / (1 + $vatRate);
            $this->$incVatField = $price;
        }
        if ($field == 'total' && Setting::getValue('vat_calculate_on') != 'order') {
            // round off price fields once vat is calculated
            $this->$vatField = round($price * ($vatRate / (1 + ($vat == 'ex' ? 0 : $vatRate))), 2);
            $this->$exVatField = round($this->$exVatField, 2);
            $this->$incVatField = round($this->$incVatField, 2);
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!array_key_exists('preserve_prices', $options) || !$options['preserve_prices']) {
            $this->calculateTotal();
        }
        return parent::save($options);
    }

    /**
     * @param int $productId
     * @return int
     */
    public static function getTaxClassId($productId)
    {
        $defaultClassId = (int) Setting::getValue('vat_tax_class');
        if (is_null(static::$productTaxClassIds)) {
            static::$productTaxClassIds = (new Product)->newModelQuery()->where('tax_class_id', '!=', $defaultClassId)->pluck('tax_class_id', 'id')->toArray();
        }
        return array_key_exists($productId, static::$productTaxClassIds) ? static::$productTaxClassIds[$productId] : $defaultClassId;
    }

}
