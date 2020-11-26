<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use CoasterCommerce\Core\Model\AbandonedCart;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AbandonedCartController extends AbstractController
{

    /**
     * @param Request $request
     * @param int $id
     * @return View
     */
    public function unsubscribe(Request $request, $id)
    {
        $email = '';
        if ($cart = AbandonedCart::find($id)) {
            if ($request->get('email') == $cart->email) {
                if(!AbandonedCart\Unsubscribed::where('email', $cart->email)->first()) {
                    $unsubscribe = new AbandonedCart\Unsubscribed();
                    $unsubscribe->email = $cart->email;
                    $unsubscribe->save();
                    $email = $cart->email;
                }
            }
        }
        $this->_setPageMeta('Unsubscribe');
        return $this->_view('customer.abandoned-cart', ['email' => $email]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function checkout(Request $request, $id)
    {
        $validAbandonedCart = false;
        if ($cart = AbandonedCart::find($id)) {
            if ($request->get('order_key') == $cart->order->order_key) {
                /** @var Order $order */
                $order = $cart->order;
                $statusesAllowed = array_diff(Order\Status::quoteStatuses(), ['payment_gateway']); // payment_gateway orders are always clones so ignore
                if (in_array($order->order_status, $statusesAllowed)) {
                    if ($order->customer_id && $order->customer_id != $this->_cart->getCustomerId()) {
                        $order->customer_id = null; // unlink customer to stop cart session unsetting due to current logged in customer
                        $order->save();
                    }
                    $this->_cart->setOrderId($cart->order->id); // set session cart as order!
                    $validAbandonedCart = true;
                }
            }
        }
        if (!$validAbandonedCart) {
            $this->_flashAlert('danger', __('coaster-commerce::frontend.abandoned_cart_checkout_fail'));
        }
        return $this->_redirect('checkout.cart');
    }

}