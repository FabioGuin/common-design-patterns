<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for circuit breakers. You can override
    | these settings for specific services by adding them to the 'services'
    | array below.
    |
    */

    'failure_threshold' => env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 5),
    'timeout' => env('CIRCUIT_BREAKER_TIMEOUT', 60000), // milliseconds
    'success_threshold' => env('CIRCUIT_BREAKER_SUCCESS_THRESHOLD', 3),
    'monitoring_enabled' => env('CIRCUIT_BREAKER_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure circuit breaker settings for specific services.
    | If a service is not listed here, it will use the default settings.
    |
    */

    'services' => [
        'payment_service' => [
            'failure_threshold' => 3,
            'timeout' => 30000, // 30 seconds
            'success_threshold' => 2,
            'monitoring_enabled' => true,
        ],
        'inventory_service' => [
            'failure_threshold' => 5,
            'timeout' => 45000, // 45 seconds
            'success_threshold' => 3,
            'monitoring_enabled' => true,
        ],
        'notification_service' => [
            'failure_threshold' => 8,
            'timeout' => 90000, // 90 seconds
            'success_threshold' => 5,
            'monitoring_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how circuit breaker metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('CIRCUIT_BREAKER_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
