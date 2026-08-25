<?php

function rate_limit_settings(): array
{
    $window = (int) (api_config()['rate_limit_window_minutes'] ?? 15);
    $maxEmail = (int) (api_config()['rate_limit_max_attempts'] ?? 5);
    $maxIp = (int) (api_config()['rate_limit_ip_max_attempts'] ?? 20);

    return [
        'window_minutes' => $window > 0 ? $window : 15,
        'max_email_attempts' => $maxEmail,
        'max_ip_attempts' => $maxIp > 0 ? $maxIp : 20,
    ];
}

function failed_login_count(mysqli $conn, int $window_minutes, ?string $email, ?string $ip): int
{
    $conditions = [];
    $types = 'i';
    $params = [$window_minutes];

    if ($email !== null) {
        $needle = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], strtolower($email)) . '%';
        $conditions[] = 'LOWER(description) LIKE ?';
        $types .= 's';
        $params[] = $needle;
    }

    if ($ip !== null && $ip !== '') {
        $conditions[] = 'ip_address = ?';
        $types .= 's';
        $params[] = $ip;
    }

    if (empty($conditions)) {
        return 0;
    }

    $where = implode(' OR ', $conditions);

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS attempts
         FROM activity_logs
         WHERE action = 'login.failed'
           AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
           AND ($where)"
    );

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $attempts = (int) ($stmt->get_result()->fetch_assoc()['attempts'] ?? 0);
    $stmt->close();

    return $attempts;
}

function login_rate_limited(mysqli $conn, string $email): bool
{
    $settings = rate_limit_settings();
    $ip = (string) activity_log_client_ip();

    if ($settings['max_email_attempts'] > 0
        && failed_login_count($conn, $settings['window_minutes'], $email, null) >= $settings['max_email_attempts']) {
        return true;
    }

    if ($settings['max_ip_attempts'] > 0
        && failed_login_count($conn, $settings['window_minutes'], null, $ip) >= $settings['max_ip_attempts']) {
        return true;
    }

    return false;
}

function login_retry_after_minutes(mysqli $conn, string $email): int
{
    $settings = rate_limit_settings();

    $emailLimited = $settings['max_email_attempts'] > 0
        && failed_login_count($conn, $settings['window_minutes'], $email, null) >= $settings['max_email_attempts'];

    $scope = $emailLimited ? 'LOWER(description) LIKE ?' : 'ip_address = ?';

    if (!$emailLimited) {
        $needle = (string) activity_log_client_ip();
    } else {
        $needle = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], strtolower($email)) . '%';
    }

    $stmt = $conn->prepare(
        "SELECT GREATEST(1, TIMESTAMPDIFF(MINUTE, NOW(), (
             SELECT DATE_ADD(created_at, INTERVAL ? MINUTE)
             FROM activity_logs
             WHERE action = 'login.failed' AND $scope
             ORDER BY created_at DESC
             LIMIT 1
         ))) AS minutes_left"
    );

    if (!$stmt) {
        return 1;
    }

    $window = $settings['window_minutes'];
    $stmt->bind_param('is', $window, $needle);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return max(1, (int) ($row['minutes_left'] ?? 1));
}
