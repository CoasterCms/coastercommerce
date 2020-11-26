@component('coaster-commerce::emails.layout')

Some of your watched items are now in stock:

@foreach($products as $product)
[{{ $product->name }}]({{ url($product->getUrl()) }})<br>
@endforeach

@endcomponent