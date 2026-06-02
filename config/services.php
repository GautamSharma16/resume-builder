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

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'fallback_models' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'GEMINI_FALLBACK_MODELS',
            'gemini-2.5-flash,gemini-2.0-flash'
        ))))),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 8192),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.2),
    ],

    'affinda' => [
        'key' => env('AFFINDA_API_KEY'),
        'base_url' => env('AFFINDA_BASE_URL', 'https://api.affinda.com/v3'),
        'organization' => env('AFFINDA_ORGANIZATION'),
        'workspace' => env('AFFINDA_WORKSPACE'),
        'document_type' => env('AFFINDA_DOCUMENT_TYPE'),
        'timeout' => (int) env('AFFINDA_TIMEOUT', 120),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY_ID'),
        'secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'download_amount' => (int) env('RAZORPAY_DOWNLOAD_AMOUNT', 4900),
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'tinymce' => [
        'key' => env('TINYMCE_API_KEY'),
    ],
    'puppeteer' => [
    'executable_path' => env('PUPPETEER_EXECUTABLE_PATH', ''),
],

];
