<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Order;

class OrderShipmentMailable extends OrderMailable
{

    /**
     * @return static
     */
    public function build()
    {
        $toAddress = $this->order->email;
        if ($shippingAddress = $this->order->shippingAddress()) {
            $toAddress = $shippingAddress->email ?: $toAddress;
        }
        return $this
            ->to($toAddress)
            ->markdown('coaster-commerce::emails.templates.order-shipment', [
                'order' => $this->order
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $order = parent::testData()[0];

        $courier = new Order\ShippingCourier();
        $courier->name = 'The Postage People';
        $courier->link = '#';
        $shipment = new Order\ShipmentTracking();
        $shipment->number = '123456789';
        $shipment->courier = $courier;
        $order->shipments = collect([$shipment]);

        return [$order];
    }

}
