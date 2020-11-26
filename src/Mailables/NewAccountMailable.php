<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Customer;

class NewAccountMailable extends AbstractMailable
{

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var array
     */
    protected $_registerFormData;

    /**
     * Create a new message instance.
     *
     * @param Customer $customer
     * @param array $registerFormData
     * @return void
     */
    public function __construct($customer, $registerFormData = [])
    {
        $this->_customer = $customer;
        $this->_registerFormData = $registerFormData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->_customer->email)
            ->markdown('coaster-commerce::emails.templates.new-account', [
                'customer' => $this->_customer,
                'registerFormData' => $this->_registerFormData
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $customer = (new Customer())->forceFill([
            'email' => 'test@example.com'
        ]);
        return [$customer];
    }

}
