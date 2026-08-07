<?php

return [

    'tenant_header' => env('EXAMFLOW_TENANT_HEADER', 'X-Tenant-ID'),

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

];
