<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */

$customer = $cart->getCustomer();
$orders = $customer->submittedOrders()->orderBy('order_placed', 'desc')->paginate(10);
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

            @if ($orders->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Order Total</th>
                        <th>Date Placed</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            {{ $order->order_number }}
                        </td>
                        <td>
                            {!! new \CoasterCommerce\Core\Currency\Format($order->order_total_inc_vat) !!}
                        </td>
                        <td>
                            {{ $order->order_placed ? $order->order_placed->format('jS F Y') : '' }}
                        </td>
                        <td>
                            {{ $order->status ? $order->status->name : $order->order_status }}
                        </td>
                        <td>
                            <a href="{{ route('coaster-commerce.frontend.customer.account.order.view', ['id' => $order->id]) }}">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No orders found.</p>
            @endif

        </div>
    </div>

</div>

@section('scripts')
<script>

</script>
@append

{!! $cart->view('sections.footer') !!}