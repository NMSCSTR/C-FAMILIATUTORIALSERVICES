<?php

require __DIR__ . '/bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = api_request_path();

require_once API_ROOT . '/routes/auth.php';
require_once API_ROOT . '/routes/profile.php';
require_once API_ROOT . '/routes/enrollment.php';
require_once API_ROOT . '/routes/payments.php';
require_once API_ROOT . '/routes/dashboard.php';
require_once API_ROOT . '/routes/content.php';

$routes = [
    ['POST', '#^auth/register$#', 'auth_register', false],
    ['POST', '#^auth/login$#',    'auth_login',    false],
    ['POST', '#^auth/logout$#',   'auth_logout',   true],
    ['GET',  '#^auth/me$#',       'auth_me',       true],

    ['GET',  '#^profile$#',          'profile_get',      true],
    ['POST', '#^profile$#',          'profile_update',   true],
    ['POST', '#^profile/password$#', 'profile_password', true],

    ['GET',  '#^enrollment/options$#', 'enrollment_options', true],
    ['GET',  '#^enrollment$#',         'enrollment_get',     true],
    ['POST', '#^enrollment$#',         'enrollment_create',  true],

    ['GET',  '#^payments$#',                       'payments_list',          true],
    ['POST', '#^payments$#',                       'payment_submit',         true],
    ['POST', '#^payments/(\d+)/cancel$#',          'payment_cancel',         true],
    ['POST', '#^payments/(\d+)/refund-request$#',  'payment_refund_request', true],
    ['GET',  '#^payments/(\d+)/receipt$#',         'payment_receipt',        true],

    ['GET',  '#^dashboard$#',        'dashboard_get',       true],

    ['GET',  '#^posts$#',            'posts_list',          true],
    ['GET',  '#^posts/(\d+)/file$#', 'post_file',           true],

    ['GET',  '#^announcements$#',    'announcements_list',  true],
    ['GET',  '#^passers$#',          'passers_list',        true],
    ['GET',  '#^gallery$#',          'gallery_list',        true],
    ['GET',  '#^testimonials$#',     'testimonials_list',   true],
    ['POST', '#^testimonials$#',     'testimonial_create',  true],
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
