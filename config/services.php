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
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => '/auth/github/callback',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => '/auth/facebook/callback',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '/auth/google/callback',
    ],

    'google_translate' => [
        'key' => env('GOOGLE_TRANSLATE_API_KEY'),
    ],

    'pusher_beams' => [
        'instance_id' => env('PUSHER_BEAMS_INSTANCE_ID'),
        'secret_key' => env('PUSHER_BEAMS_SECRET_KEY'),
    ],

    // AI providers
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

    'pollinations' => [
        'api_key' => env('POLLINATIONS_API_KEY', ''),
    ],

    'cerebras' => [
        'api_key' => env('CEREBRAS_API_KEY', ''),
        'base_url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
    ],

    'mistral' => [
        'api_key' => env('MISTRAL_API_KEY', ''),
        'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],
    
    'nvidia' => [
        'api_key' => env('NVIDIA_API_KEY', ''),
        'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
    ],

    'iflow' => [
        'api_key' => env('IFLOW_API_KEY', ''),
        'base_url' => env('IFLOW_BASE_URL', 'https://apis.iflow.cn/v1'),
    ],
    'custom' => [
        'api_key' => env('CUSTOM_API_KEY', ''),
        'base_url' => env('CUSTOM_BASE_URL', 'https://9router.com/v1'),
    ],



];
