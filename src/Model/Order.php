<?php

namespace CoasterCommerce\Core\Model;

use CoasterCommerce\Core\Events\OrderPlaced;
use CoasterCommerce\Core\Events\OrderPreCalculateTotal;
use CoasterCommerce\Core\Mailables\OrderMailable;
use CoasterCommerce\Core\Model\Order\Item;
use CoasterCommerce\Core\Model\Order\Payment;
use CoasterCommerce\Core\Model\Order\ShipmentTracking;
use CoasterCommerce\Core\Model\Order\Shipping;
use CoasterCommerce\Core\Model\Order\Status;
use CoasterCommerce\Core\Model\Product\Variation;
use CoasterCommerce\Core\Model\Tax\TaxRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Class Order
 * @package CoasterCommerce\Core\Model
 * @property Order\Item[]|Collection $items
 */
class Order extends Model
{

    /**
     * List of order states
     */
    const STATUS_QUOTE = 'quote';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @var string
     */
    protected $table = 'cc_orders';

    /**
     * @var array
     */
    protected $_ignoredPromotionIds;

    /**
     * @var array
     */
    public $dates = ['order_placed', 'payment_confirmed', 'shipment_sent'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipments()
    {
        return $this->hasMany(ShipmentTracking::class, 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Order\Item::class, 'order_id')->with(['product', 'variation']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Order\Address::class, 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Status::class, 'order_status', 'code');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return Order\Address|null
     */
    public function billingAddress()
    {
        return $this->addresses->where('type', '=', 'billing')->first();
    }

    /**
     * @return Order\Address|null
     */
    public function shippingAddress()
    {
        return $this->addresses->where('type', '=', 'shipping')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Order\Note::class, 'order_id');
    }


    /**
     * Get next order number if not set
     */
    public function generateOrderNumber()
    {
        $this->order_number = $this->order_number ?: '#' . Setting::nextOrderNumber();
    }

    /**
     * Generate unique order key
     */
    public function generateSecretKey()
    {
        if (!$this->order_key && $this->id) {
            $this->order_key = Str::random(25) . '-' . $this->id;
        }
    }

    /**
     * @return Shipping\AbstractShipping[]
     */
    public function getAvailableShippingMethods()
    {
        return (new Order\Shipping)->getAvailableMethods($this);
    }

    /**
     * @return Shipping\AbstractShipping
     */
    public function getShippingMethod()
    {
        return (new Order\Shipping)->getMethod($this->shipping_method, $this);
    }

    /**
     * @param string $shippingMethodId
     * @return Shipping\AbstractShipping
     */
    public function updateShippingMethod($shippingMethodId)
    {
        if ($shippingMethod = (new Order\Shipping)->getAvailableMethod($shippingMethodId, $this)) {
            $this->shipping_method = $shippingMethodId;
        } else {
            $this->shipping_method = null;
        }
        return $shippingMethod;
    }

    /**
     * @return Payment\AbstractPayment[]
     */
    public function getAvailablePaymentMethods()
    {
        return (new Order\Payment)->getAvailableMethods($this);
    }

    /**
     * @return Payment\AbstractPayment
     */
    public function getPaymentMethod()
    {
        return (new Order\Payment)->getMethod($this->payment_method, $this);
    }

    /**
     * @param string $paymentMethodId
     * @return Payment\AbstractPayment
     */
    public function updatePaymentMethod($paymentMethodId)
    {
        if ($paymentMethod = (new Order\Payment)->getAvailableMethod($paymentMethodId, $this)) {
            $this->payment_method = $paymentMethodId;
        } else {
            $this->payment_method = null;
        }
        return $paymentMethod;
    }

    /**
     * Populates subtotal price fields
     */
    public function calculateSubtotal()
    {
        $this->load('items'); // reload order items
        $itemVatField = 'item_total_vat';
        $itemExField = 'item_total_ex_vat';
        $itemIncField = 'item_total_inc_vat';
        $this->order_subtotal_vat = 0;
        $this->order_subtotal_ex_vat = 0;
        $this->order_subtotal_inc_vat = 0;
        foreach ($this->items as $item) {
            $this->order_subtotal_vat += $item->$itemVatField;
            $this->order_subtotal_ex_vat += $item->$itemExField;
            $this->order_subtotal_inc_vat += $item->$itemIncField;
        }
        if (Setting::getValue('vat_calculate_on') == 'order') {
            $this->_setPriceField('subtotal', $this->order_subtotal_ex_vat, 'ex'); // calc vat (ignores product tax classes)
        }
    }

    /**
     *
     */
    public function calculateShipping()
    {
        if ($shippingMethod = (new Order\Shipping)->getAvailableMethod($this->shipping_method, $this)) {
            $shippingRate = $shippingMethod->rate();
        } else {
            $shippingRate = 0;
            $this->shipping_method = null;
        }
        $this->_setPriceField('shipping', $shippingRate, Setting::getValue('vat_shipping_price'));
    }

    /**
     *
     */
    public function calculateDiscount()
    {
        $discountOn = Setting::getValue('vat_cart_discount_calculation');
        $shippingRate = $this->_getPriceField('shipping', $discountOn);
        $subTotal = $this->_getPriceField('subtotal', $discountOn);
        $promotions = Promotion::getActivePromotions(null, $this->customer, $this->order_coupon, $this->getIgnoredPromotionIds()); // cart only promotions
        $discountedShippingRate = $shippingRate;
        $discountedSubTotal = $subTotal;
        foreach ($promotions as $promotion) {
            if ($promotion->apply_to_subtotal) {
                $discountedSubTotal = $promotion->applyDiscount($discountedSubTotal);
            }
            if ($promotion->apply_to_shipping) {
                $discountedShippingRate = $promotion->applyDiscount($discountedShippingRate);
            }
        }
        // make sure subtotal & shipping discounts are less than or equal to the value they are discounting, then add together
        $this->_setPriceField('subtotal_discount', $discountedSubTotal > $subTotal ? $subTotal : ($subTotal - $discountedSubTotal), $discountOn);
        $this->_setPriceField('shipping_discount', $discountedShippingRate > $shippingRate ? $shippingRate : ($shippingRate - $discountedShippingRate), $discountOn);
    }

    /**
     * @return float
     */
    public function totalDiscount()
    {
        $vat = Setting::getValue('vat_cart_discount_calculation');
        return $this->_getPriceField('subtotal_discount', $vat) + $this->_getPriceField('shipping_discount', $vat);
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
     * Returns VAT for total order
     * @return float
     */
    public function totalVAT()
    {
        $vat = (float) $this->order_total_vat;
        if (!$this->order_vat_type && !$vat) {
            // backwards compatibility
            return $this->order_total_inc_vat - $this->order_total_ex_vat;
        }
        return $vat;
    }

    /**
     * Populates total
     */
    public function calculateTotal()
    {
        $this->calculateSubtotal();
        $this->calculateShipping();
        $this->calculateDiscount();
        $totalsOn = ['order_total_ex_vat' => 'ex', 'order_total_inc_vat' => 'inc', 'order_total_vat' => 'vat'];
        $this->order_vat_type = Setting::getValue('vat_calculate_on');
        foreach ($totalsOn as $totalField => $vat) {
            $this->$totalField =
                $this->_getPriceField('subtotal', $vat) -
                $this->_getPriceField('subtotal_discount', $vat) +
                $this->_getPriceField('shipping', $vat) -
                $this->_getPriceField('shipping_discount', $vat);
        }
    }

    /**
     * @param string $field
     * @param string $vat
     * @return float
     */
    protected function _getPriceField($field, $vat)
    {
        $fieldFullName = 'order_' . $field . ($vat == 'vat' ? '_vat' : ($vat == 'ex' ? '_ex_vat' : '_inc_vat'));
        return $this->$fieldFullName;
    }

    /**
     * @param string $field
     * @param float $price
     * @param string $vat
     */
    protected function _setPriceField($field, $price, $vat)
    {
        $taxClassId = Setting::getValue('vat_tax_class');
        $vatRate = TaxRule::getRate($taxClassId);
        $vatField = 'order_' . $field . '_vat';
        $exVatField = 'order_' . $field . '_ex_vat';
        $incVatField = 'order_' . $field . '_inc_vat';
        if ($vat == 'ex') {
            $this->$exVatField = $price;
            $this->$incVatField = $price * (1 + $vatRate);
        } else {
            $this->$exVatField = $price / (1 + $vatRate);
            $this->$incVatField = $price;
        }
        if ($field != 'subtotal' || Setting::getValue('vat_calculate_on') == 'order') { // only calc vat on subtotal if vat_calculate_on = order
            $this->$vatField = round($price * ($vatRate / (1 + ($vat == 'ex' ? 0 : $vatRate))), 2);
        }
        // make sure totals are exact amounts (stops total vat errors when total inc - ex)
        $this->$exVatField = round($this->$exVatField, 2);
        $this->$incVatField = round($this->$incVatField, 2);
    }

    /**
     * @param \Closure $fn
     * @param bool $runSetQuantity
     */
    public function recalculateItems($fn = null, $runSetQuantity = true)
    {
        // checks for newer product price & reduce item qty if stock levels exceeded
        if ($runSetQuantity) {
            foreach ($this->items as $item) {
                $item->setQuantity($item->item_qty);
            }
        }
        // checks order coupon is valid
        $validCoupon = false;
        if ($this->order_coupon) {
            foreach ($this->getAllActivePromotions() as $promotion) {
                // check coupon is actually used by an active promotion
                foreach ($promotion->coupons as $coupon) {
                    if (strtolower($coupon->code) == strtolower($this->order_coupon)) {
                        $validCoupon = true;
                        break 2;
                    }
                }
            }
        }
        // run function which can be used to display errors
        if ($fn) {
            $fn($this, $validCoupon);
        }
        // delete coupon if invalid and remove 0 stock level items
        if ($this->order_coupon && !$validCoupon) {
            $this->order_coupon = null;
        }
        foreach ($this->items as $item) {
            if ($item->item_qty == 0) {
                $item->delete();
            } else {
                $item->save();
            }
        }
        if ($this->exists) {
            $this->save();
        }
    }

    /**
     * @param Order\Item $item
     */
    public function addItem($item)
    {
        $this->saveIfNotExists();
        $item->setRelation('order', $this);
        $this->items()->save($item);
        $this->save(); // save order (to save new total values)
    }

    /**
     * @param int $itemId
     * @param int $qty
     * @param bool $updateOrder
     */
    public function updateItemQty($itemId, $qty, $updateOrder = true)
    {
        $qty = (int) $qty;
        /** @var Order\Item $item */
        if ($item = $this->items->where('id', '=', $itemId)->first()) {
            if ($qty <= 0) {
                $item->delete();
            } else {
                $item->setQuantity($qty);
                $item->save();
            }
            if ($updateOrder) {
                $this->save(); // save order (to save new total values)
            }
        }
    }

    /**
     * @param int $itemId
     */
    public function deleteItem($itemId)
    {
        if($item = $this->items->where('id', '=', $itemId)->first()) {
            /** @var Order\Item $item */
            $item->delete();
            $this->save(); // save order (to save new total values)
        }
    }

    /**
     *
     */
    public function deleteItems()
    {
        foreach($this->items as $item) {
            /** @var Order\Item $item */
            $item->delete();
        }
        $this->save(); // save order (to save new total values)
    }

    /**
     * @param int $itemId
     * @param array $options
     */
    public function updateItemOptions($itemId, $options = [])
    {
        if ($item = $this->items->where('id', $itemId)->first()) {
            $item->setData($options + $item->getDataArray())->save();
        }
    }

    /**
     * @param int $productId
     * @param array $options
     * @param int $qty
     * @return Item
     * @throws \Exception
     */
    public function addProduct($productId, $options = [], $qty = 1)
    {
        $product = $productId ? Product::find($productId) : null;
        if ($product->variations->count()) {
            throw new \Exception(__('coaster-commerce::frontend.product_option_required'));
        }
        return $this->_addProductItem($product, null, $qty, $options);
    }

    /**
     * @param int $variationId
     * @param array $options
     * @param int $qty
     * @return Item
     */
    public function addProductVariation($variationId, $options = [], $qty = 1)
    {
        $variation = Variation::find($variationId);
        $product = $variation ? Product::find($variation->product_id) : null;
        return $this->_addProductItem($product, $variation, $qty, $options);
    }

    /**
     * @param Product $product
     * @param Variation $variation
     * @param int $qty
     * @param array $options
     * @return Item
     */
    protected function _addProductItem(Product $product = null, Variation $variation = null, $qty = 1, array $options = [])
    {
        $qty = (int) $qty;
        if ($product && $qty > 0) {
            // load existing order item with same product_id/options
            $item = null;
            // replicate setData in Item model before checking options
            $optionsWithVariations = array_filter($variation ? $variation->variationArray() + $options : $options, function ($v) {
                return !is_null($v);
            });
            foreach ($this->items as $currentItem) {
                if ($currentItem->product_id == $product->id &&
                    $currentItem->variation_id == ($variation ? $variation->id : null) &&
                    $currentItem->getDataArray() == $optionsWithVariations
                ) {
                    $item = $currentItem;
                    break;
                }
            }
            // update/add item in/to order
            if ($item) {
                $item->setProduct($product, $variation, $qty + $item->item_qty, $options)->save();
                $this->save();
            } else {
                $item = new Order\Item();
                $item->setRelation('order', $this);
                $item->setProduct($product, $variation, $qty, $options);
                if ($item->item_qty > 0) {
                    $this->addItem($item);
                }
            }
        }
        return $item ?? null;
    }

    /**
     * @return Item[]
     */
    public function stockLimitedItems()
    {
        $stockLimitedItems = [];
        foreach ($this->items as $item) {
            /** @var Item $item */
            if ($item->item_request_qty != $item->item_qty) {
                $stockLimitedItems[] = $item;
            }
        }
        return $stockLimitedItems;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->order_status ? Status::getStatus($this->order_status)->state : null;
    }

    /**
     * @param string $state
     * @param bool $force
     */
    public function setState($state, $force = false)
    {
        if (!$this->order_status || ($force && $this->getState() != $state)) {
            $this->order_status = Status::getDefaultStatus($state)->code;
        }
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        foreach ($this->items as $item) {
            if (!$item->item_virtual) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return Promotion[]|Collection
     */
    public function getAllActivePromotions()
    {
        // load used cart promotions
        $promotions = Promotion::getActivePromotions(null, $this->customer, $this->order_coupon, $this->getIgnoredPromotionIds());
        // load used item promotions
        foreach ($this->items as $item) {
            if ($item->product && $item->item_qty > 0) {
                $promotions = $promotions->merge(Promotion::getActivePromotions($item->product, $this->customer, $this->order_coupon, $this->getIgnoredPromotionIds()));
            }
        }
        return $promotions;
    }

    /**
     * @return array
     */
    public function getIgnoredPromotionIds()
    {
        return $this->_ignoredPromotionIds;
    }

    /**
     * @param array $ids
     */
    public function setIgnoredPromotionIds($ids = [])
    {
        $this->_ignoredPromotionIds = $ids ?: [];
    }

    /**
     *
     */
    public function sendEmail()
    {
        Mail::send(new OrderMailable($this));
    }

    /**
     * Save if order does not exist in database
     */
    public function saveIfNotExists()
    {
        if (!$this->id) {
            $this->save();
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->generateSecretKey();
        $this->setState(static::STATUS_QUOTE);
        if (Status::getStatus($this->order_status)->state == static::STATUS_QUOTE) {
            if ($this->isVirtual()) {
                $this->shipping_method = null;
            }
            $this->customer_ip = request()->ip();
            if (array_key_exists('recalculate_items', $options) && $options['recalculate_items']) {
                $this->recalculateItems();
            }
            event(new OrderPreCalculateTotal($this));
            $this->calculateTotal();
            $this->updated_at = \Carbon\Carbon::now(); // always force update even if recalculated items/totals are unchanged
        } else {
            $this->generateOrderNumber();
        }
        if ($this->order_placed && !$this->getOriginal('order_placed')) {
            event(new OrderPlaced($this));
        }
        return parent::save($options);
    }

    /**
     * Saves new order with relations
     * Useful for payment gateways, best to work with order not in session so no modification can apply
     * @param array $attributes
     * @return static
     */
    public function saveClone($attributes = [])
    {
        /** @var static $clone */
        $clone = parent::replicate();
        // saves order entity (however will recalculate totals, so have to save again after relations are created)
        $clone->order_key = null;
        $clone->save();
        // save loaded relations (& makes sure items, addresses, notes etc. are loaded!)
        $this->loadMissing(['items', 'addresses', 'notes']);
        foreach($this->getRelations() as $relation => $items) {
            if ($items && is_a($items, Collection::class)) {
                foreach ($items as $item) {
                    $relationAttributes = $item->attributesToArray();
                    unset($relationAttributes['id']);
                    $clone->{$relation}()->getRelated()->unguard();
                    $clone->{$relation}()->create($relationAttributes);
                }
            }
        }
        // update any attributes
        foreach ($attributes as $attribute => $value) {
            $clone->setAttribute($attribute, $value);
        }
        // save again, but with correct totals and new order key
        $clone->save();
        return $clone;
    }

    /**
     * @param string $relation
     * @param mixed $value
     * @return Model
     */
    public function setRelation($relation, $value)
    {
        if ($relation == 'items') {
            foreach ($value as $item) {
                $item->setRelation('order', $this); // helps on stock checks as item has to access order model for
            }
        }
        return parent::setRelation($relation, $value);
    }

    /**
     * @param string $orderKey
     * @return Order|null
     */
    public static function loadByKey($orderKey)
    {
        return (new static)->where('order_key', $orderKey)->first();
    }

    /**
     * @param string $orderNumber
     * @return Order|null
     */
    public static function loadByNumber($orderNumber)
    {
        return (new static)->where('order_number', $orderNumber)->first();
    }

    /**
     * @param int $customerId
     * @return Order
     */
    public static function loadCustomerCart($customerId)
    {
        $loadableStatues = array_diff(Status::quoteStatuses(), ['payment_gateway']); // don't reload quotes where customer has got to payment gateway
        return (new static)->where('customer_id', $customerId)->whereIn('order_status', $loadableStatues)->orderBy('id')->first();
    }

    /**
     * @return array
     */
    public static function stateArray()
    {
        $status = [
            static::STATUS_QUOTE,
            static::STATUS_PROCESSING,
            static::STATUS_ON_HOLD,
            static::STATUS_COMPLETE,
            static::STATUS_CANCELLED
        ];
        return array_combine($status, $status);
    }

}
