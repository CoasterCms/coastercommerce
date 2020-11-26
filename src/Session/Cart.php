<?php
namespace CoasterCommerce\Core\Session;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use Illuminate\View\Factory as View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\UrlGenerator;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Contracts\Cart as CartContract;

/**
 * @mixin Order
 */
class Cart implements CartContract
{

    /**
     * var name used to store order id
     */
    const SESSION_VAR = 'coaster-commerce.order';

    /**
     * @var AuthManager
     */
    protected $_auth;

    /**
     * @var UrlGenerator
     */
    protected $_url;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var View
     */
    protected $_view;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var bool
     */
    protected $_loadedOrder;

    /**
     * Cart constructor.
     * @param Session $session
     * @param AuthManager $auth
     * @param UrlGenerator $url
     * @param View $view)
     */
    public function __construct(Session $session, AuthManager $auth, UrlGenerator $url, View $view)
    {
        $this->_session = $session;
        $this->_auth = $auth;
        $this->_url = $url;
        $this->_view = $view;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->_session->get(static::SESSION_VAR);
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->_session->put(static::SESSION_VAR, $orderId);
    }

    /**
     * Forget order session
     */
    public function forgetOrderId()
    {
        $this->_order = null;
        $this->_session->put(static::SESSION_VAR, null);
    }

    /**
     * Gets order model for current cart session or creates new one
     * @return Order
     */
    public function getOrder()
    {
        if (!$this->loadOrder()) {
            $this->newOrder();
        }
        return $this->_order;
    }

    /**
     * Load existing order or detach non-existent order from cart
     * @return bool
     */
    public function loadOrder()
    {
        if (!$this->_loadedOrder) {
            $this->_loadedOrder = true; // only run once!
            // Try to load order from session
            if ($this->getOrderId() && (!$this->_order || ($this->_order && $this->_order->id != $this->getOrderId()))) {
                $this->_order = Order::find($this->getOrderId());
            }
            // Check if the order customer id does not match the currently logged in customer id
            if ($this->_order && $this->_order->customer_id != $this->getCustomerId()) {
                if ($this->_order->customer_id) { // customer has logged out
                    $this->forgetOrderId();
                } elseif (!$this->_order->customer_id) { // customer has logged in are creating guest cart
                    $this->_order->customer_id = $this->getCustomerId();
                    $this->_order->save();
                }
            }
            // Else try to load order from customer id (also try if current order is empty)
            if ((!$this->_order || !$this->_order->items->count()) && $this->getCustomerId()) {
                $this->_order = Order::loadCustomerCart($this->getCustomerId());
            }
            if ($this->_order) {
                $this->setOrderId($this->_order->id); // make sure session matches loaded order
            } else {
                $this->forgetOrderId(); // if lingering order id in session
            }
        }
        return (bool) $this->_order;
    }

    /**
     * Create new order and set order id session
     */
    public function newOrder()
    {
        if (!$this->_order) {
            $this->_order = new Order();
            $this->_order->customer_id = $this->getCustomerId();
        }
        // set order id session is in SessionSaving middleware
    }

    /**
     * @param array $billingDetails
     * @param array $shippingDetails
     * @param string $paymentMethod
     * @param Customer $customer
     * @return RedirectResponse
     */
    public function placeOrder($billingDetails, $shippingDetails, $paymentMethod, $customer)
    {
        $redirect = $this->getOrder()->placeOrder($billingDetails, $shippingDetails, $paymentMethod, $customer);

        // end cart session
        $this->forgetOrderId();

        return $redirect;
    }

    /**
     * Get item count without initiating new order if cart empty
     * @return int
     */
    public function getItemCount()
    {
        $this->loadOrder(); // updates order id to stop new order being created unnecessarily
        return $this->getOrderId() ? $this->items->count() : 0;
    }

    /**
     * @return Guard
     */
    public function guard()
    {
        return $this->_auth->guard('cc-customer');
    }

    /**
     * @return Customer|Authenticatable
     */
    public function getCustomer()
    {
        return $this->guard()->user();
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $customer = $this->getCustomer();
        return $customer ? $customer->id : null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getOrder(), $name], $arguments);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getOrder()->$key;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->getOrder()->$key = $value;
    }

    /**
     * @param string $path
     * @param array|mixed $extra
     * @param bool $secure
     * @return string
     */
    public function route($path, $extra = [], $secure = null)
    {
        return $this->_url->route('coaster-commerce.' . $path, $extra, $secure);
    }

    /**
     * @param $view
     * @param array $viewData
     * @return mixed
     */
    public function view($view, $viewData = [])
    {
        return $this->_view->make(config('coaster-commerce.views') . $view, $viewData);
    }

}