<?php

require __DIR__ . '/bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = api_request_path();

require_once API_ROOT . '/routes/auth.php';

$routes = [
    ['POST', '#^auth/register$#', 'auth_register', false],
    ['POST', '#^auth/login$#',    'auth_login',    false],
    ['POST', '#^auth/logout$#',   'auth_logout',   true],
    ['GET',  '#^auth/me$#',       'auth_me',       true],
];

$path_matched = false;

foreach ($routes as [$route_method, $pattern, $handler, $protected]) {
    if (!preg_match($pattern, $path, $params)) {
        continue;
    }

    $path_matched = true;

    if ($route_method !== $method) {
        continue;
    }

    if ($protected) {
        $ctx = resolve_api_bearer($conn);

        if ($ctx === null) {
            api_fail(401, 'unauthorized', 'Invalid or expired token.');
        }

        if (($ctx['user']['role'] ?? '') !== 'student') {
            api_fail(403, 'forbidden', 'This endpoint is for student accounts only.');
        }
    } else {
        $ctx = null;
    }

    try {
        $handler($conn, $params, $ctx);
    } catch (Throwable $e) {
        error_log('API handler error [' . $path . ']: ' . $e->getMessage());
        api_fail(500, 'server_error', 'Unexpected server error.');
    }

    exit;
}

if ($path_matched) {
    $allowed_methods = [];
    foreach ($routes as $route) {
        if (preg_match($route[1], $path)) {
            $allowed_methods[] = $route[0];
        }
    }
    header('Allow: ' . implode(', ', $allowed_methods));

    api_fail(405, 'method_not_allowed', 'HTTP method not allowed for this endpoint.');
}

api_fail(404, 'not_found', 'Endpoint not found.');
