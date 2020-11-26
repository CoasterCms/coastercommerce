<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use CoasterCommerce\Core\Contracts\Cart;

class Customer
{

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * Auth constructor.
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->_cart = $cart;
    }

    /**
     * @param Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->_cart->getCustomer()) {
            return $next($request);
        }

        return redirect()->route('coaster-commerce.frontend.customer.login')
            ->withCookie(cookie('customer_login_path', $request->getRequestUri()));
    }

}

