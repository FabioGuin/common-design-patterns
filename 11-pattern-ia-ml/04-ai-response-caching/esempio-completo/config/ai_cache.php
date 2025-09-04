<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Response Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di caching delle risposte AI che ottimizza
    | le performance e riduce i costi delle chiamate AI.
    |
    */

    'enabled' => env('AI_CACHE_ENABLED', true),

    'default_strategy' => env('AI_CACHE_DEFAULT_STRATEGY', 'lru'),

    'default_ttl' => env('AI_CACHE_DEFAULT_TTL', 3600),

    'max_cache_size' => env('AI_CACHE_MAX_SIZE', 10000),

    'compression_enabled' => env('AI_CACHE_COMPRESSION_ENABLED', true),

    'compression_threshold' => env('AI_CACHE_COMPRESSION_THRESHOLD', 1024),

    /*
    |--------------------------------------------------------------------------
    | Cache Strategies
    |--------------------------------------------------------------------------
    |
    | Strategie di caching disponibili per ottimizzare le performance.
    |
    */

    'strategies' => [
        'lru' => [
            'class' => \App\Services\AI\Strategies\LRUCacheStrategy::class,
            'max_size' => 1000,
            'ttl' => 3600,
            'description' => 'Least Recently Used - Rimuove gli elementi meno recentemente utilizzati'
        ],
        'lfu' => [
            'class' => \App\Services\AI\Strategies\LFUCacheStrategy::class,
            'max_size' => 1000,
            'ttl' => 7200,
            'description' => 'Least Frequently Used - Rimuove gli elementi meno frequentemente utilizzati'
        ],
        'ttl' => [
            'class' => \App\Services\AI\Strategies\TTLCacheStrategy::class,
            'default_ttl' => 1800,
            'max_ttl' => 86400,
            'description' => 'Time To Live - Rimuove gli elementi scaduti'
        ],
        'fifo' => [
            'class' => \App\Services\AI\Strategies\FIFOCacheStrategy::class,
            'max_size' => 1000,
            'ttl' => 3600,
            'description' => 'First In First Out - Rimuove gli elementi più vecchi'
        ],
        'custom' => [
            'class' => \App\Services\AI\Strategies\CustomCacheStrategy::class,
            'max_size' => 1000,
            'ttl' => 3600,
            'description' => 'Strategia personalizzata basata su regole custom'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Drivers
    |--------------------------------------------------------------------------
    |
    | Configurazione per i driver di cache supportati.
    |
    */

    'drivers' => [
        'redis' => [
            'enabled' => env('AI_CACHE_REDIS_ENABLED', true),
            'connection' => env('AI_CACHE_REDIS_CONNECTION', 'default'),
            'prefix' => env('AI_CACHE_REDIS_PREFIX', 'ai_cache:'),
            'serializer' => 'json'
        ],
        'memcached' => [
            'enabled' => env('AI_CACHE_MEMCACHED_ENABLED', false),
            'servers' => [
                ['127.0.0.1', 11211, 100]
            ],
            'prefix' => 'ai_cache:'
        ],
        'database' => [
            'enabled' => env('AI_CACHE_DATABASE_ENABLED', false),
            'table' => 'ai_cache_entries',
            'connection' => 'default'
        ],
        'file' => [
            'enabled' => env('AI_CACHE_FILE_ENABLED', false),
            'path' => storage_path('app/ai_cache'),
            'permissions' => 0755
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Invalidation Rules
    |--------------------------------------------------------------------------
    |
    | Regole per l'invalidazione automatica della cache.
    |
    */

    'invalidation_rules' => [
        'user_*' => [
            'trigger' => 'user_update',
            'ttl' => 300,
            'description' => 'Invalidazione per aggiornamenti utente'
        ],
        'product_*' => [
            'trigger' => 'product_update',
            'ttl' => 600,
            'description' => 'Invalidazione per aggiornamenti prodotto'
        ],
        'global_*' => [
            'trigger' => 'global_update',
            'ttl' => 0,
            'description' => 'Invalidazione immediata per aggiornamenti globali'
        ],
        'session_*' => [
            'trigger' => 'session_end',
            'ttl' => 0,
            'description' => 'Invalidazione alla fine della sessione'
        ],
        'temp_*' => [
            'trigger' => 'manual',
            'ttl' => 60,
            'description' => 'Cache temporanea con TTL breve'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming
    |--------------------------------------------------------------------------
    |
    | Configurazione per il pre-riscaldamento della cache.
    |
    */

    'warming' => [
        'enabled' => env('AI_CACHE_WARMING_ENABLED', true),
        'schedule' => 'hourly', // hourly, daily, weekly
        'batch_size' => 100,
        'timeout' => 300, // 5 minuti
        'strategies' => [
            'common_queries' => [
                'enabled' => true,
                'queries' => [
                    'What is artificial intelligence?',
                    'How does machine learning work?',
                    'Best practices for AI development',
                    'AI ethics and responsible development',
                    'Future of artificial intelligence'
                ],
                'ttl' => 86400 // 24 ore
            ],
            'popular_requests' => [
                'enabled' => true,
                'limit' => 50,
                'ttl' => 3600 // 1 ora
            ],
            'user_specific' => [
                'enabled' => false,
                'user_limit' => 10,
                'ttl' => 1800 // 30 minuti
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il tracking delle analytics della cache.
    |
    */

    'analytics' => [
        'enabled' => env('AI_CACHE_ANALYTICS_ENABLED', true),
        'retention_days' => env('AI_CACHE_ANALYTICS_RETENTION', 90),
        'metrics' => [
            'hit_rate' => true,
            'miss_rate' => true,
            'response_time' => true,
            'cache_size' => true,
            'memory_usage' => true,
            'cost_savings' => true
        ],
        'alerts' => [
            'low_hit_rate' => 0.7, // 70%
            'high_memory_usage' => 0.8, // 80%
            'slow_response_time' => 1000 // 1 secondo
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la compressione delle risposte in cache.
    |
    */

    'compression' => [
        'enabled' => env('AI_CACHE_COMPRESSION_ENABLED', true),
        'algorithm' => env('AI_CACHE_COMPRESSION_ALGORITHM', 'gzip'), // gzip, lz4, zstd
        'threshold' => env('AI_CACHE_COMPRESSION_THRESHOLD', 1024), // bytes
        'level' => env('AI_CACHE_COMPRESSION_LEVEL', 6), // 1-9 per gzip
        'min_savings' => 0.1 // 10% di risparmio minimo per comprimere
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la sicurezza della cache.
    |
    */

    'security' => [
        'encrypt_sensitive' => env('AI_CACHE_ENCRYPT_SENSITIVE', true),
        'sensitive_patterns' => [
            'password*',
            'token*',
            'secret*',
            'private*',
            'personal*'
        ],
        'encryption_key' => env('AI_CACHE_ENCRYPTION_KEY'),
        'hash_keys' => env('AI_CACHE_HASH_KEYS', true),
        'key_salt' => env('AI_CACHE_KEY_SALT', 'ai_cache_salt')
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'ottimizzazione delle performance.
    |
    */

    'performance' => [
        'async_warming' => env('AI_CACHE_ASYNC_WARMING', true),
        'lazy_loading' => env('AI_CACHE_LAZY_LOADING', true),
        'prefetch_enabled' => env('AI_CACHE_PREFETCH_ENABLED', true),
        'prefetch_threshold' => 0.8, // 80% di probabilità
        'batch_operations' => env('AI_CACHE_BATCH_OPERATIONS', true),
        'batch_size' => 100,
        'connection_pooling' => env('AI_CACHE_CONNECTION_POOLING', true),
        'max_connections' => 10
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il monitoring della cache.
    |
    */

    'monitoring' => [
        'enabled' => env('AI_CACHE_MONITORING_ENABLED', true),
        'log_operations' => env('AI_CACHE_LOG_OPERATIONS', true),
        'log_level' => env('AI_CACHE_LOG_LEVEL', 'info'),
        'metrics_interval' => 60, // secondi
        'health_check_interval' => 300, // 5 minuti
        'alert_channels' => ['log', 'email'], // log, email, slack, webhook
        'alert_recipients' => env('AI_CACHE_ALERT_RECIPIENTS', ''),
        'webhook_url' => env('AI_CACHE_WEBHOOK_URL', '')
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Optimization
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'ottimizzazione dei costi.
    |
    */

    'cost_optimization' => [
        'enabled' => env('AI_CACHE_COST_OPTIMIZATION_ENABLED', true),
        'api_cost_per_request' => env('AI_CACHE_API_COST_PER_REQUEST', 0.01),
        'cache_cost_per_mb' => env('AI_CACHE_COST_PER_MB', 0.001),
        'optimization_threshold' => 0.5, // 50% di risparmio minimo
        'auto_eviction' => env('AI_CACHE_AUTO_EVICTION', true),
        'eviction_threshold' => 0.9, // 90% di utilizzo cache
        'cost_tracking' => env('AI_CACHE_COST_TRACKING', true)
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di tag della cache.
    |
    */

    'tags' => [
        'enabled' => env('AI_CACHE_TAGS_ENABLED', true),
        'max_tags_per_entry' => 10,
        'tag_ttl' => 86400, // 24 ore
        'auto_cleanup' => env('AI_CACHE_AUTO_CLEANUP_TAGS', true),
        'cleanup_interval' => 3600 // 1 ora
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il fallback in caso di errori della cache.
    |
    */

    'fallback' => [
        'enabled' => env('AI_CACHE_FALLBACK_ENABLED', true),
        'fallback_driver' => env('AI_CACHE_FALLBACK_DRIVER', 'file'),
        'retry_attempts' => 3,
        'retry_delay' => 100, // millisecondi
        'circuit_breaker' => env('AI_CACHE_CIRCUIT_BREAKER', true),
        'circuit_breaker_threshold' => 5,
        'circuit_breaker_timeout' => 60 // secondi
    ]
];
