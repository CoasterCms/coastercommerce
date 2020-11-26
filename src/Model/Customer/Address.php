<?php

namespace CoasterCommerce\Core\Model\Customer;

use CoasterCommerce\Core\Model\Country;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;

class Address extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_customer_addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id'
    ];

    /**
     * @param string $type
     * @return Order\Address
     */
    public function convertToOrderAddress($type)
    {
        return (new Order\Address())->forceFill($this->addressAttributes() + ['type' => $type]);
    }

    /**
     * @return array
     */
    public function addressAttributes()
    {
        return array_diff_key($this->attributesToArray(), array_flip([
            'id',
            'customer_id',
            'default_billing',
            'default_shipping',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function validationRules($prefix = '')
    {
        $rules = config('coaster-commerce.customer.address_validation', []) + [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'company' => 'nullable|max:255',
            'address_line_1' => 'required|max:255',
            'address_line_2' => 'nullable|max:255',
            'town' => 'required|max:255',
            'county' => 'nullable|max:255',
            'postcode' => 'required|max:255',
            'country_iso3' => 'required|max:255',
            'phone' => 'nullable|max:255',
            'email' => 'email|nullable|max:255'
        ];
        $prefixedRules = [];
        foreach ($rules as $field => $rule) {
            $prefixedRules[$prefix . $field] = $rule;
        }
        return $prefixedRules;
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

    /**
     * @param array $models
     * @return AddressCollection
     */
    public function newCollection(array $models = [])
    {
        return new AddressCollection($models);
    }

}
