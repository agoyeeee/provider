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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sakti_sso' => [
        'client_id' => env('SAKTI_SSO_CLIENT_ID', '7bb718d2-abea-4f3e-865b-3b87b0be59b8'),
        'client_secret' => env('SAKTI_SSO_CLIENT_SECRET', 'VEoyEM5UCOy3ZpdCf8X3kG6piRTf7etKM68FLVkg'),
        'authorize_url' => env('SAKTI_SSO_AUTHORIZE_URL', 'https://sakti.karanganyarkab.go.id/login/oauth/authorize'),
        'token_url' => env('SAKTI_SSO_TOKEN_URL', 'https://sakti.karanganyarkab.go.id/login/oauth/access_token'),
        'api_url_base' => env('SAKTI_SSO_API_URL_BASE', 'https://sakti.karanganyarkab.go.id/api/'),
        'redirect_uri' => env('SAKTI_SSO_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/auth/sso/callback'),
    ],

];
