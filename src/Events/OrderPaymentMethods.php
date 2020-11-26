<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Payment\AbstractPayment;

class OrderPaymentMethods
{

    /**
     * @var AbstractPayment[]
     */
    public $methods;

    /**
     * @var Order
     */
    public $order;

    /**
     * OrderPaymentMethods constructor.
     * @param Order $order
     * @param AbstractPayment[] $methods
     */
    public function __construct($order, $methods)
    {
        $this->order = $order;
        $this->methods = $methods;
    }

    /**
     * @return AbstractPayment[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

}

