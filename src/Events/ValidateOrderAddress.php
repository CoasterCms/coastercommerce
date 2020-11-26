<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Customer\Address;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Http\Request;

class ValidateOrderAddress
{

    /**
     * @var Order
     */
    public $request;

    /**
     * @var array
     */
    public $validationRules;

    /**
     * @var Address
     */
    public $billingAddress;

    /**
     * @var Address
     */
    public $shippingAddress;

    /**
     * ValidateOrderAddress constructor.
     * @param Request $request
     * @param array $validationRules
     * @param Address $billingAddress
     * @param Address $shippingAddress
     */
    public function __construct($request, $validationRules, $billingAddress, $shippingAddress)
    {
        $this->request = $request;
        $this->validationRules = $validationRules;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
    }

}

