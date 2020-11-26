@component('coaster-commerce::emails.layout')

<p>
    Hello {{ $note->order->billingAddress()->first_name . ' ' . $note->order->billingAddress()->last_name }},<br />
    <br />
    You order has been updated:
</p>

<h1 style="margin-top: 20px;">Order {{ $note->order->order_number }}</h1>

<p>{!!  nl2br($note->note) !!}</p>

@endcomponent