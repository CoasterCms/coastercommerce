<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Customer;

class ResetPasswordMailable extends AbstractMailable
{

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var string
     */
    protected $_link;

    /**
     * ResetPasswordMailable constructor.
     * @param Customer $customer
     * @param string $token
     */
    public function __construct(Customer $customer, $token)
    {
        $this->_customer = $customer;
        $this->_link = route('coaster-commerce.frontend.customer.reset.update', ['token' => $token, 'email' => $customer->email]);
    }

    /**
     * @return static
     */
    public function build()
    {
        return $this
            ->to($this->_customer->email)
            ->markdown('coaster-commerce::emails.templates.password-reset', [
                'customer' => $this->_customer,
                'link' => $this->_link
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        return [(new Customer())->forceFill(['email' => 'test@exmaple.com']), 'token'];
    }

}
