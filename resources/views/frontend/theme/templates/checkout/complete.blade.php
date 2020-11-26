<?php
/**
 * @var CoasterCommerce\Core\Session\Cart $cart
 * @var Collective\Html\FormBuilder $formBuilder
 */
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">

        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Order Received {{ $order->order_number }}</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>

        <div class="col-sm-12">
            <p>Thank you for your order, we will process it shortly.</p>
        </div>

    </div>
</div>

{!! $cart->view('sections.footer') !!}