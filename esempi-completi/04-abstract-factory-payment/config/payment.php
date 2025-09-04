<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used
    | by the application. You may change this value as needed.
    |
    */
    'default_provider' => env('PAYMENT_DEFAULT_PROVIDER', 'stripe'),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each payment provider.
    |
    */
    'stripe' => [
        'api_key' => env('STRIPE_API_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'enabled' => env('STRIPE_ENABLED', true),
    ],
    
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'enabled' => env('PAYPAL_ENABLED', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for payment logging.
    |
    */
    'logging' => [
        'enabled' => env('PAYMENT_LOGGING_ENABLED', true),
        'channel' => env('PAYMENT_LOG_CHANNEL', 'payment'),
    ],
];

