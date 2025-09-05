<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Pattern Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Proxy Pattern example
    |
    */

    'external_api' => [
        'url' => env('EXTERNAL_API_URL', 'https://jsonplaceholder.typicode.com'),
        'timeout' => env('EXTERNAL_API_TIMEOUT', 30),
        'retry_attempts' => env('EXTERNAL_API_RETRY_ATTEMPTS', 3),
    ],

    'cache_ttl' => env('CACHE_TTL', 3600), // 1 ora in secondi

    'access_control' => [
        'enabled' => env('ACCESS_CONTROL_ENABLED', true),
        'roles' => [
            'admin' => ['read', 'write', 'delete'],
            'moderator' => ['read', 'write'],
            'user' => ['read'],
            'guest' => []
        ]
    ],

    'logging' => [
        'enabled' => env('LOGGING_ENABLED', true),
        'level' => env('LOG_LEVEL', 'info'),
        'include_timing' => env('LOGGING_INCLUDE_TIMING', true),
    ],

];
