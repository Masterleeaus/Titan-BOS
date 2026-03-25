<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'), // never hard-code
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    ],
    'safe_mode' => env('AI_SAFE_MODE', true),
];
