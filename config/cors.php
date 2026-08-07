<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        env('EXAMFLOW_REQUEST_ID_HEADER', 'X-Request-ID'),
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
