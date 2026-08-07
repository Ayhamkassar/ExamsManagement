<?php

return [

    'tenant_header' => env('EXAMFLOW_TENANT_HEADER', 'X-Tenant-ID'),

    'frontend_url' => env('FRONTEND_URL', env('APP_URL', 'http://localhost:8000')),

    'pagination' => [
        'default_per_page' => (int) env('EXAMFLOW_DEFAULT_PER_PAGE', 25),
        'max_per_page' => (int) env('EXAMFLOW_MAX_PER_PAGE', 100),
    ],

    'rate_limit' => [
        'api' => (int) env('EXAMFLOW_API_RATE_LIMIT', 60),
    ],

    'request_id_header' => env('EXAMFLOW_REQUEST_ID_HEADER', 'X-Request-ID'),

    'storage' => [
        'tenant_prefix' => 'tenants',
    ],

    'cache' => [
        'tenant_prefix' => 'tenant',
    ],

    'auth' => [
        'token_name' => env('EXAMFLOW_TOKEN_NAME', 'examflow-session'),
        // Comma-separated Sanctum token abilities granted by default.
        'token_abilities' => explode(',', (string) env('EXAMFLOW_TOKEN_ABILITIES', '*')),
        'token_expiration_minutes' => (int) env('EXAMFLOW_TOKEN_EXPIRATION_MINUTES', 0),

        'password' => [
            'min_length' => (int) env('EXAMFLOW_PASSWORD_MIN_LENGTH', 12),
            // Reset/change tokens revoked when changed.
            'revoke_all_on_change' => (bool) env('EXAMFLOW_REVOKE_ALL_ON_CHANGE', false),
        ],

        'rate_limit' => [
            'login' => (int) env('EXAMFLOW_LOGIN_RATE_LIMIT', 5),
            'password' => (int) env('EXAMFLOW_PASSWORD_RATE_LIMIT', 3),
            'verification' => (int) env('EXAMFLOW_VERIFICATION_RATE_LIMIT', 3),
            'register' => (int) env('EXAMFLOW_REGISTER_RATE_LIMIT', 10),
        ],
    ],

];
