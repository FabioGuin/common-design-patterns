<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for cache. You can override
    | these settings for specific entities by adding them to the 'entities'
    | array below.
    |
    */

    'default' => [
        'ttl' => env('CACHE_DEFAULT_TTL', 3600), // 1 hour
        'strategy' => 'read_through',
        'monitoring_enabled' => env('CACHE_MONITORING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure cache settings for specific entities.
    | Each entity can have its own TTL and strategy.
    |
    */

    'entities' => [
        'products' => [
            'ttl' => env('CACHE_PRODUCTS_TTL', 1800), // 30 minutes
            'strategy' => 'read_through',
            'tags' => ['products', 'catalog'],
            'monitoring_enabled' => true,
        ],
        'users' => [
            'ttl' => env('CACHE_USERS_TTL', 7200), // 2 hours
            'strategy' => 'write_through',
            'tags' => ['users', 'profiles'],
            'monitoring_enabled' => true,
        ],
        'orders' => [
            'ttl' => env('CACHE_ORDERS_TTL', 900), // 15 minutes
            'strategy' => 'write_behind',
            'tags' => ['orders', 'transactions'],
            'monitoring_enabled' => true,
        ],
        'categories' => [
            'ttl' => env('CACHE_CATEGORIES_TTL', 3600), // 1 hour
            'strategy' => 'refresh_ahead',
            'tags' => ['categories', 'navigation'],
            'monitoring_enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Strategies
    |--------------------------------------------------------------------------
    |
    | Define the available cache strategies and their configurations.
    |
    */

    'strategies' => [
        'read_through' => [
            'description' => 'Loads data from database if not in cache',
            'use_case' => 'Frequently read data that changes rarely',
            'performance' => 'High read performance, medium write performance',
        ],
        'write_through' => [
            'description' => 'Updates both cache and database simultaneously',
            'use_case' => 'Critical data that must be consistent',
            'performance' => 'Medium read performance, low write performance',
        ],
        'write_behind' => [
            'description' => 'Updates cache immediately, database in background',
            'use_case' => 'High write volume data',
            'performance' => 'High read performance, high write performance',
        ],
        'write_around' => [
            'description' => 'Updates only database, invalidates cache',
            'use_case' => 'Data that is written once and read many times',
            'performance' => 'High read performance, medium write performance',
        ],
        'refresh_ahead' => [
            'description' => 'Pre-loads related data based on access patterns',
            'use_case' => 'Data with predictable access patterns',
            'performance' => 'Very high read performance, medium write performance',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how cache metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
        'alert_thresholds' => [
            'hit_ratio_min' => 80, // Minimum hit ratio percentage
            'miss_ratio_max' => 20, // Maximum miss ratio percentage
            'error_ratio_max' => 5, // Maximum error ratio percentage
        ],
    ],
];
