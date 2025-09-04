<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prompt Engineering Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il sistema di Prompt Engineering che gestisce
    | template strutturati, variabili dinamiche e validazione dell'output.
    |
    */

    'default_ai_provider' => env('PROMPT_DEFAULT_AI_PROVIDER', 'openai'),

    'cache_enabled' => env('PROMPT_CACHE_ENABLED', true),

    'cache_ttl' => env('PROMPT_CACHE_TTL', 3600), // 1 ora

    'max_template_length' => env('PROMPT_MAX_TEMPLATE_LENGTH', 4000),

    'max_variables' => env('PROMPT_MAX_VARIABLES', 20),

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per i template di prompt disponibili.
    |
    */

    'templates' => [
        'product_description' => [
            'class' => \App\Services\Prompt\Templates\ProductDescriptionTemplate::class,
            'name' => 'Descrizione Prodotto',
            'description' => 'Template per generare descrizioni prodotto accattivanti',
            'variables' => ['product_name', 'features', 'price', 'category'],
            'validation_rules' => [
                'min_length' => 100,
                'max_length' => 500,
                'required_keywords' => ['caratteristiche', 'prezzo'],
                'forbidden_words' => ['fantastico', 'incredibile']
            ],
            'cost_estimate' => 0.002,
            'expected_duration' => 2.5
        ],

        'promotional_email' => [
            'class' => \App\Services\Prompt\Templates\EmailTemplate::class,
            'name' => 'Email Promozionale',
            'description' => 'Template per email marketing personalizzate',
            'variables' => ['customer_name', 'product', 'discount', 'expiry_date'],
            'validation_rules' => [
                'min_length' => 200,
                'max_length' => 800,
                'required_keywords' => ['sconto', 'offerta'],
                'required_sections' => ['oggetto', 'corpo', 'cta']
            ],
            'cost_estimate' => 0.003,
            'expected_duration' => 3.0
        ],

        'translation' => [
            'class' => \App\Services\Prompt\Templates\TranslationTemplate::class,
            'name' => 'Traduzione',
            'description' => 'Template per traduzioni professionali',
            'variables' => ['text', 'source_language', 'target_language', 'context'],
            'validation_rules' => [
                'min_length' => 10,
                'max_length' => 2000,
                'language_consistency' => true,
                'preserve_formatting' => true
            ],
            'cost_estimate' => 0.001,
            'expected_duration' => 1.5
        ],

        'content_analysis' => [
            'class' => \App\Services\Prompt\Templates\AnalysisTemplate::class,
            'name' => 'Analisi Contenuto',
            'description' => 'Template per analisi e sentiment analysis',
            'variables' => ['content', 'analysis_type', 'target_audience'],
            'validation_rules' => [
                'min_length' => 50,
                'max_length' => 1000,
                'required_sections' => ['riassunto', 'sentiment', 'raccomandazioni'],
                'sentiment_score_range' => [-1, 1]
            ],
            'cost_estimate' => 0.004,
            'expected_duration' => 4.0
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la validazione dell'output AI.
    |
    */

    'validation' => [
        'enabled' => env('PROMPT_VALIDATION_ENABLED', true),
        
        'rules' => [
            'length' => [
                'min' => env('PROMPT_MIN_LENGTH', 50),
                'max' => env('PROMPT_MAX_LENGTH', 1000)
            ],
            
            'keywords' => [
                'required' => env('PROMPT_REQUIRED_KEYWORDS', ''),
                'forbidden' => env('PROMPT_FORBIDDEN_KEYWORDS', '')
            ],
            
            'sentiment' => [
                'min_score' => env('PROMPT_MIN_SENTIMENT', 0.3),
                'max_score' => env('PROMPT_MAX_SENTIMENT', 0.8)
            ],
            
            'readability' => [
                'max_grade' => env('PROMPT_MAX_READABILITY_GRADE', 12),
                'min_grade' => env('PROMPT_MIN_READABILITY_GRADE', 6)
            ],
            
            'structure' => [
                'require_paragraphs' => env('PROMPT_REQUIRE_PARAGRAPHS', true),
                'min_paragraphs' => env('PROMPT_MIN_PARAGRAPHS', 2),
                'max_paragraphs' => env('PROMPT_MAX_PARAGRAPHS', 5)
            ]
        ],
        
        'ai_validation' => [
            'enabled' => env('PROMPT_AI_VALIDATION_ENABLED', true),
            'provider' => env('PROMPT_AI_VALIDATION_PROVIDER', 'openai'),
            'model' => env('PROMPT_AI_VALIDATION_MODEL', 'gpt-3.5-turbo'),
            'timeout' => env('PROMPT_AI_VALIDATION_TIMEOUT', 10)
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per l'ottimizzazione automatica dei prompt.
    |
    */

    'optimization' => [
        'enabled' => env('PROMPT_OPTIMIZATION_ENABLED', true),
        
        'metrics' => [
            'quality_score' => [
                'weight' => 0.4,
                'min_threshold' => 0.7
            ],
            'cost_efficiency' => [
                'weight' => 0.3,
                'max_cost_per_quality' => 0.01
            ],
            'response_time' => [
                'weight' => 0.2,
                'max_seconds' => 5.0
            ],
            'validation_success' => [
                'weight' => 0.1,
                'min_rate' => 0.9
            ]
        ],
        
        'ab_testing' => [
            'enabled' => env('PROMPT_AB_TESTING_ENABLED', true),
            'min_samples' => env('PROMPT_AB_MIN_SAMPLES', 10),
            'confidence_level' => env('PROMPT_AB_CONFIDENCE_LEVEL', 0.95),
            'test_duration_days' => env('PROMPT_AB_TEST_DURATION', 7)
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per il tracking e analytics dei prompt.
    |
    */

    'analytics' => [
        'enabled' => env('PROMPT_ANALYTICS_ENABLED', true),
        
        'tracking' => [
            'template_usage' => true,
            'variable_impact' => true,
            'performance_metrics' => true,
            'cost_analysis' => true,
            'user_behavior' => true
        ],
        
        'retention_days' => env('PROMPT_ANALYTICS_RETENTION_DAYS', 90),
        
        'reports' => [
            'daily' => true,
            'weekly' => true,
            'monthly' => true,
            'custom_periods' => true
        ]
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
        'sanitize_input' => env('PROMPT_SANITIZE_INPUT', true),
        'max_variable_length' => env('PROMPT_MAX_VARIABLE_LENGTH', 1000),
        'allowed_html_tags' => env('PROMPT_ALLOWED_HTML_TAGS', 'b,i,em,strong'),
        'rate_limit_per_user' => env('PROMPT_RATE_LIMIT_PER_USER', 100),
        'rate_limit_per_hour' => env('PROMPT_RATE_LIMIT_PER_HOUR', 1000)
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per la cache dei template e risultati.
    |
    */

    'cache' => [
        'driver' => env('PROMPT_CACHE_DRIVER', 'redis'),
        'prefix' => 'prompt_engineering:',
        'template_ttl' => env('PROMPT_TEMPLATE_CACHE_TTL', 86400), // 24 ore
        'result_ttl' => env('PROMPT_RESULT_CACHE_TTL', 3600), // 1 ora
        'variable_ttl' => env('PROMPT_VARIABLE_CACHE_TTL', 1800) // 30 minuti
    ]
];
