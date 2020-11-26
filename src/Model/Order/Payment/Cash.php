<?php

namespace CoasterCommerce\Core\Model\Order\Payment;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class Cash
 */

class Cash extends AbstractPayment
{

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function paymentGateway(Request $request)
    {
        $this->_completeOrder();
        return redirect()->route('coaster-commerce.frontend.checkout.complete', ['orderKey' => $this->_order->order_key]);
    }

}
