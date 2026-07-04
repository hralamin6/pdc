<?php

use Laravel\Ai\Provider;

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default' => 'mistral',
    'default_for_images' => 'pollinations',
    'default_for_audio' => 'openai',
    'default_for_transcription' => 'openai',
    'default_for_embeddings' => 'openai',
    'default_for_reranking' => 'cohere',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Below you may configure caching strategies for AI related operations
    | such as embedding generation. You are free to adjust these values
    | based on your application's available caching stores and needs.
    |
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Below are each of your AI providers defined for this application. Each
    | represents an AI provider and API key combination which can be used
    | to perform tasks like text, image, and audio creation via agents.
    |
    */

    'providers' => [
        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => env('GROQ_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key' => env('MISTRAL_API_KEY'),
        ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key' => env('OPENROUTER_API_KEY'),
        ],
        'pollinations' => [
            'driver' => 'pollinations',
            'key' => env('POLLINATIONS_API_KEY'),
            'url' => 'https://gen.pollinations.ai/v1',
        ],

        'cerebras' => [
            'driver' => 'groq',
            'key' => env('CEREBRAS_API_KEY'),
            'url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
        ],
        'iflow' => [
            'driver' => 'groq',
            'key' => env('IFLOW_API_KEY'),
            'url' => env('IFLOW_BASE_URL', 'https://apis.iflow.cn/v1'),
        ],
        'nvidia' => [
            'driver' => 'groq',
            'key' => env('NVIDIA_API_KEY'),
            'url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        ],
        'custom' => [
            'driver' => 'groq',
            'key' => env('CUSTOM_API_KEY'),
            'url' => env('CUSTOM_BASE_URL', 'https://9router.com/v1'),
        ],

    ],

];
