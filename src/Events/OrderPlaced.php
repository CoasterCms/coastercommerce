<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Order;

class OrderPlaced
{

    /**
     * @var Order
     */
    public $order;

    /**
     * FrontendInit constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

}

