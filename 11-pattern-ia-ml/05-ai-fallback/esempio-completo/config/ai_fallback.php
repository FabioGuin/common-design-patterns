<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di fallback AI che garantisce la continuità
    | del servizio AI attraverso strategie di fallback intelligenti.
    |
    */

    'enabled' => env('AI_FALLBACK_ENABLED', true),

    'default_strategy' => env('AI_FALLBACK_DEFAULT_STRATEGY', 'provider_chain'),

    'timeout' => env('AI_FALLBACK_TIMEOUT', 30),

    'max_retries' => env('AI_FALLBACK_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per i provider AI supportati.
    |
    */

    'providers' => [
        'openai' => [
            'class' => \App\Services\AI\Providers\OpenAIProvider::class,
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'priority' => 1,
            'timeout' => 30,
            'retry_attempts' => 3,
            'enabled' => true,
            'description' => 'OpenAI GPT Models'
        ],
        'anthropic' => [
            'class' => \App\Services\AI\Providers\AnthropicProvider::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
            'priority' => 2,
            'timeout' => 30,
            'retry_attempts' => 3,
            'enabled' => true,
            'description' => 'Anthropic Claude Models'
        ],
        'google_ai' => [
            'class' => \App\Services\AI\Providers\GoogleAIProvider::class,
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'base_url' => env('GOOGLE_AI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'priority' => 3,
            'timeout' => 30,
            'retry_attempts' => 3,
            'enabled' => true,
            'description' => 'Google AI Gemini Models'
        ],
        'huggingface' => [
            'class' => \App\Services\AI\Providers\HuggingFaceProvider::class,
            'api_key' => env('HUGGINGFACE_API_KEY'),
            'base_url' => env('HUGGINGFACE_BASE_URL', 'https://api-inference.huggingface.co'),
            'priority' => 4,
            'timeout' => 30,
            'retry_attempts' => 3,
            'enabled' => true,
            'description' => 'Hugging Face Models'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategies
    |--------------------------------------------------------------------------
    |
    | Strategie di fallback disponibili.
    |
    */

    'strategies' => [
        'provider_chain' => [
            'class' => \App\Services\AI\Strategies\ProviderFallbackStrategy::class,
            'providers' => ['openai', 'anthropic', 'google_ai'],
            'circuit_breaker' => true,
            'retry_enabled' => true,
            'description' => 'Fallback sequenziale tra provider'
        ],
        'cache_fallback' => [
            'class' => \App\Services\AI\Strategies\CacheFallbackStrategy::class,
            'cache_ttl' => 3600,
            'fallback_to_static' => true,
            'cache_key_prefix' => 'ai_fallback:',
            'description' => 'Fallback su cache con risposte statiche'
        ],
        'queue_fallback' => [
            'class' => \App\Services\AI\Strategies\QueueFallbackStrategy::class,
            'queue_name' => 'ai-processing',
            'delay' => 60,
            'max_attempts' => 3,
            'timeout' => 300,
            'description' => 'Fallback su elaborazione asincrona'
        ],
        'static_fallback' => [
            'class' => \App\Services\AI\Strategies\StaticFallbackStrategy::class,
            'static_responses' => [
                'What is AI?' => 'AI (Artificial Intelligence) is a branch of computer science that aims to create machines capable of intelligent behavior.',
                'How does machine learning work?' => 'Machine learning is a subset of AI that enables computers to learn and improve from experience without being explicitly programmed.',
                'What are neural networks?' => 'Neural networks are computing systems inspired by biological neural networks that can learn to perform tasks by analyzing training data.'
            ],
            'default_response' => 'I apologize, but I am currently unable to process your request. Please try again later.',
            'description' => 'Fallback su risposte statiche predefinite'
        ],
        'hybrid_fallback' => [
            'class' => \App\Services\AI\Strategies\HybridFallbackStrategy::class,
            'primary_strategy' => 'provider_chain',
            'secondary_strategy' => 'cache_fallback',
            'tertiary_strategy' => 'static_fallback',
            'description' => 'Fallback ibrido con multiple strategie'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il circuit breaker pattern.
    |
    */

    'circuit_breaker' => [
        'enabled' => env('AI_FALLBACK_CIRCUIT_BREAKER_ENABLED', true),
        'failure_threshold' => env('AI_FALLBACK_FAILURE_THRESHOLD', 5),
        'recovery_timeout' => env('AI_FALLBACK_RECOVERY_TIMEOUT', 60),
        'half_open_max_calls' => env('AI_FALLBACK_HALF_OPEN_MAX_CALLS', 3),
        'success_threshold' => env('AI_FALLBACK_SUCCESS_THRESHOLD', 2),
        'states' => [
            'closed' => 'Normal operation',
            'open' => 'Circuit is open, failing fast',
            'half_open' => 'Testing if service is back'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per le strategie di retry.
    |
    */

    'retry' => [
        'enabled' => env('AI_FALLBACK_RETRY_ENABLED', true),
        'max_attempts' => env('AI_FALLBACK_MAX_ATTEMPTS', 3),
        'backoff_strategy' => env('AI_FALLBACK_BACKOFF_STRATEGY', 'exponential'), // linear, exponential, fixed
        'base_delay' => env('AI_FALLBACK_BASE_DELAY', 1000), // millisecondi
        'max_delay' => env('AI_FALLBACK_MAX_DELAY', 30000), // millisecondi
        'jitter' => env('AI_FALLBACK_JITTER', true), // Aggiunge randomizzazione
        'retryable_errors' => [
            'timeout',
            'connection_error',
            'rate_limit',
            'service_unavailable'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il monitoraggio della salute dei provider.
    |
    */

    'health_monitoring' => [
        'enabled' => env('AI_FALLBACK_HEALTH_MONITORING_ENABLED', true),
        'check_interval' => env('AI_FALLBACK_HEALTH_CHECK_INTERVAL', 60), // secondi
        'timeout' => env('AI_FALLBACK_HEALTH_TIMEOUT', 10), // secondi
        'failure_threshold' => env('AI_FALLBACK_HEALTH_FAILURE_THRESHOLD', 3),
        'success_threshold' => env('AI_FALLBACK_HEALTH_SUCCESS_THRESHOLD', 2),
        'health_check_endpoint' => '/health',
        'health_check_payload' => [
            'prompt' => 'Health check',
            'max_tokens' => 10
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Classification Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la classificazione degli errori.
    |
    */

    'error_classification' => [
        'enabled' => env('AI_FALLBACK_ERROR_CLASSIFICATION_ENABLED', true),
        'classifiers' => [
            'network_error' => [
                'patterns' => ['timeout', 'connection', 'network'],
                'retryable' => true,
                'fallback_strategy' => 'provider_chain'
            ],
            'rate_limit_error' => [
                'patterns' => ['rate_limit', 'quota', 'limit'],
                'retryable' => true,
                'retry_delay' => 60000, // 1 minuto
                'fallback_strategy' => 'cache_fallback'
            ],
            'authentication_error' => [
                'patterns' => ['unauthorized', 'forbidden', 'auth'],
                'retryable' => false,
                'fallback_strategy' => 'static_fallback'
            ],
            'quota_error' => [
                'patterns' => ['quota', 'billing', 'payment'],
                'retryable' => false,
                'fallback_strategy' => 'static_fallback'
            ],
            'service_error' => [
                'patterns' => ['server_error', 'service', 'internal'],
                'retryable' => true,
                'fallback_strategy' => 'provider_chain'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il logging del sistema di fallback.
    |
    */

    'logging' => [
        'enabled' => env('AI_FALLBACK_LOGGING_ENABLED', true),
        'log_level' => env('AI_FALLBACK_LOG_LEVEL', 'info'),
        'log_failures' => env('AI_FALLBACK_LOG_FAILURES', true),
        'log_successes' => env('AI_FALLBACK_LOG_SUCCESSES', false),
        'log_retries' => env('AI_FALLBACK_LOG_RETRIES', true),
        'log_circuit_breaker' => env('AI_FALLBACK_LOG_CIRCUIT_BREAKER', true),
        'log_health_checks' => env('AI_FALLBACK_LOG_HEALTH_CHECKS', true),
        'retention_days' => env('AI_FALLBACK_LOG_RETENTION_DAYS', 30)
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il caching delle risposte AI.
    |
    */

    'cache' => [
        'enabled' => env('AI_FALLBACK_CACHE_ENABLED', true),
        'driver' => env('AI_FALLBACK_CACHE_DRIVER', 'redis'),
        'ttl' => env('AI_FALLBACK_CACHE_TTL', 3600), // secondi
        'key_prefix' => env('AI_FALLBACK_CACHE_KEY_PREFIX', 'ai_fallback:'),
        'compress' => env('AI_FALLBACK_CACHE_COMPRESS', true),
        'encrypt' => env('AI_FALLBACK_CACHE_ENCRYPT', false)
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'elaborazione asincrona.
    |
    */

    'queue' => [
        'enabled' => env('AI_FALLBACK_QUEUE_ENABLED', true),
        'connection' => env('AI_FALLBACK_QUEUE_CONNECTION', 'redis'),
        'queue_name' => env('AI_FALLBACK_QUEUE_NAME', 'ai-processing'),
        'delay' => env('AI_FALLBACK_QUEUE_DELAY', 60), // secondi
        'max_attempts' => env('AI_FALLBACK_QUEUE_MAX_ATTEMPTS', 3),
        'timeout' => env('AI_FALLBACK_QUEUE_TIMEOUT', 300), // secondi
        'retry_after' => env('AI_FALLBACK_QUEUE_RETRY_AFTER', 90) // secondi
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
        'parallel_requests' => env('AI_FALLBACK_PARALLEL_REQUESTS', false),
        'max_concurrent_requests' => env('AI_FALLBACK_MAX_CONCURRENT_REQUESTS', 5),
        'request_timeout' => env('AI_FALLBACK_REQUEST_TIMEOUT', 30), // secondi
        'response_timeout' => env('AI_FALLBACK_RESPONSE_TIMEOUT', 60), // secondi
        'memory_limit' => env('AI_FALLBACK_MEMORY_LIMIT', '256M'),
        'enable_compression' => env('AI_FALLBACK_ENABLE_COMPRESSION', true)
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
        'encrypt_responses' => env('AI_FALLBACK_ENCRYPT_RESPONSES', false),
        'encryption_key' => env('AI_FALLBACK_ENCRYPTION_KEY'),
        'sanitize_input' => env('AI_FALLBACK_SANITIZE_INPUT', true),
        'rate_limiting' => [
            'enabled' => env('AI_FALLBACK_RATE_LIMITING_ENABLED', true),
            'max_requests_per_minute' => env('AI_FALLBACK_MAX_REQUESTS_PER_MINUTE', 60),
            'max_requests_per_hour' => env('AI_FALLBACK_MAX_REQUESTS_PER_HOUR', 1000)
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il monitoring e le metriche.
    |
    */

    'monitoring' => [
        'enabled' => env('AI_FALLBACK_MONITORING_ENABLED', true),
        'metrics_collection' => env('AI_FALLBACK_METRICS_COLLECTION', true),
        'alerting' => [
            'enabled' => env('AI_FALLBACK_ALERTING_ENABLED', true),
            'failure_rate_threshold' => env('AI_FALLBACK_FAILURE_RATE_THRESHOLD', 0.5), // 50%
            'response_time_threshold' => env('AI_FALLBACK_RESPONSE_TIME_THRESHOLD', 10), // secondi
            'notification_channels' => ['email', 'slack', 'webhook']
        ],
        'dashboards' => [
            'enabled' => env('AI_FALLBACK_DASHBOARDS_ENABLED', true),
            'real_time_updates' => env('AI_FALLBACK_REAL_TIME_UPDATES', true),
            'auto_refresh_interval' => env('AI_FALLBACK_AUTO_REFRESH_INTERVAL', 30) // secondi
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione generale per il sistema di fallback.
    |
    */

    'fallback' => [
        'graceful_degradation' => env('AI_FALLBACK_GRACEFUL_DEGRADATION', true),
        'fallback_to_static' => env('AI_FALLBACK_FALLBACK_TO_STATIC', true),
        'fallback_to_cache' => env('AI_FALLBACK_FALLBACK_TO_CACHE', true),
        'fallback_to_queue' => env('AI_FALLBACK_FALLBACK_TO_QUEUE', true),
        'max_fallback_depth' => env('AI_FALLBACK_MAX_FALLBACK_DEPTH', 3),
        'fallback_timeout' => env('AI_FALLBACK_FALLBACK_TIMEOUT', 120) // secondi
    ]
];
