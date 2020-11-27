<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
$craftClicksEnabled = !! \CoasterCommerce\Core\Model\Setting::getValue('cc_key');
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Customer Register</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {!! $formBuilder->open(['class' => 'form-horizontal', 'route' => 'coaster-commerce.frontend.customer.register']) !!}

            <input type="hidden" name="recaptcha_response" id="recaptchaResponseRF">

            <div class="row">

                <div class="col-sm-6">

                    <h2 class="mb-4">Your Details</h2>

                    <div class="form-group">
                        <label for="first_name">First Name*</label>
                        {!! $formBuilder->text('first_name', null, ['class' => 'form-control'. ($errors->has('first_name') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('first_name') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name*</label>
                        {!! $formBuilder->text('last_name', null, ['class' => 'form-control'. ($errors->has('last_name') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('last_name') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email*</label>
                        {!! $formBuilder->email('email', null, ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('email') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone number</label>
                        {!! $formBuilder->text('phone', null, ['class' => 'form-control'. ($errors->has('phone') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('phone') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Password*</label>
                        {!! $formBuilder->password('password', ['class' => 'form-control'. ($errors->has('password') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('password') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Confirm Password*</label>
                        {!! $formBuilder->password('password_confirmation', ['class' => 'form-control'. ($errors->has('password_confirmation') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('password_confirmation') }}</small>
                    </div>

                </div>

                <div class="col-sm-6">

                    <h2 class="mb-4">Address</h2>

                    @if ($craftClicksEnabled)
                    <div class="form-group">
                        <label for="billing_cc_search">Address Search</label>
                        {!! $formBuilder->text(null, null, ['class' => 'form-control cc_search', 'name' => 'cc_search']) !!}
                    </div>

                    <hr />
                    @endif

                    <div class="form-group">
                        <label for="company">Company</label>
                        {!! $formBuilder->text('company', null, ['class' => 'form-control'. ($errors->has('company') ? ' is-invalid' : '')]) !!}
                    </div>

                    <div class="form-group">
                        <label for="address_line_1">Address Line 1*</label>
                        {!! $formBuilder->text('address_line_1', null, ['class' => 'form-control'. ($errors->has('address_line_1') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('address_line_1') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="address_line_2">Address Line 2</label>
                        {!! $formBuilder->text('address_line_2', null, ['class' => 'form-control'. ($errors->has('address_line_2') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('address_line_2') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="town">City/Town*</label>
                        {!! $formBuilder->text('town', null, ['class' => 'form-control'. ($errors->has('town') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('town') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="county">County</label>
                        {!! $formBuilder->text('county', null, ['class' => 'form-control'. ($errors->has('county') ? ' is-invalid' : '')]) !!}
                    </div>

                    <div class="form-group">
                        <label for="postcode">Postcode*</label>
                        {!! $formBuilder->text('postcode', null, ['class' => 'form-control'. ($errors->has('postcode') ? ' is-invalid' : '')]) !!}
                        <small class="form-text text-danger">{{ $errors->first('postcode') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="country">Country*</label>
                        {!! $formBuilder->select('country_iso3', CoasterCommerce\Core\Model\Country::names(), \CoasterCommerce\Core\Model\Setting::getValue('country_default'), ['class' => 'form-control select2', 'id' => 'country_iso3']) !!}
                        <small class="form-text text-danger">{{ $errors->first('country_iso3') }}</small>
                    </div>

                </div>

            </div>

            <div class="row">
                <div class="col-sm-12 text-right">
                    {!! $formBuilder->submit('Register', ['class' => 'btn btn-default']) !!}
                </div>
            </div>

            {!! $formBuilder->close() !!}
        </div>
    </div>

</div>

@section('coastercommerce.scripts')
    @if ($craftClicksEnabled)
    <script src="https://cc-cdn.com/generic/scripts/v1/cc_c2a.min.js"></script>

    <script>
        cc = new clickToAddress({
            accessToken: '{{ CoasterCommerce\Core\Model\Setting::getValue("cc_key") }}',
            domMode: 'name'
        });

        cc.attach({
            search:     'cc_search', // 'search_field' is the name of the search box element
            line_1:     'address_line_1',
            line_2:     'address_line_2',
            company:    'company',
            town:       'town',
            postcode:   'postcode',
            county:     'county',
            country:    'country_iso3'
        });

    </script>
    @endif
    @if ($reCaptcha = \CoasterCommerce\Core\Model\Setting::getValue('recaptcha_public_key'))
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ $reCaptcha }}', {'action': 'register'}).then(function (token) {
                document.getElementById('recaptchaResponseRF').value = token;
            });
        });
    </script>
    @endif
@endsection

{!! $cart->view('sections.footer') !!}