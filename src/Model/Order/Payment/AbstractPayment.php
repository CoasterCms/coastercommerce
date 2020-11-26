<?php

namespace CoasterCommerce\Core\Model\Order\Payment;

use Carbon\Carbon;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Payment;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class AbstractPayment
{

    /**
     * @var Payment
     */
    protected $_model;

    /**
     * Loaded order, usually cart session order, or by orderKey on payment callbacks
     * @var Order
     */
    protected $_order;

    /**
     * AbstractShipping constructor.
     * @param Order $order
     * @param Payment $methodModel
     */
    public function __construct($methodModel, $order = null)
    {
        $this->_model = $methodModel;
        $this->_order = $order ?: new Order;
    }

    /**
     * @return string
     */
    public function type()
    {
        return ucwords(Str::snake(substr(get_class($this), strrpos(get_class($this), '\\') + 1), ' '));
    }

    /**
     * Frontend name for method
     * @return string
     */
    public function name()
    {
        return $this->_model->name;
    }

    /**
     * Frontend description for method on checkout
     * @return string
     */
    public function description()
    {
        return $this->_model->description;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Display in payment section on Customer/Admin order view
     * @return string
     */
    public function showDetails()
    {
        return (string) view('coaster-commerce::admin.order.payment-details', ['method' => $this, 'order' => $this->_order]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCustomField($name)
    {
        $config = $this->_model->custom_config ? json_decode($this->_model->custom_config, true) : [];
        return array_key_exists($name, $config) ? $config[$name] : null;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setCustomField($name, $value)
    {
        $config = $this->_model->custom_config ? json_decode($this->_model->custom_config, true) : [];
        if (is_null($value)) {
            unset($config[$name]);
        } else {
            $config[$name] = $value;
        }
        $this->fillCustomFields($config);
    }

    /**
     * @param array $config
     */
    public function fillCustomFields($config)
    {
        $this->_model->custom_config = $config ? json_encode($config) : null;
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return '';
    }

    /**
     * @return Payment
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_model->$name;
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    abstract public function paymentGateway(Request $request);

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function callbackFailure(Request $request)
    {
        /** @var FrontendAlert $alerts */
        $alerts = app(FrontendAlert::class);
        $alerts->flashAlert('danger', 'Payment has failed or been cancelled');
        return redirect()->route('coaster-commerce.frontend.checkout.onepage');
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function callbackSuccess(Request $request)
    {
        return null;
    }

    /**
     * Creates order clone that is not linked to session so it can't be changed while customer is at gateway
     * @param Order $order
     * @return Order
     */
    protected function _createNonSessionOrder($order = null)
    {
        $order = $order ?: $this->_order;
        return $order->saveClone(['order_status' => 'payment_gateway']);
    }

    /**
     * Completes order and clears cart
     * @param string $status
     * @param Order $order
     * @param bool $markPaid
     */
    protected function _completeOrder($order = null, $status = null, $markPaid = false)
    {
        $order = $order ?: $this->_order;
        if ($order->getState() === Order::STATUS_QUOTE) {
            // clear cart if in session
            $cart = app(Cart::class);
            if ($order->id == $cart->id) {
                $cart->forgetOrderId();
            } elseif ($order->order_total_inc_vat == $cart->order_total_inc_vat) {
                // or delete and clear if cloned order where quote is unchanged (_createNonSessionOrder)
                if ($order->items->pluck('item_sku')->toArray() == $cart->items->pluck('item_sku')->toArray()) {
                    $cart->delete();
                    $cart->forgetOrderId();
                }
            }
            // update order status
            $status = $status ?: $this->_model->order_status;
            if ($status) {
                $order->order_status = $status;
            } else {
                $order->setState(Order::STATUS_PROCESSING, true);
            }
            // update order placed & payment confirmed timestamps then save
            $order->order_placed = new Carbon();
            if ($markPaid) {
                $order->payment_confirmed = new Carbon();
            }
            $order->save();
            try {
                // send order email
                $order->sendEmail();
            } catch (\Exception $e) {
                /** @var FrontendAlert $alerts */
                $alerts = app(FrontendAlert::class);
                $alerts->flashAlert('warning', 'Failed to send order email - ' . $e->getMessage());
            }
        }
    }

}
