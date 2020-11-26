<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\AbandonedCart;

class AbandonedCartMailable extends AbstractMailable
{

    /**
     * @var AbandonedCart
     */
    protected $cart;

    /**
     * Create a new message instance.
     *
     * @param AbandonedCart $cart
     * @return void
     */
    public function __construct($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return static
     */
    public function build()
    {
        return $this
            ->to($this->cart->email)
            ->markdown('coaster-commerce::emails.templates.abandoned-cart', [
                'acart' => $this->cart
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $cart = new AbandonedCart();
        $cart->order = OrderMailable::testData()[0];
        $cart->email = 'test@example.com';
        return [$cart];
    }

}
