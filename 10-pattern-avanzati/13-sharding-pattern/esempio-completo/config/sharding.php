<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharding Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for sharding. You can override
    | these settings for specific entities by adding them to the 'entities'
    | array below.
    |
    */

    'default' => [
        'strategy' => env('SHARDING_STRATEGY', 'key_based'),
        'shard_count' => env('SHARDING_SHARD_COUNT', 3),
        'monitoring_enabled' => env('SHARDING_MONITORING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure sharding settings for specific entities.
    | Each entity can have its own sharding strategy and configuration.
    |
    */

    'entities' => [
        'users' => [
            'strategy' => 'key_based',
            'shard_count' => 3,
            'shard_key' => 'id',
            'shards' => [
                'shard_1' => [
                    'connection' => 'shard_1',
                    'host' => env('DB_SHARD_1_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_1_DATABASE', 'shard_1'),
                ],
                'shard_2' => [
                    'connection' => 'shard_2',
                    'host' => env('DB_SHARD_2_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_2_DATABASE', 'shard_2'),
                ],
                'shard_3' => [
                    'connection' => 'shard_3',
                    'host' => env('DB_SHARD_3_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_3_DATABASE', 'shard_3'),
                ],
            ],
        ],
        'products' => [
            'strategy' => 'range_based',
            'shard_key' => 'category',
            'ranges' => [
                'shard_1' => ['min' => 'A', 'max' => 'C'],
                'shard_2' => ['min' => 'D', 'max' => 'F'],
                'shard_3' => ['min' => 'G', 'max' => 'Z'],
            ],
            'shards' => [
                'shard_1' => [
                    'connection' => 'shard_1',
                    'host' => env('DB_SHARD_1_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_1_DATABASE', 'shard_1'),
                ],
                'shard_2' => [
                    'connection' => 'shard_2',
                    'host' => env('DB_SHARD_2_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_2_DATABASE', 'shard_2'),
                ],
                'shard_3' => [
                    'connection' => 'shard_3',
                    'host' => env('DB_SHARD_3_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_3_DATABASE', 'shard_3'),
                ],
            ],
        ],
        'orders' => [
            'strategy' => 'hash_based',
            'shard_key' => 'order_date',
            'hash_function' => 'md5',
            'shard_count' => 3,
            'shards' => [
                'shard_1' => [
                    'connection' => 'shard_1',
                    'host' => env('DB_SHARD_1_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_1_DATABASE', 'shard_1'),
                ],
                'shard_2' => [
                    'connection' => 'shard_2',
                    'host' => env('DB_SHARD_2_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_2_DATABASE', 'shard_2'),
                ],
                'shard_3' => [
                    'connection' => 'shard_3',
                    'host' => env('DB_SHARD_3_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_3_DATABASE', 'shard_3'),
                ],
            ],
        ],
        'categories' => [
            'strategy' => 'directory_based',
            'shard_key' => 'id',
            'default_shard' => 'shard_1',
            'directory' => [
                '1' => 'shard_1',
                '2' => 'shard_1',
                '3' => 'shard_2',
                '4' => 'shard_2',
                '5' => 'shard_3',
                '6' => 'shard_3',
            ],
            'shards' => [
                'shard_1' => [
                    'connection' => 'shard_1',
                    'host' => env('DB_SHARD_1_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_1_DATABASE', 'shard_1'),
                ],
                'shard_2' => [
                    'connection' => 'shard_2',
                    'host' => env('DB_SHARD_2_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_2_DATABASE', 'shard_2'),
                ],
                'shard_3' => [
                    'connection' => 'shard_3',
                    'host' => env('DB_SHARD_3_HOST', '127.0.0.1'),
                    'database' => env('DB_SHARD_3_DATABASE', 'shard_3'),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how sharding metrics are stored and monitored.
    |
    */

    'monitoring' => [
        'enabled' => env('SHARDING_MONITORING_ENABLED', true),
        'retention_days' => 30,
        'cleanup_interval' => 24, // hours
    ],
];
