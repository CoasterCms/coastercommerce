<?php
/**
 * @var CoasterCommerce\Core\Model\Order $order
 * @var string $email
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row">

                    <div class="col-sm-7 mb-5">
                        <h1 class="card-title">
                            {{ 'Cart for ' . $email }}
                        </h1>
                        <h2>
                            {{ '@ ' . $order->updated_at->format('H:i:s d/m/Y') }}
                        </h2>
                    </div>

                    <div class="col-sm-5 mb-5 text-right">&nbsp;
                        <a href="{{ route('coaster-commerce.admin.customer.abandoned-cart.list') }}" class="btn btn-success mb-2">
                            <i class="fa fa-arrow-circle-left"></i> &nbsp; Return to order list
                        </a>
                    </div>

                </div>

                <div class="row">
                    <label class="col-sm-4 col-form-label"><strong>Customer</strong> &nbsp; ({{ $order->customer_id ? $order->customer->group->name : 'GUEST' }})</label>
                    <div class="col-sm-8">
                        @if ($order->customer_id)
                            <a href="{{ route('coaster-commerce.admin.customer.edit', ['id' => $order->customer_id]) }}">{{ $email }}</a>
                        @else
                            {{ $email }}
                        @endif
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-4 col-form-label"><strong>Shipping</strong></label>
                    <div class="col-sm-8">
                        {{ (($shippingMethod = $order->getShippingMethod()) ? $shippingMethod->name : $order->shipping_method) ?: '-' }}
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label"><strong>Payment</strong></label>
                    <div class="col-sm-8">
                        {{ (($paymentMethod = $order->getPaymentMethod()) ? $paymentMethod->name : $order->payment_method) ?: '-' }}
                    </div>
                </div>

                <div class="row">
                    @if ($billingAddress = $order->billingAddress())
                        <div class="col-sm-6 mb-4">
                            <h4>Billing Address</h4>
                            {!! $billingAddress->render() !!}
                        </div>
                    @endif
                    @if ($shippingAddress = $order->shippingAddress())
                        <div class="col-sm-6 mb-4">
                            <h4>Shipping Address</h4>
                            {!! $shippingAddress->render() !!}
                        </div>
                    @endif
                </div>

                {!! view('coaster-commerce::admin.order.view.table', ['order' => $order]) !!}

            </div>
        </div>
    </div>
</div>
