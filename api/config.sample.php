<?php

return [
    'base_url' => '',
    'cors_origins' => [
        'http://localhost:8081',
        'http://localhost:19006',
        'https://c-familia.online',
        'https://www.c-familia.online',
    ],
    'token_ttl_days' => 30,
    'rate_limit_window_minutes' => 15,
    'rate_limit_max_attempts' => 5,
    'rate_limit_ip_max_attempts' => 20,
];
