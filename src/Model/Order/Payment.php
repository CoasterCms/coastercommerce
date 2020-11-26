<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Events\OrderPaymentMethods;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Payment\AbstractPayment;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    /**
     * @var string
     */
    public $table = 'cc_payment_methods';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $primaryKey = 'code';

    /**
     * @var AbstractPayment[]
     */
    protected static $_models;

    /**
     * @param Order $order
     * @return AbstractPayment[]
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
     * @return AbstractPayment[]
     */
    public function getAvailableMethods(Order $order)
    {
        $methods = array_filter(static::getMethods($order), function ($method) {
            /** @var AbstractPayment $method */
            return $method->active && $this->_isAvailable($method);
        });
        event($event = new OrderPaymentMethods($order, $methods));
        return $event->getMethods();
    }

    /**
     * @param string $methodId
     * @param Order $order
     * @return AbstractPayment
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
     * @return AbstractPayment
     */
    public function getMethod($methodId, Order $order = null)
    {
        foreach (static::getMethods($order) as $method) {
            if ($methodId == $method->code) {
                return $method;
            }
        }
        return null;
    }

    /**
     * @param AbstractPayment $method
     * @return bool
     */
    protected function _isAvailable($method)
    {
        /** @var Cart $cart */
        $cart = app(Cart::class);
        if ($method->min_cart_total && $method->min_cart_total > $cart->order_total_inc_vat) {
            return false;
        }
        if ($method->max_cart_total && $method->max_cart_total < $cart->order_total_inc_vat) {
            return false;
        }
        return $method->isAvailable();
    }

}
