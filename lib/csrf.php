<?php

require_once __DIR__ . '/session.php';

function csrf_token(): string
{
    secure_session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
        . '">';
}

function csrf_verify(): void
{
    secure_session_start();

    $token = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || $token === '' || $known === '' || !hash_equals($known, $token)) {
        http_response_code(419);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

function csrf_validate_request_token(?string $token): bool
{
    secure_session_start();

    $known = $_SESSION['csrf_token'] ?? '';

    return is_string($token) && $token !== '' && $known !== '' && hash_equals($known, $token);
}
