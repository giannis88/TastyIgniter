<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [
        '#^https?://.*\.tastyigniter\.local$#',
        '#^https?://.*\.localhost$#'
    ],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-XSRF-TOKEN'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true,
    'debug' => env('APP_DEBUG', false),
];
