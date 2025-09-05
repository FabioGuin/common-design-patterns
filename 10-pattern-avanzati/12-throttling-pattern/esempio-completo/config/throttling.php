<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Throttling Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for throttling. You can override
    | these settings for specific services by adding them to the 'services'
    | array below.
    |
    */

    'default' => [
        'rate' => env('THROTTLING_DEFAULT_RATE', 100),
        'window' => env('THROTTLING_DEFAULT_WINDOW', 3600), // seconds
        'strategy' => 'fixed_window',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure throttling settings for specific services.
    | If a service is not listed here, it will use the default settings.
    |
    */

    'services' => [
        'payment_service' => [
            'rate' => env('THROTTLING_PAYMENT_RATE', 5),
            'window' => 60, // 1 minute
            'strategy' => 'fixed_window',
            'priority' => 'high',
        ],
        'inventory_service' => [
            'rate' => env('THROTTLING_INVENTORY_RATE', 20),
            'window' => 60, // 1 minute
            'strategy' => 'sliding_window',
            'priority' => 'medium',
        ],
        'notification_service' => [
            'rate' => env('THROTTLING_NOTIFICATION_RATE', 100),
            'window' => 60, // 1 minute
            'strategy' => 'token_bucket',
            'priority' => 'low',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoint-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure throttling settings for specific endpoints.
    | These settings will override service settings for specific endpoints.
    |
    */

    'endpoints' => [
        'api/payment' => [
            'rate' => 5,
            'window' => 60,
            'strategy' => 'fixed_window',
            'priority' => 'high',
        ],
        'api/refund' => [
            'rate' => 3,
            'window' => 60,
            'strategy' => 'fixed_window',
            'priority' => 'high',
        ],
        'api/validation' => [
            'rate' => 10,
            'window' => 60,
            'strategy' => 'fixed_window',
            'priority' => 'high',
        ],
        'api/inventory' => [
            'rate' => 20,
            'window' => 60,
            'strategy' => 'sliding_window',
            'priority' => 'medium',
        ],
        'api/reserve' => [
            'rate' => 10,
            'window' => 60,
            'strategy' => 'sliding_window',
            'priority' => 'medium',
        ],
        'api/update' => [
            'rate' => 15,
            'window' => 60,
            'strategy' => 'sliding_window',
            'priority' => 'medium',
        ],
        'api/email' => [
            'rate' => 100,
            'window' => 60,
            'strategy' => 'token_bucket',
            'priority' => 'low',
        ],
        'api/sms' => [
            'rate' => 50,
            'window' => 60,
            'strategy' => 'token_bucket',
            'priority' => 'low',
        ],
        'api/push' => [
            'rate' => 200,
            'window' => 60,
            'strategy' => 'token_bucket',
            'priority' => 'low',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Type Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure different throttling limits for different user types.
    | These settings will override service and endpoint settings.
    |
    */

    'user_types' => [
        'free' => [
            'rate' => 10,
            'window' => 60,
            'strategy' => 'fixed_window',
        ],
        'premium' => [
            'rate' => 100,
            'window' => 60,
            'strategy' => 'sliding_window',
        ],
        'enterprise' => [
            'rate' => 1000,
            'window' => 60,
            'strategy' => 'token_bucket',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how throttling metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('THROTTLING_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
