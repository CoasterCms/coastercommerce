<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Customer;

class AdminCustomerSave
{

    /**
     * @var Customer
     */
    public $customer;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminCategorySave constructor.
     * @param Customer $customer
     * @param array $inputData
     */
    public function __construct(Customer $customer, array $inputData)
    {
        $this->customer = $customer;
        $this->inputData = $inputData;
    }

}

