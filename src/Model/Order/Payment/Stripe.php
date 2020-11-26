<?php

namespace CoasterCommerce\Core\Model\Order\Payment;

use Carbon\Carbon;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use CoasterCommerce\Core\Model\Currency;
use CoasterCommerce\Core\Model\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;

/**
 * Class Stripe
 */

class Stripe extends AbstractPayment
{

    /**
     * @return string
     */
    public function name()
    {
        $live = $this->getCustomField('mode') == 'live';
        return parent::name() . ($live ? '' : ' (TEST MODE)');
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.payment.method-stripe', ['method' => $this]);
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     * @throws ApiErrorException
     */
    public function paymentGateway(Request $request)
    {
        $order = $this->_createNonSessionOrder();
        // uses stripes checkout session
        // individual line items won't work accept item or order discounts
        // may have to change to single line item for order total
        $lineItems[] = [
            'name' => Setting::getValue('store_name') . ' Order',
            'amount' => round($order->order_total_inc_vat * 100),
            'currency' => Currency::getModel()->name ?: 'gbp',
            'quantity' => 1,
        ];

        // add session id to make sure callback success url can't be abused (as failure/success urls are v.similar)
        $successId = Str::random(16);

        // stripe api calls to init payment
        $this->setSecretKey();
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $order->billingAddress()->email ?: $order->email,
            'line_items' => $lineItems,
            'success_url' => route('coaster-commerce.frontend.checkout.callback.success', ['orderKey' => $order->order_key, 'session_id' => $successId]),
            'cancel_url' => route('coaster-commerce.frontend.checkout.callback.failure', ['orderKey' => $order->order_key]),
        ]);
        \Stripe\PaymentIntent::update($session->payment_intent, ['description' => Setting::getValue('store_name') . ' - Quote ID ' . $order->id]);

        // store payment_intent for later use
        (new Stripe\Model)->forceFill([
            'order_id' => $order->id,
            'session_id' => $successId,
            'payment_intent' => $session->payment_intent
        ])->save();

        return view('coaster-commerce::frontend.checkout.stripe', ['session' => $session, 'publishableKey' => $this->publishableKey()]);
    }

    /**
     * @param Request $request
     * @return View|string
     * @throws ApiErrorException
     */
    public function callbackSuccess(Request $request)
    {
        if ($stripePaymentDetails = Stripe\Model::findWithSession($request->get('session_id'))) {
            $order = $stripePaymentDetails->order;
            // complete order, only mark as paid if amounts match
            $this->setSecretKey();
            $intentDetails = \Stripe\PaymentIntent::retrieve($stripePaymentDetails->payment_intent);
            $this->_completeOrder($order, null, $intentDetails->amount == round($order->order_total_inc_vat * 100)); // saves $order
            // update payment description in stripe
            \Stripe\PaymentIntent::update($stripePaymentDetails->payment_intent, ['description' => Setting::getValue('store_name') . ' - Order ' . $order->order_number]);
            // redirect to order complete page
            return redirect()->route('coaster-commerce.frontend.checkout.complete', ['orderKey' => $order->order_key]);
        }
        /** @var FrontendAlert $alerts */
        $alerts = app(FrontendAlert::class);
        $alerts->flashAlerts(['danger' => ['Payment callback failed, order may not have been created']]);
        return redirect()->route('coaster-commerce.frontend.checkout.cart');
    }

    /**
     * @return string
     */
    public function publishableKey()
    {
        $live = $this->getCustomField('mode') == 'live';
        return $this->getCustomField($live ? 'pk_live' : 'pk_test');
    }

    /**
     *
     */
    public function setSecretKey()
    {
        $live = $this->getCustomField('mode') == 'live';
        $secretKey = $this->getCustomField($live ? 'sk_live' : 'sk_test');
        \Stripe\Stripe::setApiKey($secretKey);
    }

}
