<?php
namespace CoasterCommerce\Core\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Order;

/**
 * @mixin Order
 */
interface Cart
{

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId);

    /**
     * Forget order session
     */
    public function forgetOrderId();

    /**
     * Gets order model for current cart session or creates new one
     * @return Order
     */
    public function getOrder();

    /**
     * Load existing order or detach non-existent order from cart
     */
    public function loadOrder();

    /**
     * Create new order and set order id session
     */
    public function newOrder();

    /**
     * Get item count without initiating new order if cart empty
     * @return int
     */
    public function getItemCount();

    /**
     * @return Customer|Authenticatable
     */
    public function getCustomer();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param string $path
     * @param array|mixed $extra
     * @param bool $secure
     * @return string
     */
    public function route($path, $extra = [], $secure = null);

}