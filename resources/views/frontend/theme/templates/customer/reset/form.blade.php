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
            {!! $formBuilder->open(['class' => 'form-horizontal', 'route' => 'coaster-commerce.frontend.customer.reset.email']) !!}

            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('email', null, ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                    <small class="form-text text-danger">{{ $errors->first('email') }}</small>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-offset-2 col-sm-10">
                    {!! $formBuilder->submit('Send Password Reset Email', ['class' => 'btn btn-default']) !!}
                    <br /><br />
                    <a href="{{ $cart->route('frontend.customer.login') }}">Return to login</a>
                </div>
            </div>

            {!! $formBuilder->close() !!}
        </div>
    </div>

</div>

{!! $cart->view('sections.footer') !!}