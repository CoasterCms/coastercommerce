@component('coaster-commerce::emails.layout')

<p>{{ $shareFormData['name'] }} has shared <a href="{{ route('coaster-commerce.frontend.customer.wishlist.view', ['id' => $wishList->id, 'share_key' => $wishList->share_key]) }}">their wish list</a> with you!</p>

{!! $shareFormData['message'] ? '<h2>Message</h2><p>' . nl2br($shareFormData['message']) . '</p>' : '' !!}

<h2>List Summary</h2>

@foreach($wishList->items as $item)
<h4 style="margin: 10px 0 0;"><a href="{{ url($item->product->getUrl()) }}">{{ $item->product->name }}</a> (ref: {{ $item->product->sku }})</h4>
@if ($item->variation)
@foreach($item->variation->variationArray() as $optionLabel => $optionValue)
<p style="margin: 0"><i>{{ $optionLabel . ': ' . $optionValue }}</i></p>
@endforeach
@endif
@endforeach

<h2 style="margin-top: 40px">
    <a href="{{ route('coaster-commerce.frontend.customer.wishlist.view', ['id' => $wishList->id, 'share_key' => $wishList->share_key]) }}">
        &raquo; View List Online &laquo;
    </a>
</h2>

@endcomponent