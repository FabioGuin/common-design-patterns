<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Timeout Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for timeouts. You can override
    | these settings for specific services by adding them to the 'services'
    | array below.
    |
    */

    'timeout' => env('TIMEOUT_DEFAULT', 30000), // milliseconds
    'max_total_timeout' => env('TIMEOUT_MAX_TOTAL', 60000), // milliseconds
    'max_attempts' => env('TIMEOUT_MAX_ATTEMPTS', 3),
    'retry_delay' => env('TIMEOUT_RETRY_DELAY', 1000), // milliseconds
    'monitoring_enabled' => env('TIMEOUT_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure timeout settings for specific services.
    | If a service is not listed here, it will use the default settings.
    |
    */

    'services' => [
        'payment_service' => [
            'timeout' => env('TIMEOUT_PAYMENT', 15000), // 15 seconds
            'max_total_timeout' => 45000, // 45 seconds
            'max_attempts' => 3,
            'retry_delay' => 2000, // 2 seconds
            'priority' => 'high',
            'monitoring_enabled' => true,
        ],
        'inventory_service' => [
            'timeout' => env('TIMEOUT_INVENTORY', 10000), // 10 seconds
            'max_total_timeout' => 30000, // 30 seconds
            'max_attempts' => 2,
            'retry_delay' => 1500, // 1.5 seconds
            'priority' => 'medium',
            'monitoring_enabled' => true,
        ],
        'notification_service' => [
            'timeout' => env('TIMEOUT_NOTIFICATION', 5000), // 5 seconds
            'max_total_timeout' => 15000, // 15 seconds
            'max_attempts' => 2,
            'retry_delay' => 1000, // 1 second
            'priority' => 'low',
            'monitoring_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how timeout metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('TIMEOUT_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
