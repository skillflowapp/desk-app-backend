<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    | Supported: 'openai', 'gemini', 'local'
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        'temperature' => 0.7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Configuration
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
        'temperature' => 0.7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Local LLM Configuration (Ollama, etc)
    |--------------------------------------------------------------------------
    */
    'local' => [
        'endpoint' => env('LOCAL_LLM_ENDPOINT', 'http://localhost:8000'),
        'model' => env('LOCAL_LLM_MODEL', 'llama2'),
        'temperature' => 0.7,
    ],
];
