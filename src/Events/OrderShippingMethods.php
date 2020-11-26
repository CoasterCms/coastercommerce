<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Shipping\AbstractShipping;

class OrderShippingMethods
{

    /**
     * @var AbstractShipping[]
     */
    public $methods;

    /**
     * @var Order
     */
    public $order;

    /**
     * OrderShippingMethods constructor.
     * @param Order $order
     * @param AbstractShipping[] $methods
     */
    public function __construct($order, $methods)
    {
        $this->order = $order;
        $this->methods = $methods;
    }

    /**
     * @return AbstractShipping[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

}

