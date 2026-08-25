<?php

define('API_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__));

ob_start();
require_once APP_ROOT . '/db.php';
ob_end_clean();

if (!isset($conn) || !($conn instanceof mysqli)) {
    api_fail(500, 'server_error', 'Database connection unavailable.');
}

require_once API_ROOT . '/lib/http.php';
require_once APP_ROOT . '/lib/uploads.php';
require_once APP_ROOT . '/lib/programs.php';
require_once API_ROOT . '/lib/tokens.php';
require_once API_ROOT . '/lib/files.php';

cors_headers();

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
