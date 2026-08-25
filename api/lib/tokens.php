<?php

const API_TOKEN_PREFIX = 'cfts_';

function api_token_ttl_days(): int
{
    $days = (int) (api_config()['token_ttl_days'] ?? 30);

    return $days > 0 ? $days : 30;
}

function issue_api_token(mysqli $conn, int $user_id, ?string $device_name): array
{
    $plain = API_TOKEN_PREFIX . bin2hex(random_bytes(32));
    $hash = hash('sha256', $plain);
    $ttl = api_token_ttl_days();

    $stmt = $conn->prepare(
        "INSERT INTO api_tokens (user_id, token_hash, device_name, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))"
    );

    if (!$stmt) {
        error_log('API token insert prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'Could not create session token.');
    }

    $stmt->bind_param('issi', $user_id, $hash, $device_name, $ttl);

    if (!$stmt->execute()) {
        error_log('API token insert failed: ' . $stmt->error);
        api_fail(500, 'server_error', 'Could not create session token.');
    }

    $token_id = $stmt->insert_id;
    $stmt->close();

    $expires_at = null;
    $fetch = $conn->prepare("SELECT expires_at FROM api_tokens WHERE id = ? LIMIT 1");
    if ($fetch) {
        $fetch->bind_param('i', $token_id);
        $fetch->execute();
        $row = $fetch->get_result()->fetch_assoc();
        $expires_at = $row['expires_at'] ?? null;
        $fetch->close();
    }

    return [
        'token' => $plain,
        'expires_at' => $expires_at,
    ];
}

function resolve_api_bearer(mysqli $conn): ?array
{
    $plain = bearer_token();

    if ($plain === null || $plain === '') {
        return null;
    }

    $hash = hash('sha256', $plain);

    $stmt = $conn->prepare(
        "SELECT t.id AS token_id, u.*
         FROM api_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token_hash = ?
           AND t.revoked_at IS NULL
           AND t.expires_at > NOW()
         LIMIT 1"
    );

    if (!$stmt) {
        error_log('API token lookup prepare failed: ' . $conn->error);
        return null;
    }

    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }

    touch_api_token($conn, (int) $row['token_id']);

    return [
        'token_id' => (int) $row['token_id'],
        'user' => $row,
    ];
}

function touch_api_token(mysqli $conn, int $token_id): void
{
    $stmt = $conn->prepare(
        "UPDATE api_tokens
         SET last_used_at = NOW()
         WHERE id = ?
           AND (last_used_at IS NULL OR last_used_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))"
    );

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $token_id);
    $stmt->execute();
    $stmt->close();
}

function revoke_api_token(mysqli $conn, int $token_id): bool
{
    $stmt = $conn->prepare(
        "UPDATE api_tokens SET revoked_at = NOW() WHERE id = ? AND revoked_at IS NULL"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $token_id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function revoke_user_api_tokens(mysqli $conn, int $user_id, ?int $except_token_id = null): int
{
    if ($except_token_id !== null) {
        $stmt = $conn->prepare(
            "UPDATE api_tokens SET revoked_at = NOW()
             WHERE user_id = ? AND revoked_at IS NULL AND id != ?"
        );
        $stmt->bind_param('ii', $user_id, $except_token_id);
    } else {
        $stmt = $conn->prepare(
            "UPDATE api_tokens SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL"
        );
        $stmt->bind_param('i', $user_id);
    }

    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    return $affected;
}

function serialize_user(array $u): array
{
    $middle = trim((string) ($u['middlename'] ?? ''));
    $name = trim($u['firstname'] . ' ' . ($middle !== '' ? $middle . ' ' : '') . $u['lastname']);
    $pic = trim((string) ($u['profile_pic'] ?? ''));

    return [
        'id' => (int) $u['id'],
        'firstname' => (string) $u['firstname'],
        'middlename' => (string) ($u['middlename'] ?? ''),
        'lastname' => (string) $u['lastname'],
        'name' => $name,
        'email' => (string) $u['email'],
        'role' => (string) $u['role'],
        'birthday' => $u['birthday'] ?? null,
        'cellphone_no' => $u['cellphone_no'] ?? null,
        'address' => $u['address'] ?? null,
        'parents_name_guardian' => $u['parents_name_guardian'] ?? null,
        'parents_phone_no' => $u['parents_phone_no'] ?? null,
        'fb_messenger_account' => $u['fb_messenger_account'] ?? null,
        'profile_pic' => $pic !== '' ? $pic : null,
        'profile_pic_url' => $pic !== '' ? uploads_url('profiles/' . $pic) : null,
        'created_at' => $u['created_at'] ?? null,
    ];
}
