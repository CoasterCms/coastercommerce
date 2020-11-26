<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use CoasterCommerce\Core\Session\WishList;
use Illuminate\Http\Request;
use CoasterCommerce\Core\Session\Cart;

class SessionSaving
{

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var WishList
     */
    protected $_wishList;

    /**
     * Auth constructor.
     * @param Cart $cart
     * @param WishList $wishList
     */
    public function __construct(Cart $cart, WishList $wishList)
    {
        $this->_cart = $cart;
        $this->_wishList = $wishList;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $result = $next($request);
        if ($this->_cart->exists) {
            $this->_cart->setOrderId($this->_cart->id);
        }
        if ($this->_wishList->exists) {
            $this->_wishList->setWishListId($this->_wishList->id);
        }
        return $result;
    }

}

