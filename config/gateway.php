<?php

return [
    'routes' => [
        'api/v1/*' => 'https://jsonplaceholder.typicode.com/posts'
    ],
    'logging' => [
        'enabled' => true,
    ],
    'throttle' => [
        'enabled' => env('GATEWAY_THROTTLE_ENABLED', true),
        'limit' => env('GATEWAY_THROTTLE_LIMIT', 60),
        'period' => env('GATEWAY_THROTTLE_PERIOD', 60),
    ],
    'auth' => [
        'enabled' => env('GATEWAY_AUTH_ENABLED', false),
        'mock' => env('GATEWAY_AUTH_MOCK', true),
    ],
];
