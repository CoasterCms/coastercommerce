<?php
namespace CoasterCommerce\Core\Session;

use Illuminate\Contracts\Session\Session;
use CoasterCommerce\Core\Model\Customer\WishList as WishListModel;

/**
 * @mixin WishListModel
 */
class WishList
{

    /**
     * var name used to store wish list id
     */
    const SESSION_VAR = 'coaster-commerce.wishlist';

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var WishListModel
     */
    protected $_wishList;

    /**
     * @var bool
     */
    protected $_loadedWishList;

    /**
     * Cart constructor.
     * @param Session $session
     * @param Cart $cart
     */
    public function __construct(Session $session, Cart $cart)
    {
        $this->_session = $session;
        $this->_cart = $cart;
    }

    /**
     * @return string
     */
    public function getWishListId()
    {
        return $this->_session->get(static::SESSION_VAR);
    }

    /**
     * @param string $wishListId
     */
    public function setWishListId($wishListId)
    {
        $this->_session->put(static::SESSION_VAR, $wishListId);
    }

    /**
     * Forget wish list session
     */
    public function forgetWishListId()
    {
        $this->_wishList = null;
        $this->_session->put(static::SESSION_VAR, null);
    }

    /**
     * Gets wish list model for current cart session or creates new one
     * @return WishListModel
     */
    public function getWishList()
    {
        if (!$this->loadWishList()) {
            $this->newWishList();
        }
        return $this->_wishList;
    }

    /**
     * Load existing wish list or detach non-existent wish list from cart
     * @return bool
     */
    public function loadWishList()
    {
        if (!$this->_loadedWishList) {
            $this->_loadedWishList = true; // only run once
            // Try to load wish list from session
            if ($this->getWishListId() && (!$this->_wishList || ($this->_wishList && $this->_wishList->id != $this->getWishListId()))) {
                $this->_wishList = WishListModel::find($this->getWishListId());
            }
            // Check if customer has logged out or switched user
            if ($this->_wishList && $this->_wishList->customer_id && $this->_wishList->customer_id != $this->_cart->getCustomerId()) {
                $this->forgetWishListId();
            }
            // Finally try to load existing list for logged in customer
            if ((!$this->_wishList || !$this->_wishList->items->count()) && $this->_cart->getCustomerId()) {
                $this->_wishList = WishListModel::loadCustomerList($this->_cart->getCustomerId());
            }
            if ($this->_wishList) {
                $this->_updateWithCartDetails();
                $this->setWishListId($this->_wishList->id); // make sure session matches loaded wish list
            } else {
                $this->forgetWishListId(); // if lingering wish list id in session
            }
        }
        return (bool) $this->_wishList;
    }

    /**
     * Create new wish list and set wish list id session
     */
    public function newWishList()
    {
        if (!$this->_wishList) {
            $this->_wishList = new WishListModel();
            $this->_updateWithCartDetails();
        }
        // set wish list id session is in SessionSaving middleware
    }

    /**
     * Attach customer_id or order_id to list
     */
    protected function _updateWithCartDetails()
    {
        if ($this->_cart->getCustomerId()) {
            // customer has logged in
            $this->_wishList->customer_id = $this->_cart->getCustomerId();
            $this->_wishList->guest_order_id = null; // no longer needed as we have all customer details
        } else {
            // else guest customer, try to attach cart to log more customer details
            $this->_wishList->guest_order_id = $this->_cart->getOrderId();
        }
        if ($this->_wishList->exists) {
            $this->_wishList->save(); // don't save if new list (as it won't have items anyway)
        }
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->items->count();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getWishList(), $name], $arguments);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getWishList()->$key;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->getWishList()->$key = $value;
    }

}