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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // ─── SSO Auth central SiteV26 (Kairotaku/Auth) ────────────────────────────
    // P0-02 — Clé manquante : CentralAuthService levait TransportException systématiquement.
    // Ces valeurs sont lues depuis .env ; ne jamais hardcoder d'URL de production ici.
    'auth' => [
        'verify_url' => env('AUTH_VERIFY_URL', 'http://auth-app/api/auth/verify'),
        'api_url' => env('AUTH_API_URL', 'http://auth-app'),
        'login_url' => env('AUTH_LOGIN_URL', 'http://localhost:5176/login'),
        // slug transmis en header X-App-ID — identifiant Ultreiataku côté Auth central
        'app_id' => env('AUTH_APP_ID', env('APP_PANEL', 'ultreiataku')),
        // URL d'échange code éphémère → token (TKN-P0-2)
        'exchange_url' => env('AUTH_EXCHANGE_URL', ''),
    ],

];
