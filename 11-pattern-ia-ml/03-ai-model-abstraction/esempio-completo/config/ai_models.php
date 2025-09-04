<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Model Abstraction Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di astrazione dei modelli AI che gestisce
    | multiple provider con interfaccia unificata e selezione intelligente.
    |
    */

    'default_strategy' => env('AI_MODEL_STRATEGY', 'balanced'),

    'auto_fallback' => env('AI_MODEL_AUTO_FALLBACK', true),

    'max_retries' => env('AI_MODEL_MAX_RETRIES', 3),

    'timeout' => env('AI_MODEL_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Model Selection Strategies
    |--------------------------------------------------------------------------
    |
    | Strategie per la selezione del modello migliore.
    |
    */

    'strategies' => [
        'balanced' => [
            'cost_weight' => 0.3,
            'performance_weight' => 0.4,
            'availability_weight' => 0.3,
            'description' => 'Bilanciata tra costo, performance e disponibilità'
        ],
        'cost_optimized' => [
            'cost_weight' => 0.7,
            'performance_weight' => 0.2,
            'availability_weight' => 0.1,
            'description' => 'Ottimizzata per ridurre i costi'
        ],
        'performance_optimized' => [
            'cost_weight' => 0.1,
            'performance_weight' => 0.7,
            'availability_weight' => 0.2,
            'description' => 'Ottimizzata per massime performance'
        ],
        'reliability_optimized' => [
            'cost_weight' => 0.2,
            'performance_weight' => 0.3,
            'availability_weight' => 0.5,
            'description' => 'Ottimizzata per massima affidabilità'
        ]
    ],

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
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => 'https://api.openai.com/v1',
            'timeout' => 30,
            'retry_attempts' => 3,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 150000
            ]
        ],
        'claude' => [
            'enabled' => env('CLAUDE_ENABLED', true),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1',
            'timeout' => 30,
            'retry_attempts' => 3,
            'rate_limit' => [
                'requests_per_minute' => 50,
                'tokens_per_minute' => 100000
            ]
        ],
        'gemini' => [
            'enabled' => env('GEMINI_ENABLED', true),
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout' => 30,
            'retry_attempts' => 3,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 120000
            ]
        ],
        'huggingface' => [
            'enabled' => env('HUGGINGFACE_ENABLED', true),
            'api_key' => env('HUGGINGFACE_API_KEY'),
            'base_url' => 'https://api-inference.huggingface.co/models',
            'timeout' => 30,
            'retry_attempts' => 3,
            'rate_limit' => [
                'requests_per_minute' => 100,
                'tokens_per_minute' => 50000
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Registry
    |--------------------------------------------------------------------------
    |
    | Registro di tutti i modelli AI disponibili con le loro caratteristiche.
    |
    */

    'models' => [
        // OpenAI Models
        'gpt-4' => [
            'provider' => 'openai',
            'name' => 'GPT-4',
            'description' => 'Modello più avanzato per ragionamento complesso',
            'capabilities' => ['text_generation', 'reasoning', 'code_generation', 'analysis'],
            'cost_per_token' => 0.00003,
            'max_tokens' => 8192,
            'context_window' => 128000,
            'max_requests_per_minute' => 60,
            'priority' => 1,
            'enabled' => true,
            'tags' => ['premium', 'reasoning', 'code']
        ],
        'gpt-3.5-turbo' => [
            'provider' => 'openai',
            'name' => 'GPT-3.5 Turbo',
            'description' => 'Modello veloce ed economico per task generali',
            'capabilities' => ['text_generation', 'translation', 'summarization'],
            'cost_per_token' => 0.0000015,
            'max_tokens' => 4096,
            'context_window' => 16384,
            'max_requests_per_minute' => 60,
            'priority' => 2,
            'enabled' => true,
            'tags' => ['fast', 'cheap', 'general']
        ],
        'dall-e-3' => [
            'provider' => 'openai',
            'name' => 'DALL-E 3',
            'description' => 'Generazione immagini di alta qualità',
            'capabilities' => ['image_generation'],
            'cost_per_image' => 0.040,
            'max_images' => 1,
            'max_requests_per_minute' => 20,
            'priority' => 1,
            'enabled' => true,
            'tags' => ['image', 'art', 'creative']
        ],

        // Claude Models
        'claude-3-opus' => [
            'provider' => 'claude',
            'name' => 'Claude 3 Opus',
            'description' => 'Modello più potente per analisi complesse',
            'capabilities' => ['text_generation', 'reasoning', 'analysis', 'long_context'],
            'cost_per_token' => 0.000075,
            'max_tokens' => 200000,
            'context_window' => 200000,
            'max_requests_per_minute' => 50,
            'priority' => 1,
            'enabled' => true,
            'tags' => ['premium', 'analysis', 'long_context']
        ],
        'claude-3-sonnet' => [
            'provider' => 'claude',
            'name' => 'Claude 3 Sonnet',
            'description' => 'Bilanciato tra performance e costo',
            'capabilities' => ['text_generation', 'document_analysis', 'long_context'],
            'cost_per_token' => 0.000015,
            'max_tokens' => 200000,
            'context_window' => 200000,
            'max_requests_per_minute' => 50,
            'priority' => 2,
            'enabled' => true,
            'tags' => ['balanced', 'document', 'long_context']
        ],
        'claude-3-haiku' => [
            'provider' => 'claude',
            'name' => 'Claude 3 Haiku',
            'description' => 'Modello veloce per task semplici',
            'capabilities' => ['text_generation', 'translation', 'summarization'],
            'cost_per_token' => 0.0000025,
            'max_tokens' => 200000,
            'context_window' => 200000,
            'max_requests_per_minute' => 50,
            'priority' => 3,
            'enabled' => true,
            'tags' => ['fast', 'cheap', 'simple']
        ],

        // Gemini Models
        'gemini-pro' => [
            'provider' => 'gemini',
            'name' => 'Gemini Pro',
            'description' => 'Modello versatile per task generali',
            'capabilities' => ['text_generation', 'translation', 'analysis'],
            'cost_per_token' => 0.00001,
            'max_tokens' => 32768,
            'context_window' => 32768,
            'max_requests_per_minute' => 60,
            'priority' => 2,
            'enabled' => true,
            'tags' => ['versatile', 'general', 'multimodal']
        ],
        'gemini-pro-vision' => [
            'provider' => 'gemini',
            'name' => 'Gemini Pro Vision',
            'description' => 'Modello per analisi di immagini e testo',
            'capabilities' => ['text_generation', 'image_analysis', 'multimodal'],
            'cost_per_token' => 0.00001,
            'max_tokens' => 16384,
            'context_window' => 16384,
            'max_requests_per_minute' => 60,
            'priority' => 2,
            'enabled' => true,
            'tags' => ['vision', 'multimodal', 'analysis']
        ],

        // Hugging Face Models
        'microsoft/DialoGPT-medium' => [
            'provider' => 'huggingface',
            'name' => 'DialoGPT Medium',
            'description' => 'Modello per conversazioni e chatbot',
            'capabilities' => ['text_generation', 'conversation'],
            'cost_per_token' => 0.000001,
            'max_tokens' => 1024,
            'context_window' => 1024,
            'max_requests_per_minute' => 100,
            'priority' => 3,
            'enabled' => true,
            'tags' => ['conversation', 'chatbot', 'free']
        ],
        'facebook/blenderbot-400M-distill' => [
            'provider' => 'huggingface',
            'name' => 'BlenderBot 400M',
            'description' => 'Modello per conversazioni naturali',
            'capabilities' => ['text_generation', 'conversation'],
            'cost_per_token' => 0.000001,
            'max_tokens' => 512,
            'context_window' => 512,
            'max_requests_per_minute' => 100,
            'priority' => 3,
            'enabled' => true,
            'tags' => ['conversation', 'natural', 'free']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Mapping
    |--------------------------------------------------------------------------
    |
    | Mappatura dei task ai modelli più adatti.
    |
    */

    'task_mapping' => [
        'text_generation' => [
            'preferred_models' => ['gpt-4', 'claude-3-sonnet', 'gemini-pro'],
            'fallback_models' => ['gpt-3.5-turbo', 'claude-3-haiku']
        ],
        'translation' => [
            'preferred_models' => ['gpt-3.5-turbo', 'gemini-pro', 'claude-3-haiku'],
            'fallback_models' => ['gpt-4', 'claude-3-sonnet']
        ],
        'code_generation' => [
            'preferred_models' => ['gpt-4', 'claude-3-opus', 'gemini-pro'],
            'fallback_models' => ['gpt-3.5-turbo', 'claude-3-sonnet']
        ],
        'analysis' => [
            'preferred_models' => ['claude-3-opus', 'gpt-4', 'gemini-pro'],
            'fallback_models' => ['claude-3-sonnet', 'gpt-3.5-turbo']
        ],
        'image_generation' => [
            'preferred_models' => ['dall-e-3'],
            'fallback_models' => []
        ],
        'conversation' => [
            'preferred_models' => ['claude-3-sonnet', 'gpt-4', 'microsoft/DialoGPT-medium'],
            'fallback_models' => ['gpt-3.5-turbo', 'facebook/blenderbot-400M-distill']
        ],
        'summarization' => [
            'preferred_models' => ['claude-3-sonnet', 'gpt-3.5-turbo', 'gemini-pro'],
            'fallback_models' => ['claude-3-haiku', 'gpt-4']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Tracking
    |--------------------------------------------------------------------------
    |
    | Configurazione per il tracking delle performance dei modelli.
    |
    */

    'performance_tracking' => [
        'enabled' => env('AI_MODEL_PERFORMANCE_TRACKING', true),
        'retention_days' => env('AI_MODEL_RETENTION_DAYS', 90),
        'metrics' => [
            'response_time' => true,
            'success_rate' => true,
            'cost_per_request' => true,
            'quality_score' => true,
            'error_rate' => true
        ],
        'alerts' => [
            'high_error_rate' => 0.1, // 10%
            'slow_response' => 10.0, // 10 secondi
            'high_cost' => 0.10 // $0.10 per richiesta
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la cache delle risposte e metadati.
    |
    */

    'cache' => [
        'enabled' => env('AI_MODEL_CACHE_ENABLED', true),
        'driver' => env('AI_MODEL_CACHE_DRIVER', 'redis'),
        'prefix' => 'ai_model:',
        'ttl' => env('AI_MODEL_CACHE_TTL', 3600), // 1 ora
        'response_cache_ttl' => env('AI_MODEL_RESPONSE_CACHE_TTL', 1800), // 30 min
        'model_metadata_ttl' => env('AI_MODEL_METADATA_CACHE_TTL', 86400) // 24 ore
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
        'enabled' => env('AI_MODEL_MONITORING_ENABLED', true),
        'log_requests' => env('AI_MODEL_LOG_REQUESTS', true),
        'log_responses' => env('AI_MODEL_LOG_RESPONSES', false),
        'log_errors' => env('AI_MODEL_LOG_ERRORS', true),
        'save_to_database' => env('AI_MODEL_SAVE_TO_DATABASE', true),
        'alert_on_failure' => env('AI_MODEL_ALERT_ON_FAILURE', true)
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
        'encrypt_responses' => env('AI_MODEL_ENCRYPT_RESPONSES', false),
        'mask_sensitive_data' => env('AI_MODEL_MASK_SENSITIVE_DATA', true),
        'allowed_ips' => env('AI_MODEL_ALLOWED_IPS', ''),
        'rate_limit_by_ip' => env('AI_MODEL_RATE_LIMIT_BY_IP', true),
        'max_requests_per_hour' => env('AI_MODEL_MAX_REQUESTS_PER_HOUR', 1000)
    ]
];
