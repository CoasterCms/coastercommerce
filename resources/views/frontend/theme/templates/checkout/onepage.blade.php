<?php
/**
 * @var CoasterCommerce\Core\Session\Cart $cart
 * @var Collective\Html\FormBuilder $formBuilder
 */
$craftClicksEnabled = !! \CoasterCommerce\Core\Model\Setting::getValue('cc_key');
$hasShipping = !$cart->isVirtual();
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">

        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Checkout</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>

        <div class="col-sm-9">

            <div id="checkout-progress" class="row text-center border-bottom border-top mb-4">
                <div class="col-4 pb-2 pt-2 active" href="#details">
                    Details &nbsp;
                    <span class="fa fa-user"></span>
                </div>
                @if ($hasShipping)
                <div class="col-4 pb-2 pt-2" href="#shipping">
                    Shipping &nbsp;
                    <span class="fa fa-truck"></span>
                </div>
                @endif
                <div class="col-4 pb-2 pt-2" href="#payment">
                    Payment &nbsp;
                    <span class="fa fa-credit-card"></span>
                </div>
            </div>

            <div id="checkout-steps" class="mb-5">

                <div id="details" class="active">

                    @php $currentBilling = $cart->billingAddress() ?: new \CoasterCommerce\Core\Model\Order\Address() @endphp
                    @php $currentShipping = $cart->shippingAddress() ?: new \CoasterCommerce\Core\Model\Order\Address() @endphp

                    @if (!$cart->getCustomerId())
                    <div id="accountDetails">

                        <h2 class="mb-4" id="title">Customer Details</h2>

                        <div class="form-group row" id="emailFormGroup">
                            <label for="email" class="col-sm-2 col-form-label">Email*</label>
                            <div class="col-sm-10">
                                {!! $formBuilder->text('email', $cart->email, ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : ''), 'id' => 'email', 'required']) !!}
                                <small class="form-text text-danger">{{ $errors->first('email') }}</small>
                            </div>
                        </div>

                        <div id="customerActions" style="@if (request()->get('login'))display: none;@endif">
                            <button type="button" class="btn btn-info" id="guestButton">Continue as Guest</button>
                            <button type="button" class="btn btn-default" id="customerButton">Or login as customer</button>
                        </div>

                        <div id="customerLogin" style="display: none;">

                            {!! $formBuilder->open(['class' => 'form-horizontal', 'route' => 'coaster-commerce.frontend.customer.auth']) !!}

                            {!! $formBuilder->hidden('error_path', request()->getPathInfo() . '?login=1') !!}
                            {!! $formBuilder->hidden('login_path', request()->getPathInfo()) !!}

                            <div class="form-group row">
                                <label for="password" class="col-sm-2 col-form-label">Password</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->password('password', ['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : ''), 'id' => 'password', 'required']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-offset-2 col-sm-10">
                                    {!! $formBuilder->submit('Login', ['class' => 'btn btn-default']) !!}
                                </div>
                            </div>

                            {!! $formBuilder->close() !!}

                        </div>

                    </div>
                    @endif

                    {!! $formBuilder->open(['id' => 'addressForm', 'name' => 'addressForm']) !!}

                    <div id="shippingAddress" class="address-top" style="@if (!$cart->getCustomerId() || !$hasShipping)display: none;@endif">

                        <h2 class="mb-4" id="title">Shipping Address</h2>

                        @php
                            $addresses = collect();
                            $shippingId = null;
                            if ($cart->getCustomer()) {
                                /** @var \Illuminate\Support\Collection $addresses */
                                $addresses = $cart->getCustomer()->addresses->where('default_shipping', '!=', 1);
                                $shippingDefault = $cart->getCustomer()->defaultShippingAddress();
                                if ($shippingDefault->exists) {
                                    $addresses->prepend($shippingDefault);
                                    if (!$currentShipping->id) {
                                        $shippingId = $shippingDefault->id;
                                    }
                                }
                                foreach ($addresses as $address) {
                                    if ($address->stringValue() == $currentShipping->stringValue()) {
                                        $shippingId = $address->id;
                                        break;
                                    }
                                }
                                $addresses->push(new CoasterCommerce\Core\Model\Customer\Address());
                            }
                        @endphp

                        @if ($addresses->count() > 1)
                            <div class="row mb-4" class="saved-address">
                                @foreach($addresses as $address)
                                    <div class="col-sm-6">
                                        <div class="address-box {{ $address->id == $shippingId ? 'active' : '' }}" data-id="{{ $address->id }}">
                                            <div class="address-details">
                                                {!! $address->id ? $address->render() : '<p>Ship to a new address</p>' !!}
                                            </div>
                                            <div class="selected-address">
                                                Selected <i class="fa fa-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {!! $formBuilder->hidden('shipping[id]', $shippingId, ['class' => 'saved-address-input']) !!}

                        <div class="custom-address" style="@if ($shippingId)display: none;@endif">

                            <div id="passwordPopup" style="display: none">
                                <div class="form-group row">
                                    <label for="password2" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" id="password2" name="password" type="password" value="">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <input class="btn btn-default" type="button" value="Login">
                                    </div>
                                </div>
                                <hr />
                            </div>

                            <div class="form-group row">
                                <label for="shipping_first_name" class="col-sm-2 col-form-label">First name*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[first_name]', $currentShipping->first_name, ['class' => 'form-control', 'id' => 'shipping_first_name']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.first_name') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_last_name" class="col-sm-2 col-form-label">Last name*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[last_name]', $currentShipping->last_name, ['class' => 'form-control', 'id' => 'shipping_last_name']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.last_name') }}</small>
                                </div>
                            </div>

                            @if ($craftClicksEnabled)
                            <hr />

                            <div class="form-group row">
                                <label for="shipping_cc_search" class="col-sm-2 col-form-label">Address Search</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text(null, null, ['class' => 'form-control cc_search', 'name' => 'shipping_cc_search']) !!}
                                </div>
                            </div>
                            @endif

                            <hr />

                            <div class="form-group row">
                                <label for="shipping_company" class="col-sm-2 col-form-label">Company</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[company]', $currentShipping->company, ['class' => 'form-control', 'id' => 'shipping_company']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.company') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_address_line_1" class="col-sm-2 col-form-label">Address line 1*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[address_line_1]', $currentShipping->address_line_1, ['class' => 'form-control', 'id' => 'shipping_address_line_1']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.address_line_1') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_address_line_2" class="col-sm-2 col-form-label">Address line 2</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[address_line_2]', $currentShipping->address_line_2, ['class' => 'form-control', 'id' => 'shipping_address_line_2']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.address_line_2') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_town" class="col-sm-2 col-form-label">City/Town*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[town]', $currentShipping->town, ['class' => 'form-control', 'id' => 'shipping_town']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.town') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_county" class="col-sm-2 col-form-label">County</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[county]', $currentShipping->county, ['class' => 'form-control', 'id' => 'shipping_county']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.county') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_postcode" class="col-sm-2 col-form-label">Postcode*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[postcode]', $currentShipping->postcode, ['class' => 'form-control', 'id' => 'shipping_postcode']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.postcode') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_country_iso3" class="col-sm-2 col-form-label">Country*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->select('shipping[country_iso3]', CoasterCommerce\Core\Model\Country::names(), $currentShipping->country_iso3 ?: \CoasterCommerce\Core\Model\Setting::getValue('country_default'), ['class' => 'form-control select2']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.country_iso3') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shipping_phone" class="col-sm-2 col-form-label">Phone number</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('shipping[phone]', $currentShipping->phone, ['class' => 'form-control', 'id' => 'shipping_phone']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('shipping.phone') }}</small>
                                </div>
                            </div>

                        </div>

                        <div class="form-check text-right mb-4">
                            <input class="form-check-input" name="billing[same]" type="checkbox" value="1" id="billingSame" @if ($currentShipping->stringValue() == $currentBilling->stringValue())checked="checked"@endif>
                            <label class="form-check-label" for="billingSame">
                                My billing and shipping address are the same
                            </label>
                        </div>

                    </div>

                    <div id="billingAddress" class="address-top" style="@if (!$cart->getCustomerId() || $hasShipping)display: none;@endif">

                        <h2 class="mb-4" id="title">Billing Address</h2>

                        @php
                            $addresses = collect();
                            $billingId = null;
                            if ($cart->getCustomer()) {
                                /** @var \Illuminate\Support\Collection $addresses */
                                $addresses = $cart->getCustomer()->addresses->where('default_billing', '!=', 1);
                                $billingDefault = $cart->getCustomer()->defaultBillingAddress();
                                if ($billingDefault->exists) {
                                    $addresses->prepend($billingDefault);
                                    if (!$currentBilling->id) {
                                        $billingId = $billingDefault->id;
                                    }
                                }
                                foreach ($addresses as $address) {
                                    if ($address->stringValue() == $currentBilling->stringValue()) {
                                        $billingId = $address->id;
                                        break;
                                    }
                                }
                                $addresses->push(new CoasterCommerce\Core\Model\Customer\Address());
                            }
                        @endphp

                        @if ($addresses->count() > 1)
                            <div class="row mb-4" class="saved-address">
                                @foreach($addresses as $address)
                                    <div class="col-sm-6">
                                        <div class="address-box {{ $address->id == $billingId ? 'active' : '' }}" data-id="{{ $address->id }}">
                                            <div class="address-details">
                                                {!! $address->id ? $address->render() : '<p>New billing address</p>' !!}
                                            </div>
                                            <div class="selected-address">
                                                Selected <i class="fa fa-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {!! $formBuilder->hidden('billing[id]', $billingId, ['class' => 'saved-address-input']) !!}

                        <div class="custom-address" style="@if ($billingId)display: none;@endif">

                            <div class="form-group row">
                                <label for="billing_email" class="col-sm-2 col-form-label">Email</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[email]', $currentBilling->email, ['class' => 'form-control', 'id' => 'billing_email']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.email') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_first_name" class="col-sm-2 col-form-label">First name*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[first_name]', $currentBilling->first_name, ['class' => 'form-control', 'id' => 'billing_first_name']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.first_name') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_last_name" class="col-sm-2 col-form-label">Last name*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[last_name]', $currentBilling->last_name, ['class' => 'form-control', 'id' => 'billing_last_name']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.last_name') }}</small>
                                </div>
                            </div>

                            @if ($craftClicksEnabled)
                            <hr />

                            <div class="form-group row">
                                <label for="billing_cc_search" class="col-sm-2 col-form-label">Address Search</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text(null, null, ['class' => 'form-control cc_search', 'name' => 'billing_cc_search']) !!}
                                </div>
                            </div>
                            @endif

                            <hr />

                            <div class="form-group row">
                                <label for="billing_company" class="col-sm-2 col-form-label">Company</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[company]', $currentBilling->company, ['class' => 'form-control', 'id' => 'billing_company']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.company') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_address_line_1" class="col-sm-2 col-form-label">Address line 1*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[address_line_1]', $currentBilling->address_line_1, ['class' => 'form-control', 'id' => 'billing_address_line_1']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.address_line_1') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_address_line_2" class="col-sm-2 col-form-label">Address line 2</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[address_line_2]', $currentBilling->address_line_2, ['class' => 'form-control', 'id' => 'billing_address_line_2']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.address_line_2') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_town" class="col-sm-2 col-form-label">City/Town*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[town]', $currentBilling->town, ['class' => 'form-control', 'id' => 'billing_town']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.town') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_county" class="col-sm-2 col-form-label">County</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[county]', $currentBilling->county, ['class' => 'form-control', 'id' => 'billing_county']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.county') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_postcode" class="col-sm-2 col-form-label">Postcode*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[postcode]', $currentBilling->postcode, ['class' => 'form-control', 'id' => 'billing_postcode']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.postcode') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_country_iso3" class="col-sm-2 col-form-label">Country*</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->select('billing[country_iso3]', CoasterCommerce\Core\Model\Country::names(), $currentBilling->country_iso3 ?: \CoasterCommerce\Core\Model\Setting::getValue('country_default'), ['class' => 'form-control select2']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.country_iso3') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="billing_phone" class="col-sm-2 col-form-label">Phone number</label>
                                <div class="col-sm-10">
                                    {!! $formBuilder->text('billing[phone]', $currentBilling->phone, ['class' => 'form-control', 'id' => 'billing_phone']) !!}
                                    <small class="form-text text-danger">{{ $errors->first('billing.phone') }}</small>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="text-right" id="detailsSubmit" style="@if (!$cart->getCustomerId())display: none;@endif">
                        <button type="submit" class="btn btn-default">Next step</button>
                    </div>

                    {!! $formBuilder->close() !!}

                </div>

                <div id="shipping">

                    <h2 class="mb-4">Shipping Method</h2>

                    <div class="method-template" style="display:none">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping_method" id="{method}" value="{method}">
                            <label class="form-check-label" for="{method}">
                                {name} &nbsp; ({rate})
                            </label>
                            <div>
                                {desc}
                            </div>
                        </div>
                    </div>

                    <div class="methods"></div>

                    <div class="text-right mt-4" id="shippingSubmit">
                        <button type="button" class="btn btn-default">Next step</button>
                    </div>

                </div>

                <div id="payment">

                    <h2 class="mb-4">Payment Method</h2>

                    {!! $formBuilder->open(['id' => 'paymentForm', 'route' => 'coaster-commerce.frontend.checkout.onepage.pay']) !!}

                    <div class="method-template" style="display:none">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="{method}" value="{method}">
                            <label class="form-check-label" for="{method}">
                                {name}
                            </label>
                            <div>
                                {desc}
                            </div>
                        </div>
                    </div>

                    <div class="methods"></div>

                    <h2 class="mb-4 mt-4">Order Comments</h2>

                    @php $note = $cart->notes()->first() @endphp
                    {!! $formBuilder->textarea('comment', $note ? $note->note : '', ['class' => 'form-control', 'rows' => 2]) !!}

                    <div class="text-right mt-4" id="paymentSubmit">
                        <button type="submit" class="btn btn-default">Pay & Complete Order</button>
                    </div>

                    {!! $formBuilder->close() !!}

                </div>

            </div>

        </div>

        <div class="col-sm-3">

            <div class="ml-sm-3" id="cartSummary">

                {!! $cart->view('templates.checkout.summary') !!}

            </div>

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
            search:     'shipping_cc_search', // 'search_field' is the name of the search box element
            line_1:     'shipping[address_line_1]',
            line_2:     'shipping[address_line_2]',
            company:    'shipping[company]',
            town:       'shipping[town]',
            postcode:   'shipping[postcode]',
            county:     'shipping[county]',
            country:    'shipping[country_iso3]'
        });

        cc.attach({
            search:     'billing_cc_search', // 'search_field' is the name of the search box element
            line_1:     'billing[address_line_1]',
            line_2:     'billing[address_line_2]',
            company:    'billing[company]',
            town:       'billing[town]',
            postcode:   'billing[postcode]',
            county:     'billing[county]',
            country:    'billing[country_iso3]'
        });
    </script>
    @endif

    <script>
        jQuery(document).ready(function ($) {

            let xhr;
            $('#email').keyup(function () {
                notLoggedInClear();
                if ($('#customerLogin').css('display') != 'none') return;
                if ($('#shippingAddress').css('display') == 'none') return;
                if (xhr) xhr.abort();
                xhr = $.post(
                    '{{ route('coaster-commerce.frontend.checkout.onepage.check-email') }}',
                    {email: $(this).val()},
                    function (r) {
                        if (r.result == 1) notLoggedInAlert();
                    },
                    'json'
                );
            });

            function notLoggedInClear() {
                $('#email').parent().find('.form-text').html('');
                $('#passwordPopup').hide();
            }

            function notLoggedInAlert() {
                $('#email').parent().find('.form-text').html('A customer account exists for this email, you can quickly login below.');
                $('#passwordPopup').show();
                $('#passwordPopup input').keypress(function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        popupLogin();
                    }
                });
                $('#passwordPopup input[type=button]').click(popupLogin);
            }

            function popupLogin() {
                $('#password').val($('#password2').val());
                $('#passwordPopup').hide();
                $('<p class="mb-5">Logging in ...</p>').insertBefore('#passwordPopup');
                $('#emailFormGroup').prependTo('#customerLogin > form');
                $('#customerLogin > form').submit();
            }

            $('#guestButton').click(function () {
                $.post(
                    '{{ route('coaster-commerce.frontend.checkout.onepage.save.email') }}',
                    {email: $('#email').val()}
                );
                $('#accountDetails').hide();
                @if ($hasShipping)
                $('#shippingAddress').show();
                $('#billingSame').trigger('change');
                $('#title').html('Shipping Address');
                $('#emailFormGroup').prependTo('#shippingAddress .custom-address');
                @else
                $('#billingAddress').show();
                $('#billing_email').closest('.form-group').hide();
                $('#title').html('Billing Address');
                $('#emailFormGroup').prependTo('#billingAddress .custom-address');
                @endif
                $('#detailsSubmit').show();
                $('#email').trigger('keyup');
            });

            $('#customerButton').click(function () {
                $('#customerActions').hide();
                $('#customerLogin').show();
                $('#emailFormGroup').prependTo('#customerLogin > form');
            });

            @if (request()->get('login'))
            $('#customerButton').trigger('click');
            @endif

            function setRequiredAddressFields(addressTopEl, enabled) {
                enabled = (enabled !== undefined) ? enabled : !addressTopEl.find('.saved-address-input').val();
                let requiredFields = ['first_name', 'last_name', 'address_line_1', 'town', 'postcode', 'country_iso3'];
                let requiredFieldsLen = requiredFields.length;
                addressTopEl.find('.form-control').each(function () {
                    for (let i = 0; i < requiredFieldsLen; i++) {
                        if ($(this).attr('id') && $(this).attr('id').indexOf(requiredFields[i]) > 0) {
                            $(this).attr('required', enabled);
                            break;
                        }
                    }
                });
            }

            let shippingAddressEl = $('#shippingAddress'), billingAddressEl = $('#billingAddress'), accountEl = $('#accountDetails');
            @if ($hasShipping)
            // billing same as shipping checkbox changes
            $('#billingSame').change(function () {
                setRequiredAddressFields(shippingAddressEl);
                if (!$(this).prop('checked') && (accountEl.length === 0 || accountEl.css('display') === 'none')) {
                    billingAddressEl.show();
                    setRequiredAddressFields(billingAddressEl);
                } else {
                    billingAddressEl.hide();
                    setRequiredAddressFields(billingAddressEl, false);
                }
            }).trigger('change');
            @else
            setRequiredAddressFields(billingAddressEl);
            @endif

            // for selecting saved customer account address
            $('#details .address-box').click(function () {
                let addressTopEl = $(this).closest('.address-top');
                addressTopEl.find('.address-box').removeClass('active');
                $(this).addClass('active');
                if (!$(this).data('id')) {
                    addressTopEl.find('.saved-address-input').val(null);
                    addressTopEl.find('.custom-address').show();
                    setRequiredAddressFields(addressTopEl);
                } else {
                    addressTopEl.find('.saved-address-input').val($(this).data('id'));
                    addressTopEl.find('.custom-address').hide();
                    setRequiredAddressFields(addressTopEl, false);
                }
            });

            $('#addressForm').submit(function (e) {
                e.preventDefault();
                $.post(
                    '{{ route('coaster-commerce.frontend.checkout.onepage.save.address') }}',
                    $(this).serializeArray(),
                    function (r) {
                        reloadSummary(); // shipping may be unset
                        @if ($hasShipping)
                        updateShippingMethods(r.shipping_methods);
                        gotoStep('#shipping', true);
                        @else
                        updatePaymentMethods(r.payment_methods);
                        gotoStep('#payment', true);
                        @endif
                    },
                    'json'
                ).fail(function(r) {
                    alert(r.responseJSON.message);
                });
            });

            let activeShippingMethod = '{{ $cart->shipping_method }}';
            let availableShippingMethodsEl = $('#shipping .methods'), optionShippingTemplate = $('#shipping .method-template').html();
            function updateShippingMethods(shippingMethods) {
                availableShippingMethodsEl.html('');
                if (shippingMethods.length) {
                    for (let i = 0; i < shippingMethods.length; i++) {
                        availableShippingMethodsEl.append('<div class="method" data-id="' + shippingMethods[i].id + '">' +
                            optionShippingTemplate
                                .replace(/{method}/g, shippingMethods[i].id)
                                .replace(/{name}/g, shippingMethods[i].name)
                                .replace(/{desc}/g, shippingMethods[i].desc)
                                .replace(/{rate}/g, shippingMethods[i].rate_formatted) +
                            '</div>');
                    }
                    let activeShippingMethodSet = false;
                    availableShippingMethodsEl.find('.method').each(function () {
                        if ($(this).data('id') === activeShippingMethod) {
                            selectShippingMethod($(this));
                            activeShippingMethodSet = true;
                        }
                    });
                    if (!activeShippingMethodSet) {
                        selectShippingMethod(availableShippingMethodsEl.find('.method').first());
                    }
                    $('#shippingSubmit').show();
                } else {
                    availableShippingMethodsEl.html('<p>No shipping methods available.</p>');
                    $('#shippingSubmit').hide();
                }
            }

            function selectShippingMethod(methodEl, clickRadio = true) {
                availableShippingMethodsEl.find('.method').removeClass('active');
                methodEl.addClass('active');
                activeShippingMethod = methodEl.data('id');
                if (clickRadio) {
                    methodEl.find('input[name=shipping_method]').click();
                }
            }

            availableShippingMethodsEl.on('click', '.method', function (e) {
                selectShippingMethod($(this), e.target.nodeName !== 'INPUT' && e.target.nodeName !== 'LABEL');
            });

            $('#shippingSubmit').click(function() {
                let shippingMethod = availableShippingMethodsEl.find('input[name=shipping_method]:checked').val();
                let shippingDetailsKey = 'shipping_details_' + shippingMethod;
                let shippingDetails = {};
                $('[name^="'+shippingDetailsKey+'"]').each(function () {
                    shippingDetails[$(this).attr('name').replace(shippingDetailsKey + '_', '')] = $(this).val();
                });
                $.post(
                    '{{ route('coaster-commerce.frontend.checkout.onepage.save.shipping') }}',
                    {shipping_method: shippingMethod, shipping_details: shippingDetails},
                    function (r) {
                        reloadSummary();
                        updatePaymentMethods(r.payment_methods);
                        gotoStep('#payment', true);
                    },
                    'json'
                ).fail(function(r) {
                    alert(r.responseJSON.message);
                });
            });

            let activePaymentMethod = '{{ $cart->payment_method }}';
            let availablePaymentMethodsEl = $('#payment .methods'), optionPaymentTemplate = $('#payment .method-template').html();
            function updatePaymentMethods(paymentMethods) {
                availablePaymentMethodsEl.html('');
                if (paymentMethods.length) {
                    for (let i = 0; i < paymentMethods.length; i++) {
                        availablePaymentMethodsEl.append('<div class="method" data-id="' + paymentMethods[i].id + '">' +
                            optionPaymentTemplate
                                .replace(/{method}/g, paymentMethods[i].id)
                                .replace(/{name}/g, paymentMethods[i].name)
                                .replace(/{desc}/g, paymentMethods[i].desc) +
                            '</div>');
                    }
                    let activePaymentMethodSet = false;
                    availablePaymentMethodsEl.find('.method').each(function () {
                        if ($(this).data('id') === activePaymentMethod) {
                            selectPaymentMethod($(this));
                            activePaymentMethodSet = true;
                        }
                    });
                    if (!activePaymentMethodSet) {
                        selectPaymentMethod(availablePaymentMethodsEl.find('.method').first());
                    }
                    $('#paymentSubmit').show();
                } else {
                    availablePaymentMethodsEl.html('<p>No payment methods available.</p>');
                    $('#paymentSubmit').hide();
                }
            }

            function selectPaymentMethod(methodEl, clickRadio = true) {
                availablePaymentMethodsEl.find('.method').removeClass('active');
                methodEl.addClass('active');
                activePaymentMethod = methodEl.data('id');
                if (clickRadio) {
                    methodEl.find('input[name=payment_method]').click();
                }
            }

            availablePaymentMethodsEl.on('click', '.method', function (e) {
                selectPaymentMethod($(this), e.target.nodeName !== 'INPUT' && e.target.nodeName !== 'LABEL');
            });

            function reloadSummary() {
                $.post(
                    '{{ route('coaster-commerce.frontend.checkout.onepage.summary') }}',
                    null,
                    function (r) {
                        $('#cartSummary').html(r);
                    }
                );
            }

            function gotoStep(stepId, scroll = false) {
                let stepComplete = true;
                $('#checkout-progress > div').each(function () {
                    if ($(this).attr('href') === stepId) {
                        $(this).addClass('active');
                        let stepContentEl = $($(this).attr("href"));
                        stepContentEl.parent().find('> div').removeClass('active');
                        stepContentEl.addClass('active');
                        stepComplete = false;
                    } else {
                        $(this).removeClass('active');
                        stepComplete ? $(this).addClass('complete') : $(this).removeClass('complete');
                    }
                });
                if (scroll && $(window).scrollTop() > $('#checkout-progress').offset().top) {
                    $.scrollTo('#checkout-progress', 200);
                }
            }

            $('#checkout-progress > div').click(function() {
                if ($(this).hasClass('complete')) {
                    gotoStep($(this).attr('href'));
                }
            });

        });

    </script>
@endsection

{!! $cart->view('sections.footer') !!}