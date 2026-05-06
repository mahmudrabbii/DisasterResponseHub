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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'weather' => [
        'default_location' => env('WEATHER_DEFAULT_LOCATION', 'Dhaka, Bangladesh'),
    ],

    'stripe' => [
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    'shurjopay' => [
        'api_endpoint' => env('SHURJOPAY_API'),
        'username' => env('SP_USERNAME'),
        'password' => env('SP_PASSWORD'),
        'prefix' => env('SP_PREFIX'),
        'callback_url' => env('SP_CALLBACK'),
        'log_path' => env('SP_LOG_LOCATION', 'storage/logs'),
    ],

];
