<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */
$craftClicksEnabled = !! \CoasterCommerce\Core\Model\Setting::getValue('cc_key');
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">{!! $address->exists ? 'Edit Address' : 'New Address' !!}</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">

            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}

        </div>
        <div class="col-sm-9">

            {!! $formBuilder->open(['url' => route('coaster-commerce.frontend.customer.account.address.save', ['id' => $address->id ?: 0])]) !!}
            <div class="form-group row">
                <label for="first_name" class="col-sm-2 col-form-label">First name*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('first_name', $address->first_name, ['class' => 'form-control', 'id' => 'first_name', 'required']) !!}
                    <small class="form-text text-danger">{{ $errors->first('first_name') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="last_name" class="col-sm-2 col-form-label">Last name*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('last_name', $address->last_name, ['class' => 'form-control', 'id' => 'last_name', 'required']) !!}
                    <small class="form-text text-danger">{{ $errors->first('last_name') }}</small>
                </div>
            </div>

            @if ($craftClicksEnabled)
            <hr />

            <div class="form-group row">
                <label for="cc_search" class="col-sm-2 col-form-label">Address Search</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text(null, null, ['class' => 'form-control', 'name' => 'cc_search']) !!}
                </div>
            </div>
            @endif

            <hr />

            <div class="form-group row">
                <label for="company" class="col-sm-2 col-form-label">Company</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('company', $address->company, ['class' => 'form-control', 'id' => 'company']) !!}
                    <small class="form-text text-danger">{{ $errors->first('company') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="address_line_1" class="col-sm-2 col-form-label">Address line 1*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('address_line_1', $address->address_line_1, ['class' => 'form-control', 'id' => 'address_line_1', 'required']) !!}
                    <small class="form-text text-danger">{{ $errors->first('address_line_1') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="address_line_2" class="col-sm-2 col-form-label">Address line 2</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('address_line_2', $address->address_line_2, ['class' => 'form-control', 'id' => 'address_line_2']) !!}
                    <small class="form-text text-danger">{{ $errors->first('address_line_2') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="town" class="col-sm-2 col-form-label">City/Town*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('town', $address->town, ['class' => 'form-control', 'id' => 'town', 'required']) !!}
                    <small class="form-text text-danger">{{ $errors->first('town') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="county" class="col-sm-2 col-form-label">County</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('county', $address->county, ['class' => 'form-control', 'id' => 'county']) !!}
                    <small class="form-text text-danger">{{ $errors->first('county') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="postcode" class="col-sm-2 col-form-label">Postcode*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('postcode', $address->postcode, ['class' => 'form-control', 'id' => 'postcode', 'required']) !!}
                    <small class="form-text text-danger">{{ $errors->first('postcode') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="country_iso3" class="col-sm-2 col-form-label">Country*</label>
                <div class="col-sm-10">
                    {!! $formBuilder->select('country_iso3', CoasterCommerce\Core\Model\Country::names(), $address->country_iso3 ?: \CoasterCommerce\Core\Model\Setting::getValue('country_default'), ['class' => 'form-control select2', 'id' => 'country_iso3']) !!}
                    <small class="form-text text-danger">{{ $errors->first('country_iso3') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="phone" class="col-sm-2 col-form-label">Phone number</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('phone', $address->phone, ['class' => 'form-control', 'id' => 'phone']) !!}
                    <small class="form-text text-danger">{{ $errors->first('phone') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                    {!! $formBuilder->text('email', $address->email, ['class' => 'form-control', 'id' => 'email']) !!}
                    <small class="form-text text-danger">{{ $errors->first('email') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="default_billing" class="col-sm-3 col-form-label">Default billing?</label>
                <div class="col-sm-9">
                    {!! $formBuilder->checkbox('default_billing', 1, $address->default_billing ?: request()->get('billing'), ['class' => 'form-control']) !!}
                    <small class="form-text text-danger">{{ $errors->first('default_billing') }}</small>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="default_shipping" class="col-sm-3 col-form-label">Default shipping?</label>
                <div class="col-sm-9">
                    {!! $formBuilder->checkbox('default_shipping', 1, $address->default_shipping ?: request()->get('shipping'), ['class' => 'form-control']) !!}
                    <small class="form-text text-danger">{{ $errors->first('default_shipping') }}</small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    {!! $formBuilder->submit($address->exists ? 'Update Address' : 'Add Address', ['class' => 'btn btn-default']) !!}
                    @if ($address->id)
                    <a href="{{ route('coaster-commerce.frontend.customer.account.address.delete', ['id' => $address->id]) }}" data-confirm="you wish to delete this address" class="d-block text-danger mt-4 confirm">Delete Address</a>
                    @endif
                </div>
            </div>

            {!! $formBuilder->close() !!}

        </div>
    </div>

</div>

@section('scripts')
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
@endsection

{!! $cart->view('sections.footer') !!}