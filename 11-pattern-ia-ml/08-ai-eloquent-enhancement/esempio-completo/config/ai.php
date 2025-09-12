<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per i servizi di intelligenza artificiale utilizzati
    | nell'applicazione Laravel.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | Provider AI di default da utilizzare. Deve essere uno dei provider
    | configurati nella sezione 'providers'.
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la cache delle risposte AI.
    |
    */

    'cache_ttl' => env('AI_CACHE_TTL', 3600), // 1 ora in secondi
    'cache_prefix' => 'ai_',

    /*
    |--------------------------------------------------------------------------
    | Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per i diversi provider AI supportati.
    |
    */

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-3.5-turbo',
            'embedding_model' => 'text-embedding-ada-002',
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'timeout' => 30,
        ],

        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1',
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'timeout' => 30,
        ],

        'gemini' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'model' => 'gemini-pro',
            'embedding_model' => 'embedding-001',
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il fallback quando i servizi AI non sono disponibili.
    |
    */

    'fallback' => [
        'enabled' => true,
        'provider' => 'openai', // Provider di fallback
        'retry_attempts' => 3,
        'retry_delay' => 1000, // millisecondi
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configurazione per il rate limiting delle chiamate AI.
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        'max_requests_per_minute' => 60,
        'max_requests_per_hour' => 1000,
        'max_requests_per_day' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Processing
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'elaborazione dei contenuti AI.
    |
    */

    'content_processing' => [
        'max_content_length' => 10000, // caratteri
        'truncate_long_content' => true,
        'preserve_formatting' => true,
        'remove_sensitive_data' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configurazione per il logging delle chiamate AI.
    |
    */

    'logging' => [
        'enabled' => true,
        'log_level' => 'info',
        'log_requests' => true,
        'log_responses' => false, // Per privacy
        'log_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Configurazione per la sicurezza delle chiamate AI.
    |
    */

    'security' => [
        'validate_api_keys' => true,
        'encrypt_responses' => false,
        'sanitize_input' => true,
        'max_prompt_length' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'ottimizzazione delle performance AI.
    |
    */

    'performance' => [
        'enable_caching' => true,
        'cache_embeddings' => true,
        'batch_requests' => false,
        'async_processing' => false,
        'connection_pooling' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Categorie predefinite per la classificazione dei contenuti.
    |
    */

    'categories' => [
        'tecnologia',
        'cucina',
        'viaggi',
        'sport',
        'cultura',
        'business',
        'salute',
        'educazione',
        'intrattenimento',
        'scienza',
        'arte',
        'musica',
        'cinema',
        'libri',
        'fotografia',
    ],

    /*
    |--------------------------------------------------------------------------
    | Languages
    |--------------------------------------------------------------------------
    |
    | Lingue supportate per la traduzione.
    |
    */

    'languages' => [
        'en' => 'Inglese',
        'es' => 'Spagnolo',
        'fr' => 'Francese',
        'de' => 'Tedesco',
        'it' => 'Italiano',
        'pt' => 'Portoghese',
        'ru' => 'Russo',
        'ja' => 'Giapponese',
        'ko' => 'Coreano',
        'zh' => 'Cinese',
    ],
];
