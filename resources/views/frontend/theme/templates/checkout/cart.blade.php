<?php
/**
 * @var CoasterCommerce\Core\Session\Cart $cart
 */
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Currency\Format;
$cataloguePrices = Setting::getValue('vat_catalogue_display');
$cataloguePrices = $cart->totalVAT() ? $cataloguePrices : 'inc'; // setting to inc should hide any vat
$cartCalcVAT = $cart->order_vat_type == 'order' ? 'ex' : 'inc';
$cartSummary = Setting::getValue('vat_cart_summary_display') ?: $cartCalcVAT;
$totalsSuffix = '';
if ($cart->totalVAT()) {
    $totalsSuffix = $cartSummary == 'inc' ? ' (Inc. VAT)' : ' (Ex. VAT)';
}
$showUnitVAT = $cart->order_vat_type == 'unit';
$showItemVAT = $cart->order_vat_type == 'item';
$tableCols = (($showUnitVAT || $showItemVAT) && $cataloguePrices != 'inc') ? 9 : 8;
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">

        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Basket</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>

        <div class="col-sm-12">
            @if ($cart->getItemCount())
                {!! $formBuilder->open(['route' => 'coaster-commerce.frontend.checkout.cart.update', 'id' => 'cartForm']) !!}
                <table class="table" id="cartTable">
                    <thead>
                    <tr>
                        <th colspan="2">Product</th>
                        @if ($showUnitVAT && $cataloguePrices != 'inc')
                            <th class="product-price">Price (Ex. VAT)</th>
                            <th class="product-price-inc">Price (Inc. VAT)</th>
                        @else
                            <th class="product-price">Price</th>
                        @endif
                        <th class="product-quantity">Quantity</th>
                        @if ($showItemVAT && $cataloguePrices != 'inc')
                            <th class="product-subtotal">Total (Ex. VAT)</th>
                            <th class="product-subtotal-inc">Total (Inc. VAT)</th>
                        @else
                            <th class="product-subtotal">Total{{ $totalsSuffix }}</th>
                        @endif
                        <th class="product-remove">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cart->items as $item)
                        <tr class="cart_item">
                            <td class="product-thumbnail">
                                <a href="{{ $item->product->getUrl() }}">
                                    <img src="{{ $item->getImage() }}" class="img-fluid" alt="{{ $item->item_name }}">
                                </a>
                            </td>
                            <td class="product-name">
                                <a href="{{ $item->product->getUrl() }}">{!! $item->item_name !!}</a>
                                <dl class="variation">
                                    @foreach($item->getDataArray('link') as $optionLabel => $optionValue)
                                        <dt>{{ $optionLabel }}:</dt>
                                        <dd>{!! $optionValue !!}</dd>
                                    @endforeach
                                </dl>
                            </td>

                            @if ($showUnitVAT && $cataloguePrices != 'inc')
                                <td class="product-price">
                                    <span class="amount">{!! new Format($item->item_price_ex_vat) !!}</span>
                                </td>
                                <td class="product-price-inc">
                                    <span class="amount">{!! new Format($item->item_price_inc_vat) !!}</span>
                                </td>
                            @else
                                <td class="product-price">
                                    <span class="amount">{!! new Format($item->getCost('price', ($cartSummary == 'ex' || $cataloguePrices == 'ex') ? 'ex' : 'inc')) !!}</span>
                                </td>
                            @endif

                            <td class="product-quantity">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button type="button" data-action="minus" class="btn"><span class="fa fa-minus"></span></button>
                                    </div>
                                    <input
                                            type="number" name="qty[{{ $item->id }}]" value="{{ $item->item_qty }}" title="Qty"
                                            step="1" min="0" size="4" class="form-control qty"
                                            data-price-ex="{{ $item->item_price_ex_vat }}"
                                            data-price-inc="{{ $item->item_price_inc_vat }}"
                                    >
                                    <div class="input-group-append">
                                        <button type="button" data-action="plus" class="btn"><span class="fa fa-plus"></span></button>
                                    </div>
                                </div>
                            </td>

                            @if ($showItemVAT && $cataloguePrices != 'inc')
                                <td class="product-subtotal">
                                    <span class="amount">{!! new Format($item->item_total_ex_vat) !!}</span>
                                </td>
                                <td class="product-subtotal-inc">
                                    <span class="amount">{!! new Format($item->item_total_inc_vat) !!}</span>
                                </td>
                            @else
                                <td class="product-subtotal">
                                    <span class="amount">{!! new Format($item->getCost('total', $cartSummary)) !!}</span>
                                </td>
                            @endif

                            <td class="product-remove">
                                <a href="{{ route('coaster-commerce.frontend.checkout.cart.remove', ['id' => $item->id]) }}" title="Remove this item">
                                    <span class="fa fa-trash"></span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="{{ $tableCols }}" class="actions text-right">
                            @if (!$cart->order_coupon)
                                <div class="row">
                                    <div class="input-group mb-3 col-lg-9 col-xl-6 ml-auto">
                                        <input type="text" name="order_coupon" class="form-control" placeholder="Enter Coupon" aria-label="Enter Coupon">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" value="Apply Coupon Code" type="submit" name="action">Apply Coupon Code</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="order_coupon" value="{{ $cart->order_coupon }}" />
                                <p>Coupon: <span>{{ $cart->order_coupon }}</span> <a href="javascript:void(0)" class="text-danger" id="removeCoupon">(remove)</a></p>
                            @endif
                            <p>Subtotal {{ $totalsSuffix }}: <span>{!! new Format($cart->getCost('subtotal', $cartSummary)) !!}</span></p>
                            @if ($cart->order_subtotal_discount_inc_vat > 0)
                                <p>Subtotal Discount: <span>-{!! new Format($cart->getCost('subtotal_discount', $cartSummary)) !!}</span></p>
                            @endif
                            @if ($cart->shipping_method)
                                <p>Shipping {{ $totalsSuffix }}: <span>{!! new Format($cart->getCost('shipping', $cartSummary)) !!}</span></p>
                                @if ($cart->order_shipping_discount_inc_vat > 0)
                                    <p>Shipping Discount: <span>-{!! new Format($cart->getCost('shipping_discount', $cartSummary)) !!}</span></p>
                                @endif
                            @endif
                            @if ($vat = $cart->totalVAT())
                                <p>VAT: <span>{!! new Format($vat) !!}</span></p>
                            @endif
                            <p>Order Total: <span>{!! new Format($cart->order_total_inc_vat) !!}</span></p>
                            <a href="{{ route('coaster-commerce.frontend.checkout.cart.clear') }}" class="btn btn-default">Clear Basket</a>
                            <input type="submit" name="action" class="btn btn-default" value="Update Basket">
                            <input type="submit" name="action" class="btn btn-primary" value="Proceed to Checkout">
                        </td>
                    </tr>
                    </tfoot>
                </table>

                {!! $formBuilder->close() !!}
            @else
                <p>Basket is empty</p>
            @endif
        </div>
    </div>
</div>

@section('coastercommerce.scripts')
    <script>
        jQuery(document).ready(function ($) {
            $('#removeCoupon').click(function () {
                $('input[name=order_coupon]').val('');
                $('#cartForm').submit();
            });
            $('.product-quantity button').click(function () {
                var quantityEl = $(this).closest('.product-quantity').find('.qty');
                var quantityVal = quantityEl.val();
                $(this).data('action') === 'minus' ? quantityVal-- : quantityVal++;
                quantityEl.val(quantityVal < 0 ? 0 : quantityVal).trigger('change');
            });
        });
    </script>
@endsection

{!! $cart->view('sections.footer') !!}
