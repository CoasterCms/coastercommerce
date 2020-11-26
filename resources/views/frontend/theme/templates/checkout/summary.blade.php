<?php
/**
 * @var CoasterCommerce\Core\Session\Cart $cart
 */
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Currency\Format;
$cataloguePrices = Setting::getValue('vat_catalogue_display');
$cartCalcVAT = $cart->order_vat_type == 'order' ? 'ex' : 'inc';
$cartSummary = Setting::getValue('vat_cart_summary_display') ?: $cartCalcVAT;
?>

<table class="table">
    <thead>
    <tr class="bg-light">
        <th colspan="3">Order Summary</th>
    </tr>
    </thead>
    <tbody>
    @foreach($cart->items as $item)
        <tr>
            <td>
                {{ $item->item_name }}
                <dl class="variation">
                    @foreach($item->getDataArray('link') as $optionLabel => $optionValue)
                        <dt>{{ $optionLabel }}:</dt>
                        <dd>{!! $optionValue !!}</dd>
                    @endforeach
                </dl>
            </td>
            <td>x{{ $item->item_qty }}</td>
            <td class="text-right">{!! new Format($item->getCost('total', $cartSummary)) !!}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $cart->getCost('subtotal', $cartSummary), 'field' =>  'Subtotal', 'class' => 'bg-light']) !!}

    @if ($cart->order_subtotal_discount_inc_vat > 0)
        {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $cart->getCost('subtotal_discount', $cartSummary), 'field' => 'Subtotal Discount', 'discount' => true, 'class' => 'bg-light']) !!}
    @endif

    @if ($cart->shipping_method)
        {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $cart->getCost('shipping', $cartSummary), 'field' => 'Shipping', 'class' => 'bg-light']) !!}
        @if ($cart->order_shipping_discount_inc_vat > 0)
            {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $cart->getCost('shipping_discount', $cartSummary), 'field' => 'Shipping Discount', 'discount' => true, 'class' => 'bg-light']) !!}
        @endif
    @endif

    @if ($vat = $cart->totalVAT())
        {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $vat, 'field' =>  'VAT', 'class' => 'bg-light']) !!}
    @endif

    {!! $cart->view('templates.checkout.summary.footer-row', ['value' => $cart->order_total_inc_vat, 'field' =>  'Grand Total', 'style' => 'background: #e2e3e5']) !!}
    </tfoot>
</table>

@if ($shippingMethod = $cart->getShippingMethod())
    <table class="table mt-4 border-bottom">
        <thead>
        <tr class="bg-light">
            <th>Shipping Method</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><p>{!! $shippingMethod->name !!}</p></td>
        </tr>
        </tbody>
    </table>
@endif

@foreach($cart->addresses as $address)
    <table class="table mt-4 border-bottom">
        <thead>
        <tr class="bg-light">
            <th>{{ ucwords($address->type) }} Address</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{!! $address->render() !!}</td>
        </tr>
        </tbody>
    </table>
@endforeach

