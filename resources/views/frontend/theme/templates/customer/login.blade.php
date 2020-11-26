<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Customer Login</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-7">
            {!! $formBuilder->open(['class' => 'form-horizontal', 'route' => 'coaster-commerce.frontend.customer.auth']) !!}

            {!! $formBuilder->hidden('login_path', request()->get('login_path')) !!}

            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('email', null, ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                    <small class="form-text text-danger">{{ $errors->first('email') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-sm-2 col-form-label">Password</label>
                <div class="col-sm-10">
                    {!! $formBuilder->password('password', ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-sm-offset-2 col-sm-10">
                    {!! $formBuilder->submit('Login', ['class' => 'btn btn-default']) !!}
                    <br /><br />
                    <a href="{{ $cart->route('frontend.customer.reset') }}">Forgotten Password ?</a>
                </div>
            </div>

            {!! $formBuilder->close() !!}

        </div>

        <div class="offset-sm-1 col-sm-4">
            <h4>New Customers</h4>
            <p>Creating an account has many benefits: check out faster, keep more than one address, track orders and more.</p>
            <a href="{{ $cart->route('frontend.customer.register') }}" class="btn btn-default mt-5">Register Account</a>
        </div>

    </div>

</div>

{!! $cart->view('sections.footer') !!}