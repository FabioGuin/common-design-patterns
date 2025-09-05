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
    | Chain of Responsibility Pattern Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Chain of Responsibility Pattern example
    |
    */

    'approval' => [
        'enabled' => env('APPROVAL_ENABLED', true),
        'manager_approval_threshold' => env('MANAGER_APPROVAL_THRESHOLD', 1000),
        'director_approval_threshold' => env('DIRECTOR_APPROVAL_THRESHOLD', 5000),
        'credit_check_enabled' => env('CREDIT_CHECK_ENABLED', true),
        'inventory_check_enabled' => env('INVENTORY_CHECK_ENABLED', true),
    ],

];
