<?php
/**
 * @var CoasterCms\Contracts\PageBuilder $pb
 */
?>

{!! $pb->section('head') !!}

<!-- ecomm meta/canonical
<meta name="description" content="{{ $coasterCommerceMetas->description ?: $pb->block('meta_description', ['meta' => true]) }}">
<meta name="keywords" content="{{ $coasterCommerceMetas->keywords ?: $pb->block('meta_keywords', ['meta' => true]) }}">
<title>{{ $coasterCommerceMetas->title ?: $pb->block('meta_title', ['meta' => true]) }}</title>
@if ($coasterCommerceCanonical)<link rel="canonical" href="{{ $coasterCommerceCanonical }}" />@endif
-->

<!-- ecomm js + css -->
<link href="{{ config('coaster-commerce.url.assets') }}/_/css/bootstrap.min.css" rel="stylesheet">
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/fa-all.min.js"></script>
@if (($reCaptcha = \CoasterCommerce\Core\Model\Setting::getValue('recaptcha_public_key')) &&
    (in_array($pb->template, ['contact']) || request()->routeIs(['coaster-commerce.frontend.customer.register'])))
<script src="https://www.google.com/recaptcha/api.js?render={{ $reCaptcha }}"></script>
@endif
<link href="{{ config('coaster-commerce.url.assets') }}/frontend/shop.css" rel="stylesheet">
<!-- /ecomm js + css -->

<!-- ecomm menu links -->
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-12 text-right">
            @if ($customer = $cart->getCustomer())
                <a href="{{ route('coaster-commerce.frontend.customer.account') }}" ><i class="fas fa-user"></i> My Account</a> &nbsp;
                <a href="{{ route('coaster-commerce.frontend.customer.logout') }}" ><i class="fas fa-lock"></i> Logout</a> &nbsp;
            @else
                <a href="{{ route('coaster-commerce.frontend.customer.login') }}" ><i class="fas fa-lock"></i> Login / register</a> &nbsp;
            @endif
            <a href="{{ route('coaster-commerce.frontend.checkout.cart') }}" >
                <i class="fas fa-shopping-cart"></i> Basket {!! $cart->getItemCount() ? '(' . new \CoasterCommerce\Core\Currency\Format($cart->order_total_inc_vat) . ')' : null !!}
            </a>
        </div>
    </div>
</div>
<!-- /ecomm menu links -->

<!-- ecomm alerts -->
<div class="container">
<div class="row">
<div id="commerceAlerts" class="col-12">
    <div class="alert mt-4 mb-0" id="commerceAlert" style="display: none;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
</div>
</div>
</div>
<!-- /ecomm alerts -->
