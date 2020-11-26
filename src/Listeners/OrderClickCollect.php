<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\OrderPaymentMethods;
use CoasterCommerce\Core\Model\Order\Shipping\ClickCollect as ClickCollectShipping;
use CoasterCommerce\Core\Model\Order\Payment\ClickCollect as ClickCollectPayment;

class OrderClickCollect
{

    /**
     * @param OrderPaymentMethods $event
     */
    public function handle(OrderPaymentMethods $event)
    {
        $shippingMethod = $event->order->getShippingMethod();
        if ($shippingMethod instanceof ClickCollectShipping) {
            if ($shippingMethod->getCustomField('collect_payment_only')) {
                $event->methods = array_filter($event->methods, function ($method) {
                    return $method instanceof ClickCollectPayment; // remove non click & collect payment methods
                });
            }
        } else {
            $event->methods = array_filter($event->methods, function ($method) {
                return !($method instanceof ClickCollectPayment); // remove click & collect payment methods
            });
        }
    }

}

