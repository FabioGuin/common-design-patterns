<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bulkhead Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for bulkheads. You can override
    | these settings for specific services by adding them to the 'services'
    | array below.
    |
    */

    'max_threads' => env('BULKHEAD_MAX_THREADS', 5),
    'max_connections' => env('BULKHEAD_MAX_CONNECTIONS', 10),
    'max_queue_length' => env('BULKHEAD_MAX_QUEUE_LENGTH', 100),
    'memory_limit' => env('BULKHEAD_MEMORY_LIMIT', 256), // MB
    'priority' => env('BULKHEAD_PRIORITY', 'medium'),
    'timeout' => env('BULKHEAD_TIMEOUT', 30), // seconds
    'monitoring_enabled' => env('BULKHEAD_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure bulkhead settings for specific services.
    | If a service is not listed here, it will use the default settings.
    |
    */

    'services' => [
        'payment_service' => [
            'max_threads' => env('BULKHEAD_PAYMENT_THREADS', 10),
            'max_connections' => 20,
            'max_queue_length' => 50,
            'memory_limit' => 512, // MB
            'priority' => 'high',
            'timeout' => 15, // seconds
            'monitoring_enabled' => true,
        ],
        'inventory_service' => [
            'max_threads' => env('BULKHEAD_INVENTORY_THREADS', 5),
            'max_connections' => 10,
            'max_queue_length' => 100,
            'memory_limit' => 256, // MB
            'priority' => 'medium',
            'timeout' => 30, // seconds
            'monitoring_enabled' => true,
        ],
        'notification_service' => [
            'max_threads' => env('BULKHEAD_NOTIFICATION_THREADS', 2),
            'max_connections' => 5,
            'max_queue_length' => 200,
            'memory_limit' => 128, // MB
            'priority' => 'low',
            'timeout' => 60, // seconds
            'monitoring_enabled' => true,
        ],
        'report_service' => [
            'max_threads' => env('BULKHEAD_REPORT_THREADS', 1),
            'max_connections' => 3,
            'max_queue_length' => 500,
            'memory_limit' => 64, // MB
            'priority' => 'low',
            'timeout' => 120, // seconds
            'monitoring_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how bulkhead metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('BULKHEAD_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
