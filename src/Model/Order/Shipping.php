<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Events\OrderShippingMethods;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Shipping\AbstractShipping;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{

    /**
     * @var string
     */
    public $table = 'cc_shipping_methods';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $primaryKey = 'code';

    /**
     * @var bool
     */
    public $showOrderRestrictions = true;

    /**
     * @var Collection
     */
    protected static $_models;

    /**
     * @param Order $order
     * @return AbstractShipping[]
     */
    public function getMethods(Order $order = null)
    {
        if (is_null(static::$_models)) {
            static::$_models = $this->all();
        }
        $methods = [];
        foreach (static::$_models as $methodModel) {
            $methodClass = $methodModel->class;
            if (!class_exists($methodClass)) {
                continue;
            }
            $methods[] = new $methodClass($methodModel, $order);
        }
        usort($methods, function ($a, $b) {
            return $a->sort_value <=> $b->sort_value;
        });
        return $methods;
    }

    /**
     * @param Order $order
     * @return AbstractShipping[]
     */
    public function getAvailableMethods(Order $order)
    {
        $methods = array_filter(static::getMethods($order), function ($method) {
            /** @var AbstractShipping $method */
            return $method->active && $this->_isAvailable($method);
        });
        event($event = new OrderShippingMethods($order, $methods));
        return $event->getMethods();
    }

    /**
     * @param string $methodId
     * @param Order $order
     * @return AbstractShipping
     */
    public function getAvailableMethod($methodId, Order $order)
    {
        foreach ($this->getAvailableMethods($order) as $method) {
            if ($methodId == $method->code) {
                return $method;
            }
        }
        return null;
    }

    /**
     * @param string $methodId
     * @param Order $order
     * @return AbstractShipping
     */
    public function getMethod($methodId, Order $order)
    {
        foreach (static::getMethods($order) as $method) {
            if ($methodId == $method->code) {
                return $method;
            }
        }
        return null;
    }

    /**
     * @param AbstractShipping $method
     * @return bool
     */
    protected function _isAvailable($method)
    {
        /** @var Cart $cart */
        $cart = app(Cart::class);
        $totalMinusShipping = $cart->order_total_inc_vat - $cart->order_shipping_inc_vat + $cart->order_shipping_discount_inc_vat;
        if ($method->min_cart_total && $method->min_cart_total > $totalMinusShipping) {
            return false;
        }
        if ($method->max_cart_total && $method->max_cart_total < $totalMinusShipping) {
            return false;
        }
        return $method->isAvailable();
    }

}
