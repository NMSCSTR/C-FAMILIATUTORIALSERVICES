<?php

function api_config(): array
{
    static $config = null;

    if ($config === null) {
        $file = __DIR__ . '/../config.php';
        $config = is_file($file) ? (require $file) : [];
        if (!is_array($config)) {
            $config = [];
        }
    }

    return $config;
}

function api_default_cors_origins(): array
{
    return [
        'http://localhost:8081',
        'http://localhost:19006',
        'https://c-familia.online',
        'https://www.c-familia.online',
    ];
}

function cors_headers(): void
{
    $allowed = api_config()['cors_origins'] ?? null;
    if (!is_array($allowed)) {
        $allowed = api_default_cors_origins();
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '' && in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin', false);
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
}

function json_out(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_ok(array $data = [], int $status = 200): void
{
    json_out($status, ['success' => true, 'data' => $data]);
}

function api_fail(int $status, string $code, string $message, array $fields = []): void
{
    $error = ['code' => $code, 'message' => $message];
    if (!empty($fields)) {
        $error['fields'] = $fields;
    }
    json_out($status, ['success' => false, 'error' => $error]);
}

function json_body(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        api_fail(422, 'validation_error', 'Request body must be a valid JSON object.');
    }

    return $decoded;
}

function input_str(array $data, string $key): string
{
    $value = $data[$key] ?? '';

    return is_scalar($value) ? trim((string) $value) : '';
}

function query_int(string $key, int $default, int $min, int $max): int
{
    $raw = $_GET[$key] ?? null;

    if ($raw === null || !preg_match('/^\d+$/', (string) $raw)) {
        return $default;
    }

    $value = (int) $raw;

    return max($min, min($max, $value));
}

function validate_fields(array $data, array $rules): array
{
    $errors = [];

    foreach ($rules as $field => $ruleSet) {
        $value = input_str($data, $field);

        foreach ($ruleSet as $rule) {
            if ($rule === 'required') {
                if ($value === '') {
                    $errors[$field] = 'This field is required.';
                    break;
                }
                continue;
            }

            if ($value === '') {
                continue;
            }

            if ($rule === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Enter a valid email address.';
                    break;
                }
            } elseif (strpos($rule, 'min:') === 0) {
                if (mb_strlen($value) < (int) substr($rule, 4)) {
                    $errors[$field] = 'Must be at least ' . substr($rule, 4) . ' characters.';
                    break;
                }
            } elseif (strpos($rule, 'max:') === 0) {
                if (mb_strlen($value) > (int) substr($rule, 4)) {
                    $errors[$field] = 'Must not exceed ' . substr($rule, 4) . ' characters.';
                    break;
                }
            } elseif ($rule === 'date') {
                $date = DateTime::createFromFormat('Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $errors[$field] = 'Use the YYYY-MM-DD format.';
                    break;
                }
            }
        }
    }

    return $errors;
}

function bearer_token(): ?string
{
    $header = '';

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $header = (string) $value;
                break;
            }
        }
    }

    if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $header, $m)) {
        return $m[1];
    }

    return null;
}

function api_request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $uri = is_string($uri) && $uri !== '' ? $uri : '/';

    $pos = strrpos($uri, '/api/');
    if ($pos !== false) {
        $uri = '/' . substr($uri, $pos + 5);
    } elseif (preg_match('#/api$#i', $uri)) {
        $uri = '/';
    }

    return trim($uri, '/');
}

function public_base_url(): string
{
    $configured = trim((string) (api_config()['base_url'] ?? ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = rtrim($dir, '/');
    $dir = preg_replace('#/api$#', '', $dir);

    return $scheme . '://' . $host . $dir;
}

function uploads_url(string $relative): string
{
    return public_base_url() . '/uploads/' . ltrim($relative, '/');
}
