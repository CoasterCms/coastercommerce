<?php

namespace CoasterCommerce\Core\Model\Tax;

use CoasterCommerce\Core\Model\Customer\Group;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRule extends Model
{

    public $table = 'cc_tax_rules';

    /**
     * @return BelongsTo
     */
    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * @return BelongsTo
     */
    public function taxZone()
    {
        return $this->belongsTo(TaxZone::class);
    }

    /**
     * @return BelongsTo
     */
    public function customerGroup()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return string
     */
    public function customerGroupName()
    {
        $group = $this->customerGroup;
        return $group ? $group->name : '*';
    }

    /**
     * @param int $taxClassId
     * @return float
     */
    public static function getRate($taxClassId)
    {
        /** @var Cart $cart */
        $cart = app(Cart::class);
        $country = null;
        if ($address = $cart->billingAddress()) {
            $country = $address->country_iso3;
        }
        if ($customer = $cart->getCustomer()) {
            $customerGroupId = $customer->group_id;
            $country = $country ?: $customer->defaultBillingAddress()->country_iso3;
        }
        if ($country) {
            $taxZoneIds = TaxZoneArea::where('country_iso3', $country)->pluck('tax_zone_id')->toArray();
        }
        $taxClassId = $taxClassId ?: Setting::getValue('vat_tax_class');
        $taxZoneIds = !empty($taxZoneIds) ? $taxZoneIds : [Setting::getValue('vat_tax_zone')];
        $customerGroupId = !empty($customerGroupId) ? $customerGroupId : null;
        $taxRule = static::where('tax_class_id', $taxClassId)
            ->whereIn('tax_zone_id', $taxZoneIds)
            ->where(function ($q) use($customerGroupId) {
                $q->where('customer_group_id', $customerGroupId)->orWhere('customer_group_id', null);
            })->orderBy('priority', 'desc')->first();
        return $taxRule ? $taxRule->percentage / 100 : 0;
    }

    /**
     * @param int $taxClassId
     * @param float $price
     * @param string $from
     * @param string $to
     * @return float
     */
    public static function calculatePrice($taxClassId, $price, $from, $to)
    {
        if ($from != $to) {
            $vatRate = static::getRate($taxClassId);
            $price = $from == 'ex' ?
                $price * (1 + $vatRate) :
                $price / (1 + $vatRate);
        }
        return $price;
    }

}
