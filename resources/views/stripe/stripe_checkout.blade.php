<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
</head>
<body>

    <script>
        var checkout                   = {!! json_encode($checkout, JSON_HEX_TAG) !!};
        var stripe_account_id          = {!! json_encode($stripe_account_id, JSON_HEX_TAG) !!};
        var stripe_pulick_key          = {!! json_encode(setting('apps.stripe_public_key'), JSON_HEX_TAG) !!};

        var stripe = Stripe(stripe_pulick_key, {
            stripeAccount: stripe_account_id
        });
        
        stripe.redirectToCheckout({
           sessionId: checkout.id
        }).then(function (result) {
                // error section
        });

    </script>
</body>
</html>



