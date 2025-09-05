<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
];

// Configurazione aggiuntiva per i pagamenti
config(['payments.default_provider' => env('DEFAULT_PAYMENT_PROVIDER', 'stripe')]);
