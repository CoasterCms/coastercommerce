<?php
/**
 * @var CoasterCommerce\Core\Model\Order $order
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row">

                    <h1 class="card-title col-sm-7 mb-5">
                        Order {{ $order->order_number }}
                    </h1>

                    <div class="col-sm-5 mb-5 text-right">&nbsp;
                        @if (($order->status ? $order->status->state : '') !== \CoasterCommerce\Core\Model\Order::STATUS_QUOTE)
                        <a href="{{ route('coaster-commerce.admin.order.email.order', ['id' => $order->id]) }}" class="btn btn-warning mb-2">
                            <i class="fa fa-envelope"></i> &nbsp; Resend Order Email
                        </a> &nbsp;
                        @endif
                        <a href="{{ route('coaster-commerce.admin.order.list') }}" class="btn btn-success mb-2">
                            <i class="fa fa-arrow-circle-left"></i> &nbsp; Return to order list
                        </a>
                    </div>

                </div>

                <div class="row">
                    <label class="col-sm-4 col-form-label"><strong>Order Placed:</strong></label>
                    <div class="col-sm-8">{{ $order->order_placed ? $order->order_placed->format('h:i:sa l jS F Y') : '' }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-4 col-form-label" for="order_status"><strong>Order Status:</strong></label>
                    <div class="col-sm-8">
                        {{ $order->status ? $order->status->name : $order->order_status }}
                        <a href="javascript:void(0)" id="statusButton">(update)</a>
                    </div>
                    <div class="col-sm-8 mb-2" id="statusUpdate" style="display: none">
                        {!! $formBuilder->open(['url' => route('coaster-commerce.admin.order.save.status', ['id' => $order->id])]) !!}
                        <div class="input-group">
                            <select name="order_status" class="form-control" id="order_status">
                                @foreach($statuses as $code => $name)
                                    <option value="{{ $code }}" {{ $code == $order->order_status ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        </div>
                        {!! $formBuilder->close() !!}
                    </div>
                </div>
                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label"><strong>Customer</strong> &nbsp; ({{ $order->customer_id ? $order->customer->group->name : 'GUEST' }})</label>
                    <div class="col-sm-8">
                        @if ($order->customer_id)
                        <a href="{{ route('coaster-commerce.admin.customer.edit', ['id' => $order->customer_id]) }}">{{ $order->email ?: $order->customer->email }}</a>
                        @else
                        {{ $order->email }}
                        @endif
                    </div>
                </div>

                {!! view('coaster-commerce::admin.order.view.table', ['order' => $order]) !!}

            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">

                <h2 class="mb-4">Notes / Order Comments</h2>

                {!! $formBuilder->open(['url' => route('coaster-commerce.admin.order.note', ['id' => $order->id])]) !!}

                @foreach($order->notes as $note)
                    <div class="row">
                        <div class="col-3">
                            {{ $note->author ?: 'Customer' }} @ {{ $note->created_at->format('H:i:s d/m/Y') }}<br />
                            @if ($note->author)
                                {!!  $note->customer_notified ? '<div class="text-success">Notified Customer</div>' : '<div class="text-danger">Admin only</div>' !!}
                            @else
                                <div class="text-success">Order Comment</div>
                            @endif
                        </div>
                        <div class="col-9">
                            {!! nl2br($note->note) !!}
                        </div>
                        <div class="col-12">
                            <div class="border-bottom mb-4">&nbsp;</div>
                        </div>
                    </div>
                @endforeach

                {!! $formBuilder->textarea('note', null, ['class' => 'form-control', 'rows' => 3]) !!}

                <div class="form-check mt-2">
                    {!! $formBuilder->checkbox('notify', 1, false, ['class' => 'form-check-input', 'id' => 'notify_customer']) !!}
                    <label for="notify_customer" class="form-check-label">Notify customer (will send email and be visible in customers account if they have one)</label>
                </div>

                {!! $formBuilder->submit('Add Note', ['class' => 'btn btn-success mt-2']) !!}

                {!! $formBuilder->close() !!}

            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">

                <h2 class="mb-4">Order Payment</h2>

                <div class="row">
                    <div class="col-sm-6">
                        <h4>Address</h4>
                        @if ($billingAddress = $order->billingAddress())
                            {!! $billingAddress->render() !!}
                            <a href="{{ route('coaster-commerce.admin.order.address.update', ['id' => $order->id, 'type' => $billingAddress->type]) }}">Edit</a>
                        @else
                            <p>None set</p>
                        @endif
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
                        @if (!$order->payment_confirmed)
                            <a href="{{ route('coaster-commerce.admin.order.save.paid', ['id' => $order->id]) }}" class="btn btn-success">Mark Paid</a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">

                <h2 class="mb-4">Order Shipping</h2>

                <div class="row">
                    <div class="col-sm-6">
                        <h4>Address</h4>
                        @if ($shippingAddress = $order->shippingAddress())
                            {!! $shippingAddress->render() !!}
                            <a href="{{ route('coaster-commerce.admin.order.address.update', ['id' => $order->id, 'type' => $shippingAddress->type]) }}">Edit</a>
                        @else
                            <p>None set</p>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        @if ($order->shipping_method)
                        <h4>Method</h4>
                        <p>{{ $order->getShippingMethod() ? $order->getShippingMethod()->name : $order->shipping_method }}</p>
                        @if ($order->shipment_sent)
                            <p>Shipment sent:<br /> {{ $order->shipment_sent->format('H:i:s d/m/Y') }}</p>
                            @foreach($order->shipments as $shipment)
                                <b>Courier: </b> {{ $shipment->courier->name }} (<a href="{{ $shipment->link() }}" target="_blank">Track</a>)<br />
                                <b>Tracking Number:</b> {{ $shipment->number }}
                            @endforeach
                        @else
                            {!! $formBuilder->open(['url' => route('coaster-commerce.admin.order.save.shipped', ['id' => $order->id])]) !!}
                            {!! $formBuilder->select('tracking_courier', \CoasterCommerce\Core\Model\Order\ShippingCourier::pluck('name', 'id'), null, ['class' => 'form-control']) !!}
                            {!! $formBuilder->text('tracking_number', null, ['class' => 'form-control', 'placeholder' => 'Tracking Number']) !!}
                            <div class="form-check mb-4">
                                {!! $formBuilder->checkbox('send_email', 1, true, ['class' => 'form-check-input', 'id' => 'send_email']) !!}
                                <label for="send_email" class="form-check-label">Send shipping email ?</label>
                            </div>
                            <button class="btn btn-success">Mark Shipped</button>
                            {!! $formBuilder->close() !!}
                        @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#statusButton').click(function() {
                $('#statusUpdate').show();
                $(this).parent().hide();
            })

        });
    </script>
@append