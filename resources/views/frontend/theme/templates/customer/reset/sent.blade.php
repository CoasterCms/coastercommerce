<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
?>

{!! $cart->view('sections.head') !!}


<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Password Reset</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <p>
                If the account exists, an email with instructions on resetting your password will have been sent.<br />
                Please allow a minute for it to arrive in your inbox.
            </p>

            <br />

            <a href="{{ $cart->route('frontend.customer.login') }}">Return to login</a>

        </div>
    </div>
</div>

{!! $cart->view('sections.footer') !!}