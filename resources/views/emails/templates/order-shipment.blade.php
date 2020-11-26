@component('coaster-commerce::emails.layout')

<p>
    Hello {{ $order->billingAddress()->first_name . ' ' . $order->billingAddress()->last_name }},<br />
    <br />
    Your order has been shipped!
    @php $shipment = $order->shipments->first() @endphp
    @if ($shipment && $shipment->courier)
        <br /><br />
        <b>Courier:</b> {{ $shipment->courier->name }} @if ($trackingLink = $shipment->link())(<a href="{{ $trackingLink }}" target="_blank">Tracking Service</a>)@endif<br />
        @if ($shipment->number)<b>Tracking Number:</b> {{ $shipment->number }}@endif
    @endif
    <br /><br />
    If you have questions about your order, you can email us at {{ \CoasterCommerce\Core\Model\Setting::getValue('store_email') }} or call us on {{ \CoasterCommerce\Core\Model\Setting::getValue('store_phone') }}.
    Your order details are below. Thank you again for your business.
</p>

<h1 style="margin-top: 20px;">Order {{ $order->order_number }}</h1>

<table style="width:100%">
    <tbody>
    <tr>
        @foreach(array_filter(['Billing Address' => $order->billingAddress(), 'Shipping Address' => $order->shippingAddress()]) as $header => $address)
            <td style="width:48%;padding-right:2%;">
                <h2>{{ $header }}</h2>
                @php
                    $lines = [
                        $address->first_name . ' ' .$address->last_name,
                        $address->company,
                        $address->address_line_1,
                        $address->address_line_2,
                        $address->town,
                        $address->county,
                        $address->postcode,
                        $address->country(),
                        $address->email ? ('Email: ' . $address->email) : null,
                        $address->phone ? 'Tel: ' . $address->phone : null,
                    ];
                @endphp
                {!! implode('<br />', array_filter(array_map('trim', $lines))) !!}
            </td>
        @endforeach
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 10px;">
            @if ($order->payment_method)
                <b>Payment Method:</b> &nbsp; {{ $order->getPaymentMethod() ? $order->getPaymentMethod()->name : $order->payment_method }}<br />
            @endif
            @if ($order->shipping_method)
                <b>Shipping Method:</b> &nbsp; {{ $order->getShippingMethod() ? $order->getShippingMethod()->name : $order->shipping_method }}<br />
            @endif
        </td>
    </tr>
    </tbody>
</table>

<h2 style="margin-top: 20px;">Order Details</h2>

{!! view('coaster-commerce::admin.order.view.table', ['order' => $order, 'email' => true]) !!}

@endcomponent