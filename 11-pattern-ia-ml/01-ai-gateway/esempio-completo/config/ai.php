<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema AI Gateway che gestisce multiple provider
    | AI con fallback automatici, rate limiting e caching.
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    'fallback_strategy' => env('AI_FALLBACK_STRATEGY', 'priority'), // priority, cost, performance

    'timeout' => env('AI_TIMEOUT', 30), // secondi

    'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per ogni provider AI supportato.
    |
    */

    'providers' => [
        'openai' => [
            'enabled' => env('OPENAI_ENABLED', true),
            'priority' => 1,
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => 'https://api.openai.com/v1',
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
            'timeout' => 30,
            'cost_per_token' => 0.00003, // Costo approssimativo per token
        ],

        'claude' => [
            'enabled' => env('CLAUDE_ENABLED', true),
            'priority' => 2,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1',
            'model' => env('CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
            'max_tokens' => env('CLAUDE_MAX_TOKENS', 2000),
            'temperature' => env('CLAUDE_TEMPERATURE', 0.7),
            'timeout' => 30,
            'cost_per_token' => 0.000015,
        ],

        'gemini' => [
            'enabled' => env('GEMINI_ENABLED', true),
            'priority' => 3,
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 2000),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
            'timeout' => 30,
            'cost_per_token' => 0.00001,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il rate limiting di ogni provider.
    |
    */

    'rate_limits' => [
        'openai' => [
            'requests_per_minute' => env('OPENAI_RPM', 60),
            'tokens_per_minute' => env('OPENAI_TPM', 150000),
            'requests_per_day' => env('OPENAI_RPD', 10000),
        ],

        'claude' => [
            'requests_per_minute' => env('CLAUDE_RPM', 50),
            'tokens_per_minute' => env('CLAUDE_TPM', 100000),
            'requests_per_day' => env('CLAUDE_RPD', 5000),
        ],

        'gemini' => [
            'requests_per_minute' => env('GEMINI_RPM', 60),
            'tokens_per_minute' => env('GEMINI_TPM', 120000),
            'requests_per_day' => env('GEMINI_RPD', 15000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la cache delle risposte AI.
    |
    */

    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'driver' => env('AI_CACHE_DRIVER', 'redis'),
        'prefix' => 'ai_gateway:',
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 ora
        'max_size' => env('AI_CACHE_MAX_SIZE', '100MB'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il monitoring e logging.
    |
    */

    'monitoring' => [
        'enabled' => env('AI_MONITORING_ENABLED', true),
        'log_requests' => env('AI_LOG_REQUESTS', true),
        'log_responses' => env('AI_LOG_RESPONSES', false),
        'save_to_database' => env('AI_SAVE_TO_DATABASE', true),
        'alert_on_failure' => env('AI_ALERT_ON_FAILURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per i controlli di salute dei provider.
    |
    */

    'health_check' => [
        'enabled' => env('AI_HEALTH_CHECK_ENABLED', true),
        'interval' => env('AI_HEALTH_CHECK_INTERVAL', 300), // 5 minuti
        'timeout' => env('AI_HEALTH_CHECK_TIMEOUT', 10),
        'test_prompt' => 'Test health check',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di fallback.
    |
    */

    'fallback' => [
        'enabled' => env('AI_FALLBACK_ENABLED', true),
        'max_attempts' => env('AI_FALLBACK_MAX_ATTEMPTS', 3),
        'backoff_multiplier' => env('AI_FALLBACK_BACKOFF_MULTIPLIER', 2),
        'max_backoff' => env('AI_FALLBACK_MAX_BACKOFF', 60), // secondi
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la sicurezza del sistema.
    |
    */

    'security' => [
        'encrypt_responses' => env('AI_ENCRYPT_RESPONSES', false),
        'mask_sensitive_data' => env('AI_MASK_SENSITIVE_DATA', true),
        'allowed_ips' => env('AI_ALLOWED_IPS', ''),
        'rate_limit_by_ip' => env('AI_RATE_LIMIT_BY_IP', true),
    ],
];
