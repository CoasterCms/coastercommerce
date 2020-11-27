
<!-- ecomm scripts -->
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/jquery-3.4.1.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/bootstrap.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/frontend/shop.js"></script>

<script>
    @foreach($ccMessageAlerts as $alertClass => $alertArray)
    @foreach($alertArray as $alert)
    commerceAlert('{{ $alertClass }}', '{{ $alert }}');
    @endforeach
    @endforeach
</script>
@yield('coastercommerce.scripts')
<!-- /ecomm scripts -->

{!! $pb->section('footer') !!}
