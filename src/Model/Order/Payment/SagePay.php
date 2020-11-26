<?php

namespace CoasterCommerce\Core\Model\Order\Payment;

use Carbon\Carbon;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use CoasterCommerce\Core\Model\Order\Payment\SagePay\FormAPI;
use CoasterCommerce\Core\Model\Order\Payment\SagePay\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\ISO3166\ISO3166;

/**
 * Class SagePay
 */

class SagePay extends AbstractPayment
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
     * @return string
     */
    public function getKey()
    {
        return $this->isLive() ? $this->getCustomField('key_live') : $this->getCustomField('key_test');
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.payment.method-sagepay', ['method' => $this]);
    }

    /**
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function paymentGateway(Request $request)
    {
        $order = $this->_createNonSessionOrder();
        $txCode = 'TX-' . (new Carbon())->format('y-m-d-H-i-s-') . 'ID-' . $this->_order->id;
        $formAPI = $this->_getAPI();
        $formAPI->setCryptArray([
            // required fields
            'VendorTxCode' => $txCode,
            'Amount' => (string) $order->order_total_inc_vat,
            'Currency' => 'GBP',
            'Description' => 'Order for ' . $order->email,
            'SuccessURL' => route('coaster-commerce.frontend.checkout.callback.success', ['orderKey' => $order->order_key]),
            'FailureURL' => route('coaster-commerce.frontend.checkout.callback.failure', ['orderKey' => $order->order_key]),
            'BillingSurname' => $order->billingAddress()->last_name,
            'BillingFirstnames' => $order->billingAddress()->first_name,
            'BillingAddress1' => $order->billingAddress()->address_line_1,
            'BillingCity' => $order->billingAddress()->town,
            'BillingPostCode' => $order->billingAddress()->postcode,
            'BillingCountry' => (new ISO3166)->alpha3($order->billingAddress()->country_iso3)['alpha2'],
            'DeliverySurname' => $order->shippingAddress()->last_name,
            'DeliveryFirstnames' => $order->shippingAddress()->first_name,
            'DeliveryAddress1' => $order->shippingAddress()->address_line_1,
            'DeliveryCity' => $order->shippingAddress()->town,
            'DeliveryPostCode' => $order->shippingAddress()->postcode,
            'DeliveryCountry' => (new ISO3166)->alpha3($order->shippingAddress()->country_iso3)['alpha2'],
            // optional fields
            'CustomerName' => $order->billingAddress()->fullName(),
            'CustomerEMail' => $order->billingAddress()->email ?: $order->email,
            'VendorEmail' => $this->getCustomField('email_bcc')
        ]);
        SagePay\Model::saveTxCode($txCode, $order->id);
        return view('coaster-commerce::frontend.checkout.sagepay', [
            'formAPI' => $formAPI
        ]);
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     * @throws \Exception
     */
    public function callbackFailure(Request $request)
    {
        $this->_updatePaymentData($request->get('crypt'));
        return parent::callbackFailure($request);
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse|string
     * @throws \Exception
     */
    public function callbackSuccess(Request $request)
    {
        if ($paymentDetails = $this->_updatePaymentData($request->get('crypt'))) {
            $order = $paymentDetails->order;
            // complete order, only mark as paid if amounts match
            $paymentData = $paymentDetails->getPaymentData();
            if (array_key_exists('Status', $paymentData) && $paymentData['Status'] == 'OK') {
                $this->_completeOrder($order, null, $paymentData['Amount'] == $order->order_total_inc_vat); // saves $order
                return redirect()->route('coaster-commerce.frontend.checkout.complete', ['orderKey' => $order->order_key]);
            }
        }
        /** @var FrontendAlert $alerts */
        $alerts = app(FrontendAlert::class);
        $alerts->flashAlerts(['danger' => ['Payment callback failed, order may not have been created']]);
        return redirect()->route('coaster-commerce.frontend.checkout.cart');
    }

    /**
     * @return FormAPI
     */
    protected function _getAPI()
    {
        return (new FormAPI($this->getKey(), $this->getCustomField('mode')))->setVendor($this->getCustomField('vendor'));
    }

    /**
     * Saves additional response data from sage pay using TxCode
     * @param string $crypt
     * @return Model|bool
     */
    protected function _updatePaymentData($crypt)
    {
        if ($crypt) {
            $sagePayResponse = $this->_getAPI()->decodeCrypt($crypt);
            if ($paymentDetails = SagePay\Model::findWithTxCode($sagePayResponse['VendorTxCode'])) {
                // don't update if previous saved "OK" response exists
                $paymentData = $paymentDetails->getPaymentData();
                if (array_key_exists('Status', $paymentData) && $paymentData['Status'] == 'OK') {
                    return true;
                }
                // update details
                $paymentDetails->setPaymentData(array_intersect_key($sagePayResponse, array_flip([
                    'VendorTxCode',
                    'Status',
                    'StatusDetail',
                    'AVSCV2',
                    'AddressResult',
                    'CV2Result',
                    '3DSecureStatus',
                    'CardType',
                    'Amount',
                ])));
                if ($paymentDetails->save()) {
                    return $paymentDetails;
                }
            }
        }
        return false;
    }

}
