<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Order;

class OrderMailable extends AbstractMailable
{

    /**
     * @var Order
     */
    protected $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * @return static
     */
    public function build()
    {
        $toAddresses = [];
        if ($address = $this->order->billingAddress()) {
            $toAddresses[] = $address->email;
        }
        if ($address = $this->order->shippingAddress()) {
            $toAddresses[] = $address->email;
        }
        $toAddresses = array_unique(array_filter($toAddresses));
        $toAddresses = $toAddresses ?: [$this->order->email];
        foreach ($toAddresses as $toAddress) {
            $this->to($toAddress);
        }
        return $this
            ->markdown('coaster-commerce::emails.templates.order', [
                'order' => $this->order
            ]);
    }

    /**
     * @param string $subject
     * @return AbstractMailable
     */
    public function subject($subject)
    {
        $subject = str_replace('%order_number', $this->order->order_number, $subject);
        return parent::subject($subject);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $order = new Order;
        $order->order_number = '#1000001';
        $order->email = 'test@example.com';
        $order->shipping_method = 'free_shipping';
        $order->payment_method = 'cash';
        $order->order_subtotal_ex_vat = 32;
        $order->order_subtotal_inc_vat = 38.4;
        $order->order_shipping_ex_vat = 10;
        $order->order_shipping_inc_vat = 12;
        $order->order_discount_ex_vat = 0;
        $order->order_discount_inc_vat = 0;
        $order->order_total_ex_vat = 42;
        $order->order_total_inc_vat = 50.4;

        $note = new Order\Note();
        $note->note = 'Test comment ...';
        $note->customer_notified = 1;
        $order->notes = collect([$note]);

        $address = new Order\Address();
        $address->first_name = 'Wonderful';
        $address->last_name = 'Customer';
        $address->company = 'Business Inc.';
        $address->address_line_1 = 'Street';
        $address->town = 'Town';
        $address->county = 'County';
        $address->postcode = 'SO99 9ZZ';
        $address->country_iso3 = 'GBR';
        $address->email = $order->email;
        $addressBilling = clone $address;
        $addressBilling->type = 'billing';
        $addressShipping = clone $address;
        $addressShipping->type = 'shipping';
        $order->addresses = collect([$addressBilling, $addressShipping]);

        $item = new Order\Item();
        $item->item_name = 'New Product';
        $item->product_id = 1;
        $item->item_price_ex_vat = 8;
        $item->item_price_inc_vat = 9.6;
        $item->item_qty = 4;
        $item->item_subtotal_ex_vat = 32;
        $item->item_subtotal_inc_vat = 38.4;
        $item->item_discount_ex_vat = 0;
        $item->item_discount_inc_vat = 0;
        $item->item_total_ex_vat = 32;
        $item->item_total_inc_vat = 38.4;

        $order->items = collect([$item]);

        return [$order];
    }

}
