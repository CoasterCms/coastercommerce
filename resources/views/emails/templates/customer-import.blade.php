@component('coaster-commerce::emails.layout')

We have a new website {{ config('app.url') }} where you can order online.

Here are the details for your new Undersea account:

Username: {{ $email }}<br>
Password: {{ $password }}

Thank you,<br>
The Undersea Team

@endcomponent