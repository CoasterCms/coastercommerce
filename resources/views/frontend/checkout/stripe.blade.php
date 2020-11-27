<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Redirecting to stripe</title>
</head>
<body>
    <p>Redirecting to stripe ...</p>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var stripe = Stripe('{{ $publishableKey }}');
        stripe.redirectToCheckout({
            sessionId: '{{ $session->id }}'
        }).then(function (result) {

        });
    </script>
</body>
</html>