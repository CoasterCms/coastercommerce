<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \Collective\Html\FormBuilder $formBuilder */

$customer = $cart->getCustomer();
$shippingAddress = $customer->defaultShippingAddress();
$billingAddress = $customer->defaultBillingAddress();

?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5 homeh2">Account Details</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">

            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}

        </div>
        <div class="col-sm-9">

            <div class="row">

                <div class="col-sm-12">
                    <h3 class="mb-5">Account Email: {{ $customer->email }}</h3>
                </div>

                <div class="col-sm-6">

                    <h3>
                        Default Billing Address &nbsp;
                        <a href="{{ $billingAddress->exists ? route('coaster-commerce.frontend.customer.account.address.edit', ['id' => $billingAddress->id]) : route('coaster-commerce.frontend.customer.account.address.new', ['billing' => 1]) }}" class="btn btn-default" style="padding: 6px 12px;">
                            <i class="fa fa-edit"></i>
                        </a>
                    </h3>
                    {!! $billingAddress->render() !!}

                </div>

                <div class="col-sm-6">

                    <h3>
                        Default Shipping Address &nbsp;
                        <a href="{{ $shippingAddress->exists ? route('coaster-commerce.frontend.customer.account.address.edit', ['id' => $shippingAddress->id]) : route('coaster-commerce.frontend.customer.account.address.new', ['shipping' => 1]) }}" class="btn btn-default" style="padding: 6px 12px;">
                            <i class="fa fa-edit"></i>
                        </a>
                    </h3>
                    {!! $shippingAddress->render() !!}

                </div>

                <div class="col-sm-12 mt-2">

                    @php $otherAddresses = $cart->getCustomer()->otherAddresses() @endphp
                    @if ($otherAddresses->count())
                    <h3>Other Addresses</h3>

                    <div class="input-group">
                        {!! $formBuilder->select('addresses', $otherAddresses->selectOptions(), null, ['class' => 'form-control', 'id' => 'otherAddressId']) !!}
                        <div class="input-group-append">
                            <button class="btn btn-default" id="otherAddress">Edit</button>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('coaster-commerce.frontend.customer.account.address.new') }}" class="btn btn-default mt-3">
                        <i class="fa fa fa-address-book"></i> &nbsp; Add New
                    </a>

                </div>

            </div>

        </div>
    </div>

</div>

@section('coastercommerce.scripts')
<script>
    $('#otherAddress').click(function (e) {
        window.location = '{{ route('coaster-commerce.frontend.customer.account.address.edit', ['id' => '--']) }}'
            .replace('--', $('#otherAddressId').val());
    });

</script>
@append

{!! $cart->view('sections.footer') !!}