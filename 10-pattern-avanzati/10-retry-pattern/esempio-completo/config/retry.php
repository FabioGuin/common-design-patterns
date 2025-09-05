<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Retry Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for retry. You can override
    | these settings for specific services by adding them to the 'services'
    | array below.
    |
    */

    'max_attempts' => env('RETRY_MAX_ATTEMPTS', 3),
    'base_delay' => env('RETRY_BASE_DELAY', 1000), // milliseconds
    'max_delay' => env('RETRY_MAX_DELAY', 10000), // milliseconds
    'multiplier' => env('RETRY_MULTIPLIER', 2.0),
    'backoff_strategy' => env('RETRY_BACKOFF_STRATEGY', 'exponential'),
    'monitoring_enabled' => env('RETRY_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure retry settings for specific services.
    | If a service is not listed here, it will use the default settings.
    |
    */

    'services' => [
        'payment_service' => [
            'max_attempts' => 5,
            'base_delay' => 1000,
            'max_delay' => 5000,
            'multiplier' => 2.0,
            'backoff_strategy' => 'exponential',
            'retryable_errors' => [500, 502, 503, 504],
            'non_retryable_errors' => [400, 401, 403, 404],
            'monitoring_enabled' => true,
        ],
        'inventory_service' => [
            'max_attempts' => 3,
            'base_delay' => 2000,
            'max_delay' => 8000,
            'multiplier' => 1.5,
            'backoff_strategy' => 'linear',
            'retryable_errors' => [500, 502, 503, 504],
            'non_retryable_errors' => [400, 401, 403, 404],
            'monitoring_enabled' => true,
        ],
        'notification_service' => [
            'max_attempts' => 2,
            'base_delay' => 5000,
            'max_delay' => 15000,
            'multiplier' => 2.0,
            'backoff_strategy' => 'jitter',
            'retryable_errors' => [500, 502, 503, 504],
            'non_retryable_errors' => [400, 401, 403, 404],
            'monitoring_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how retry metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('RETRY_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
