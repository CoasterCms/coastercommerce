<?php

namespace CoasterCommerce\Core\Model\Order\Payment;

use Carbon\Carbon;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use CoasterCommerce\Core\Model\Currency;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\ISO3166\ISO3166;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

/**
 * Class SagePay
 */

class PayPal extends AbstractPayment
{

    /**
     * @return string
     */
    public function name()
    {
        return parent::name() . ($this->isLive() ? '' : ' (TEST MODE)');
    }

    /**
     * @return bool
     */
    public function isLive()
    {
        return $this->getCustomField('mode')  == 'live';
    }

    /**
     * @return PayPalHttpClient
     */
    public function getClient()
    {
        if ($this->isLive()) {
            $clientEnv = new ProductionEnvironment(
                $this->getCustomField('id_live'),
                $this->getCustomField('secret_live')
            );
        } else {
            $clientEnv = new SandboxEnvironment(
                $this->getCustomField('id_sandbox'),
                $this->getCustomField('secret_sandbox')
            );
        }
        return new PayPalHttpClient($clientEnv);
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.payment.method-paypal', ['method' => $this]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function paymentGateway(Request $request)
    {
        $order = $this->_createNonSessionOrder();
        $createRequest = new OrdersCreateRequest();
        $additionalPayerInfo = [];
        if ($phone = $order->billingAddress()->phone) {
            $additionalPayerInfo["phone"] = [
                "phone_number"=> [
                    "national_number"=> str_replace(' ', '', $phone)
                ]
            ];
        }
        $createRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "invoice_id" => "ORDER-" .  (new Carbon())->format('y-m-d-H-i-s-') . 'ID-' . $order->id,
                "amount" => [
                    "value" => (string) $order->order_total_inc_vat,
                    "currency_code" => Currency::getModel()->name ?: 'gbp'
                ],
            ]],
            "payer"=> [
                "name"=> [
                    "given_name"=> $order->billingAddress()->first_name,
                    "surname"=> $order->billingAddress()->last_name
                ],
                "email_address"=> $order->billingAddress()->email ?: $order->email,
                "address"=> [
                    "address_line_1"=> $order->billingAddress()->address_line_1,
                    "address_line_2"=> $order->billingAddress()->address_line_2,
                    "admin_area_2"=> $order->billingAddress()->town,
                    "admin_area_1"=> $order->billingAddress()->county,
                    "postal_code"=> $order->billingAddress()->postcode,
                    "country_code"=> (new ISO3166)->alpha3($order->billingAddress()->country_iso3)['alpha2']
                ]
            ] + $additionalPayerInfo,
            "application_context" => [
                "cancel_url" => route('coaster-commerce.frontend.checkout.callback.failure', ['orderKey' => $order->order_key]),
                "return_url" => route('coaster-commerce.frontend.checkout.callback.success', ['orderKey' => $order->order_key])
            ]
        ];

        try {
            $response = $this->getClient()->execute($createRequest);
            $payment = PayPal\Model::findWithPPId($response->result->id) ?: new PayPal\Model();
            $payment->forceFill([
                'pp_id' => $response->result->id,
                'pp_status' => $response->result->status,
                'order_id' => $order->id
            ])->save();
            return redirect()->away($response->result->links[1]->href); // customer approve (redirect to paypal for payment details)
        } catch (\Exception $e) {
            /** @var FrontendAlert $alerts */
            $alerts = app(FrontendAlert::class);
            Log::error('PayPal Error [Order ID ' . $order->id . ']: ' . $e->getMessage());
            $alerts->flashAlerts(['danger' => ['Payment has failed or been cancelled', $e->getMessage()]]);
            return redirect()->route('coaster-commerce.frontend.checkout.onepage');
        }
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     * @throws \Exception
     */
    public function callbackFailure(Request $request)
    {
        if ($payment = PayPal\Model::findWithPPId($request->get('token'))) {
            if ($payment->pp_status == 'CREATED' && !$payment->pp_payments) {
                $payment->pp_payments = json_encode(['action' => 'Cancelled by customer']);
                $payment->save();
            }
        }
        return parent::callbackFailure($request);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|string
     * @throws \Exception
     */
    public function callbackSuccess(Request $request)
    {
        $errors = ['Payment callback failed, order may not have been created'];
        if ($payment = PayPal\Model::findWithPPId($request->get('token'))) {
            try {
                $order = $payment->order;
                // capture payment in paypal
                if ($payment->pp_status != 'COMPLETED') {
                    $captureRequest = new OrdersCaptureRequest($payment->pp_id); // token = pp_id
                    $response = $this->getClient()->execute($captureRequest);
                    if ($response->result->status == 'COMPLETED') {
                        $payment->pp_status = $response->result->status;
                        $payment->pp_payer = json_encode($response->result->payer);
                        $payment->pp_payments = json_encode($response->result->purchase_units[0]->payments);
                        $payment->save();
                    } else {
                        $errors[] = 'PayPal payment response: ' . $response->result->status;
                    }
                }
                // complete order if payment captured & still marked quote in website
                if ($payment->pp_status == 'COMPLETED') {
                    $this->_completeOrder($order, null, $response->result->purchase_units[0]->payments->captures[0]->amount->value == $order->order_total_inc_vat); // saves $order
                    return redirect()->route('coaster-commerce.frontend.checkout.complete', ['orderKey' => $order->order_key]);
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        /** @var FrontendAlert $alerts */
        $alerts = app(FrontendAlert::class);
        $alerts->flashAlerts(['danger' => $errors]);
        return redirect()->route('coaster-commerce.frontend.checkout.cart');
    }

}
