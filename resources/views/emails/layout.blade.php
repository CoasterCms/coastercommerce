
@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {!! $slot !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <?php $name = \CoasterCommerce\Core\Model\Setting::getValue('store_name') ?>
            If you did not expect to receive this email from {{ $name }} ([{{ trim(preg_replace('#^http[s]*://#', '', config('app.url')), '/') }}]({{ config('app.url') }})), then please ignore this email.<br />
            &copy; {{ date('Y') }} {{ $name }}. All Rights Reserved.
        @endcomponent
    @endslot
@endcomponent
