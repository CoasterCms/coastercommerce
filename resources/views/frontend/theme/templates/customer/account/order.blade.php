<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">My Orders</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">

            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}

        </div>
        <div class="col-sm-9">

            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-4">Order {{ $order->order_number }}</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('coaster-commerce.frontend.customer.account.order.reorder', ['id' => $order->id]) }}" class="btn btn-default">Reorder</a>
                </div>
            </div>

            <p class="mb-4">
                <strong>Order Placed:</strong> {{ $order->order_placed ? $order->order_placed->format('jS F Y \a\t h:i:sa') : '' }}<br />
                <strong>Order Status:</strong> {{ $order->status ? $order->status->name : $order->order_status }}
            </p>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h3 class="mb-3">Billing Details</h3>
                    <div class="row">
                        <div class="col-sm-6">
                            <h4>Address</h4>
                            {!! $order->billingAddress() ? $order->billingAddress()->render() : 'None set' !!}
                        </div>
                        <div class="col-sm-6">
                            <h4>Method</h4>
                            @if ($paymentMethod = $order->getPaymentMethod())
                                <p>{{ $paymentMethod->name }}</p>
                                {!! $paymentMethod->showDetails() !!}
                            @else
                                <p>{{ $order->payment_method }}</p>
                                @if ($order->payment_confirmed)
                                    <p>Payment confirmed:<br /> {{ $order->payment_confirmed->format('H:i:s d/m/Y') }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <h3 class="mb-3">Shipping Details</h3>
                    <div class="row">
                        <div class="col-sm-6">
                            <h4>Address</h4>
                            {!! $order->shippingAddress() ? $order->shippingAddress()->render() : 'None set' !!}
                        </div>
                        <div class="col-sm-6">
                            @if ($order->shipping_method)
                            <h4>Method</h4>
                            <p>{{ $order->getShippingMethod() ? $order->getShippingMethod()->name : $order->shipping_method }}</p>
                            @if ($order->shipment_sent)
                                <p>Shipment sent:<br /> {{ $order->shipment_sent->format('H:i:s d/m/Y') }}</p>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @php $notes = $order->notes()->where('customer_notified', '=', 1)->get() @endphp

            @if ($notes->count())
            <h3 class="mb-4">Comments</h3>

            @foreach($order->notes()->where('customer_notified', '=', 1)->get() as $note)
                <div class="row">
                    <div class="col-3">
                        {{ $note->author ?: 'Customer' }} @ {{ $note->created_at->format('H:i:s d/m/Y') }}
                    </div>
                    <div class="col-9">
                        {!! nl2br($note->note) !!}
                    </div>
                    <div class="col-12">
                        <div class="border-bottom mb-4">&nbsp;</div>
                    </div>
                </div>
            @endforeach

            @endif

            <h3 class="mb-4">Items Ordered</h3>

            {!! view('coaster-commerce::admin.order.view.table', ['order' => $order]) !!}

        </div>
    </div>

</div>

@section('coastercommerce.scripts')
<script>

</script>
@append

{!! $cart->view('sections.footer') !!}