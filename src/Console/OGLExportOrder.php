<?php

namespace CoasterCommerce\Core\Console;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Carbon\Carbon;

class OGLExportOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ogl:order-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export order to csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // should run hourly, but check last month of orders just in case
        $orders = Order::whereDate('order_placed', '>', (new Carbon())->modify('-1 month'))->get();

        foreach ($orders as $order) {
            $file = base_path('ogl/orders/' . substr($order->order_number, 1) . '.csv');
            $archivedFile = base_path('ogl/orders/Archive/' . substr($order->order_number, 1) . '.csv');
            if (!is_null($order->order_number) && !file_exists($file) && !file_exists($archivedFile)) {
                $this->writeCsv($file, $order);
            }
        }
    }

    /**
     * @param string $file
     * @param Order $order
     */
    protected function writeCsv($file, $order)
    {
        $customer = $order->customer;
        $billing_address = $order->billingAddress();
        $shipping_address = $order->shippingAddress();

        $customer_reference = '';
        if ($customer) {
            if ($customer_reference_model = $customer->meta->where('key', 'customer_reference')->first()) {
                $customer_reference = $customer_reference_model->value ? str_pad($customer_reference_model->value, 6, '0', STR_PAD_LEFT) : null;
            }
        }

        $webRecord = [
            'WEB', //WEB
            $order->email, // Users email address/login id,
            '',
            $order->order_number, // Webref
            $billing_address->first_name . ' ' . $billing_address->last_name, // Contact Name
            $billing_address->email, // Contact Email
            $billing_address->phone, // Contact Telephone Number
            '',
            '',
            '',
            $billing_address->country_iso3, // Country Code
            'N', // Marketing Opt Out Flag
            '',
            '',
            '',
            '',
            '',
            '',
            $customer_reference ? 'N' : 'Y' // Create new account
        ];
        if (!$customer_reference) { // Extra fields for new OGL account only
            $webRecord = array_merge($webRecord, [
                Str::random(10), // Password (required but not used)
                $billing_address->first_name . ' ' . $billing_address->last_name, // Contact Name
                $billing_address->address_line_1, // Billing Address 1
                $billing_address->address_line_2, // Billing Address 2
                $billing_address->town, // Billing Address 3
                $billing_address->county, // Billing Address 4
                $billing_address->postcode, // Billing Post Code
                $shipping_address->first_name . ' ' . $shipping_address->last_name, // Delivery Name
                $shipping_address->address_line_1, // Delivery Address 1
                $shipping_address->address_line_2, // Delivery Address 2
                $shipping_address->town, // Delivery Address 3
                $shipping_address->county, // Delivery Address 4
                $shipping_address->postcode, // Delivery Post Code
                '',
                ''
            ]);
        }
        $csvRecords[] = $webRecord;

        $csvRecords[] = [
            'HDR', // HDR
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $customer_reference, // Customer reference
            $shipping_address->first_name . ' ' . $shipping_address->last_name, // Delivery Name
            $shipping_address->address_line_1, // Delivery Address 1
            $shipping_address->address_line_2, // Delivery Address 2
            $shipping_address->town, // Delivery Address 3
            $shipping_address->county, // Delivery Address 4
            $shipping_address->postcode, // Delivery Post Code
            '',
            $order->order_placed ? $order->order_placed->format('dmy') : '', // Order Date
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            str_replace('#', '', $order->order_number), // File No
            Carbon::now()->format('dmy'),
            'N', // Cash Sale
            '',
            '',
            $order->order_shipping_ex_vat, // Carriage Amount
            '',
            'Y', // Retain Prices
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        foreach ($order->items as $item) {
            $csvRecords[] = [
                'OLD', // OLD
                '',
                $item->item_name, // Stock Code
                '',
                $item->item_qty, // Order Quantity
                '',
                '',
                $item->item_price_ex_vat, // Unit Price
                '',
                ''
            ];
        }

        $writer = Writer::createFromPath($file, 'w+');
        $writer->insertAll($csvRecords);
    }
}
