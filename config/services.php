<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'billing' => [
        'base_uri' => env('BILLING_BASE_URI'),
        'authorization_token' => env('BILLING_AUTHORIZATION_TOKEN'),
        'partnership_id' => env('BILLING_PARTNERSHIP_ID'),
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', '158732244393-43jq11h2quvqu2g0dl9371fjk0d0rj3t.apps.googleusercontent.com'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET','fDenYVQ3NpJOReIcmS90s1qO'),
        'redirect' => 'https://gestordepartes.net/login/google/callback',
    ], 

    'eventsDocument' => [
        'base_uri' => env('EVENTS_DOCUMENTS_BASE_URI'),
        'token' => env('EVENTS_DOCUMENTS_TOKEN'),
        'ambiente' => env('EVENTS_DOCUMENTS_AMBIENTE')
    ],

];
