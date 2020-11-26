<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Abandoned Cart</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <p>You have been unsubscribed from abandoned cart reminders.</p>
            <p>Email: {{ $email }}</p>
        </div>
    </div>

</div>

{!! $cart->view('sections.footer') !!}