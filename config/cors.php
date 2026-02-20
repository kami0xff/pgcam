<?php

return [
    'paths' => ['api/v1/*'],

    'allowed_methods' => ['GET', 'OPTIONS'],

    'allowed_origins' => array_filter(
        array_map('trim', explode(',', env('PRELANDER_ALLOWED_ORIGINS', '')))
    ) ?: ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
