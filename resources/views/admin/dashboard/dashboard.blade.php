<?php
use CoasterCommerce\Core\Currency\Format;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Dashboard</h1>
                <p><strong>Latest orders:</strong></p>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order Number</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Total (Inc. Vat)</th>
                    </tr>
                    </thead>
                    @foreach($orders as $order)
                        <tbody>
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->email }}</td>
                            <td>{{ $order->billingAddress() ? $order->billingAddress()->first_name . ' ' . $order->billingAddress()->last_name : null }}</td>
                            <td>{!! new Format($order->order_total_inc_vat) !!}</td>
                        </tr>
                        </tbody>
                    @endforeach
                </table>
                <a href="{{ route('coaster-commerce.admin.order.list') }}">View all orders</a>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Sales</h2>
                <p>
                    <strong>Total orders:</strong> {{ $order_complete_count }}<br />
                    <strong>Total order value:</strong> {!! new Format($order_complete_total) !!}
                </p>
                <h4>Orders fulfilled in past quarter</h4>
                <p>
                    <strong>Total orders:</strong> {{ $order_complete_quarter_count }}<br />
                    <strong>Total order value:</strong> {!! new Format($order_complete_quarter_total) !!}
                </p>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Products</h2>
                <p>
                    <strong>Total products:</strong> {{ $product_count }}<br />
                    <strong>Enabled products:</strong> {{ $product_count_enabled }}
                </p>
                <a href="{{ route('coaster-commerce.admin.product.list') }}">View products</a>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Customers</h2>
                <p>
                    <strong>Total customers:</strong> {{ $customer_count }}<br />
                    <strong>Active in last quarter:</strong> {{ $customer_count_active }}
                </p>
                <a href="{{ route('coaster-commerce.admin.customer.list') }}">View customers</a>
            </div>
        </div>
    </div>
</div>

