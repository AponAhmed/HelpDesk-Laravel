<?php

return [
    'provider' => env('AI_PROVIDER', 'gemini'), // Default provider

    'settings' => [
        'openai' => [
            'key' => env('OPENAI_API_KEY', ''), // API key default
            'model' => env('OPENAI_MODEL', 'text-davinci-003'), // Default model
            'max_tokens' => env('OPENAI_MAX_TOKENS', 100), // Default max tokens
            'temperature' => env('OPENAI_TEMPERATURE', 0.7), // Default temperature
        ],
        'gemini' => [
            'key' => env('GEMINI_API_KEY', ''), // API key default
            'model' => env('GEMINI_MODEL', 'gemini-1.5'), // Default model
            'max_tokens' => env('GEMINI_MAX_TOKENS', 100), // Default max tokens
            'temperature' => env('GEMINI_TEMPERATURE', 0.7), // Default temperature
        ],
    ]
];
