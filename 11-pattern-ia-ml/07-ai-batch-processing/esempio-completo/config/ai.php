<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Batch Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il pattern AI Batch Processing
    |
    */

    'batch' => [
        'enabled' => env('AI_BATCH_ENABLED', true),
        'default_size' => env('AI_BATCH_SIZE', 100),
        'max_size' => env('AI_BATCH_MAX_SIZE', 1000),
        'timeout' => env('AI_BATCH_TIMEOUT', 300),
        'retry_attempts' => env('AI_BATCH_RETRY_ATTEMPTS', 3),
        'queue' => env('AI_BATCH_QUEUE', 'ai-batch'),
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => 'https://api.openai.com/v1',
            'batch_support' => true,
            'max_batch_size' => 1000,
            'batch_discount' => 0.1, // 10% di sconto
            'timeout' => 60,
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1',
            'batch_support' => true,
            'max_batch_size' => 500,
            'batch_discount' => 0.15, // 15% di sconto
            'timeout' => 60,
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'batch_support' => true,
            'max_batch_size' => 1000,
            'batch_discount' => 0.1, // 10% di sconto
            'timeout' => 60,
        ],
    ],

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),
    'default_model' => env('AI_DEFAULT_MODEL', 'gpt-3.5-turbo'),

    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('AI_RATE_LIMIT_RPM', 60),
        'burst_limit' => env('AI_RATE_LIMIT_BURST', 10),
    ],

    'caching' => [
        'enabled' => env('AI_CACHING_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 ora
        'store' => env('AI_CACHE_STORE', 'redis'),
    ],

    'monitoring' => [
        'enabled' => env('AI_MONITORING_ENABLED', true),
        'log_requests' => env('AI_LOG_REQUESTS', true),
        'log_responses' => env('AI_LOG_RESPONSES', false),
        'metrics_enabled' => env('AI_METRICS_ENABLED', true),
    ],
];
