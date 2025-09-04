<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurazione Pool di Default
    |--------------------------------------------------------------------------
    |
    | Configurazione per il pool di connessioni di default
    |
    */
    'default' => [
        'max_size' => env('POOL_DEFAULT_MAX_SIZE', 10),
        'timeout' => env('POOL_DEFAULT_TIMEOUT', 30),
        'retry_attempts' => env('POOL_DEFAULT_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pool Aggiuntivi
    |--------------------------------------------------------------------------
    |
    | Configurazione per pool aggiuntivi
    |
    */
    'additional_pools' => [
        // Esempio di pool aggiuntivo
        // [
        //     'name' => 'read-only',
        //     'connection' => 'mysql_read',
        //     'max_size' => 5,
        //     'timeout' => 15,
        //     'retry_attempts' => 2,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurazione Monitoraggio
    |--------------------------------------------------------------------------
    |
    | Configurazione per il monitoraggio dei pool
    |
    */
    'monitoring' => [
        'enabled' => env('POOL_MONITORING_ENABLED', true),
        'log_level' => env('POOL_LOG_LEVEL', 'info'),
        'health_check_interval' => env('POOL_HEALTH_CHECK_INTERVAL', 60), // secondi
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurazione Performance
    |--------------------------------------------------------------------------
    |
    | Configurazione per ottimizzazioni di performance
    |
    */
    'performance' => [
        'connection_validation' => env('POOL_CONNECTION_VALIDATION', true),
        'auto_reset_on_error' => env('POOL_AUTO_RESET_ON_ERROR', true),
        'max_retry_delay' => env('POOL_MAX_RETRY_DELAY', 1000), // millisecondi
    ],
];
