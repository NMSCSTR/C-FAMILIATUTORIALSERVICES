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
    // Set true when the server cannot honor .htaccess rewrites (e.g. no sudo to enable mod_rewrite).
    // Routes are then served as /api/index.php?route=auth/login and gated file URLs match.
    'force_query_urls' => false,
];
