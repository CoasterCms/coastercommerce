<?php
/**
 * @var CoasterCommerce\Core\Model\Order $order
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Currency\Format;
$cataloguePrices = Setting::getValue('vat_catalogue_display');
$cataloguePrices = $order->totalVAT() ? $cataloguePrices : 'inc'; // setting to inc should hide any vat
$orderCalcVAT = $order->order_vat_type == 'order' ? 'ex' : 'inc';
$orderSummary = Setting::getValue('vat_cart_summary_display') ?: $orderCalcVAT;
$totalsSuffix = '';
if ($order->totalVAT()) {
    $totalsSuffix = $orderSummary == 'inc' ? ' (Inc. VAT)' : ' (Ex. VAT)';
}
$showUnitVAT = $order->order_vat_type == 'unit';
$showItemVAT = $order->order_vat_type == 'item';
$tableCols = (($showUnitVAT || $showItemVAT) && $cataloguePrices != 'inc') ? 5 : 4;
?>

<table @if(!isset($email))class="table table-striped" @endif style="width: 100%; text-align: left;">
    <thead>
    <tr>
        <th class="product-name" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Product</th>
        @if ($showUnitVAT && $cataloguePrices != 'inc')
        <th class="product-price" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Price (Ex. VAT)</th>
        <th class="product-price-inc" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Price (Inc. VAT)</th>
        @else
        <th class="product-price" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Price</th>
        @endif
        <th class="product-quantity" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Quantity</th>
        @if ($showItemVAT && $cataloguePrices != 'inc')
        <th class="product-subtotal" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif">Total (Ex. VAT)</th>
        <th class="product-subtotal-inc" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif text-align: right;">Total (Inc. VAT)</th>
        @else
        <th class="product-subtotal" style="@if(isset($email))border-bottom: 1px solid #edeff2;@endif text-align: right;">Total{{ $totalsSuffix }}</th>
        @endif
    </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td class="product-name" @if(isset($email))style="padding: 5px 0;" @endif>
                @if(isset($email))<b>{{ $item->item_name }}</b> @else {{ $item->item_name }}  @endif
                @if ($options = $item->getDataArray('link'))
                <dl class="variation mb-0 mt-2">
                    @foreach($options as $optionLabel => $optionValue)
                        <dt>{{ $optionLabel }}:</dt>
                        <dd>{!! $optionValue !!}</dd>
                    @endforeach
                </dl>
                @endif
            </td>
            @if ($showUnitVAT && $cataloguePrices != 'inc')
                <td class="product-price" @if(isset($email))style="padding: 5px 0;" @endif>
                    <span class="amount">{!! new Format($item->item_price_ex_vat) !!}</span>
                </td>
                <td class="product-price-inc" @if(isset($email))style="padding: 5px 0;" @endif>
                    <span class="amount">{!! new Format($item->item_price_inc_vat) !!}</span>
                </td>
            @else
                <td class="product-price" @if(isset($email))style="padding: 5px 0;" @endif>
                    <span class="amount">{!! new Format($item->getCost('price', ($orderSummary == 'ex' || $cataloguePrices == 'ex') ? 'ex' : 'inc')) !!}</span>
                </td>
            @endif
            <td class="product-quantity" @if(isset($email))style="padding: 5px 0;" @endif>
                {{ $item->item_qty }}
            </td>
            @if ($showItemVAT && $cataloguePrices != 'inc')
                <td class="product-subtotal" @if(isset($email))style="padding: 5px 0;" @endif>
                    <span class="amount">{!! new Format($item->item_total_ex_vat) !!}</span>
                </td>
                <td class="product-subtotal-inc" style="text-align: right; @if(isset($email))padding: 5px 0; @endif ">
                    <span class="amount">{!! new Format($item->item_total_inc_vat) !!}</span>
                </td>
            @else
                <td class="product-subtotal" style="text-align: right; @if(isset($email))padding: 5px 0; @endif ">
                    <span class="amount">{!! new Format($item->getCost('total', $orderSummary)) !!}</span>
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
    <tfoot style="text-align: right;">

        @if ($order->order_coupon)
            {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => ucwords($order->order_coupon), 'field' =>  'Coupon code', 'fieldCols' => $tableCols-1]) !!}
        @endif
    
        {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $order->getCost('subtotal', $orderSummary), 'field' => 'Subtotal' . $totalsSuffix, 'fieldCols' => $tableCols-1]) !!}
    
        @if ($order->order_subtotal_discount_inc_vat > 0)
            {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $order->getCost('subtotal_discount', $orderSummary), 'field' => 'Subtotal Discount', 'discount' => true, 'fieldCols' => $tableCols-1]) !!}
        @endif
    
        @if ($order->shipping_method)
            {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $order->getCost('shipping', $orderSummary), 'field' => 'Shipping' . $totalsSuffix, 'fieldCols' => $tableCols-1]) !!}
            @if ($order->order_shipping_discount_inc_vat > 0)
                {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $order->getCost('shipping_discount', $orderSummary), 'field' => 'Shipping Discount', 'discount' => true, 'fieldCols' => $tableCols-1]) !!}
            @endif
        @endif
    
        @if ($vat = $order->totalVAT())
            {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $vat, 'field' => 'VAT', 'showZero' => true, 'fieldCols' => $tableCols-1]) !!}
        @endif
    
        {!! view('coaster-commerce::admin.order.view.summary.footer-row', ['value' => $order->order_total_inc_vat, 'field' =>  'Order Total', 'fieldCols' => $tableCols-1]) !!}

    </tfoot>
</table>
