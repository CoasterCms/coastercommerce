@component('coaster-commerce::emails.layout')

<p>
Hello,<br />
You still have the items in your shopping cart on {{ \CoasterCommerce\Core\Model\Setting::getValue('store_name') }}.<br />
<br />
If you're having difficulty placing your order, please contact us at {{ \CoasterCommerce\Core\Model\Setting::getValue('store_email') }} or call us on {{ \CoasterCommerce\Core\Model\Setting::getValue('store_phone') }}.
</p>

<h1 style="margin-top: 20px;">Order Items</h1>

{!! view('coaster-commerce::admin.order.view.table', ['order' => $acart->order, 'email' => true]) !!}

@component('mail::button', ['url' => route('coaster-commerce.frontend.customer.abandoned-cart.checkout', ['id' => $acart->id, 'order_key' => $acart->order->order_key])])
    Check Out
@endcomponent

<div style="text-align: center; margin-top: 100px;">
    <a href="{{ route('coaster-commerce.frontend.customer.abandoned-cart.unsubscribe', ['id' => $acart->id, 'email' => $acart->email]) }}">unsubscribe</a>
</div>

@endcomponent