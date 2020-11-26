<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Change Password</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">

            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}

        </div>
        <div class="col-sm-9">

        @if (!isset($success))

            {!! $formBuilder->open(['class' => 'form-horizontal']) !!}

            <div class="form-group row">
                <label for="current_password" class="col-sm-2 col-form-label">Current password</label>
                <div class="col-sm-10">
                    {!! $formBuilder->password('current_password', ['class' => 'form-control', 'id' => 'current_password']) !!}
                    <small class="form-text text-danger">{{ $errors->first('current_password') }}</small>
                </div>
            </div>

            <hr>

            <div class="form-group row">
                <label for="password" class="col-sm-2 col-form-label">Password</label>
                <div class="col-sm-10">
                    {!! $formBuilder->password('password', ['class' => 'form-control', 'id' => 'password']) !!}
                    <small class="form-text text-danger">{{ $errors->first('password') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="password_confirmation" class="col-sm-2 col-form-label">Confirm Password</label>
                <div class="col-sm-10">
                    {!! $formBuilder->password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation']) !!}
                    <small class="form-text text-danger">{{ $errors->first('password_confirmation') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-10">
                    {!! $formBuilder->submit('Change password', ['class' => 'btn btn-default', 'style' => 'margin-top: 15px']) !!}
                </div>
            </div>

            {!! $formBuilder->close() !!}

            @else

            <p>Password updated!</p>

            @endif

        </div>
    </div>

</div>

{!! $cart->view('sections.footer') !!}