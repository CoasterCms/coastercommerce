<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Model\Country;
use CoasterCommerce\Core\Model\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_order_addresses';

    /**
     * @return Customer\Address
     */
    public function convertToCustomerAddress()
    {
        return (new Customer\Address())->forceFill($this->addressAttributes());
    }

    /**
     * @return array
     */
    public function addressAttributes()
    {
        return array_diff_key($this->attributesToArray(), array_flip([
            'id',
            'order_id',
            'type',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * @return string
     */
    public function fullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * @return string
     */
    public function country()
    {
        return Country::name($this->country_iso3);
    }

    /**
     * @param string $view
     * @return View
     */
    public function render($view = null)
    {
        return view($view ?: 'coaster-commerce::admin.customer.address', ['address' => $this]);
    }

    /**
     * Used to quickly check for matching addresses
     * @return string
     */
    public function stringValue()
    {
        $attributeArray = [];
        foreach ($this->addressAttributes() as $attribute => $value) {
            $attributeArray[$attribute] = '#' . $attribute . ':' . trim($value);
        }
        ksort($attributeArray);
        return implode('', $attributeArray);
    }

}
