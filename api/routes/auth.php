<?php

function respond_with_session(mysqli $conn, int $user_id, ?string $device_name, int $status = 200): void
{
    $issued = issue_api_token($conn, $user_id, $device_name);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) {
        api_fail(500, 'server_error', 'Could not load account.');
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        api_fail(500, 'server_error', 'Could not load account.');
    }

    api_ok([
        'token' => $issued['token'],
        'token_type' => 'Bearer',
        'expires_at' => $issued['expires_at'],
        'user' => serialize_user($user),
    ], $status);
}

function auth_register(mysqli $conn, array $params, ?array $ctx): void
{
    $in = json_body();

    $errors = validate_fields($in, [
        'firstname'             => ['required', 'max:100'],
        'middlename'            => ['max:100'],
        'lastname'              => ['required', 'max:100'],
        'email'                 => ['required', 'email', 'max:100'],
        'password'              => ['required', 'min:8', 'max:255'],
        'password_confirmation' => [],
        'birthday'              => ['date'],
        'cellphone_no'          => ['max:30'],
        'address'               => ['max:2000'],
        'parents_name_guardian' => ['max:150'],
        'parents_phone_no'      => ['max:30'],
        'fb_messenger_account'  => ['max:150'],
    ]);

    $password = input_str($in, 'password');
    $confirm  = input_str($in, 'password_confirmation');

    if ($password !== $confirm) {
        $errors['password_confirmation'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $email = strtolower(input_str($in, 'email'));

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->bind_param('s', $email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', [
            'email' => 'Email is already registered!',
        ]);
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $role = 'student';

    $firstname             = input_str($in, 'firstname');
    $middlename            = input_str($in, 'middlename');
    $lastname              = input_str($in, 'lastname');
    $birthday              = input_str($in, 'birthday') ?: null;
    $cellphone_no          = input_str($in, 'cellphone_no') ?: null;
    $address               = input_str($in, 'address') ?: null;
    $parents_name_guardian = input_str($in, 'parents_name_guardian') ?: null;
    $parents_phone_no      = input_str($in, 'parents_phone_no') ?: null;
    $fb_messenger_account  = input_str($in, 'fb_messenger_account') ?: null;

    $insert = $conn->prepare(
        "INSERT INTO users (firstname, middlename, lastname, email, password, role,
            birthday, cellphone_no, address, parents_name_guardian, parents_phone_no, fb_messenger_account)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$insert) {
        error_log('API register prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'Registration failed. Please try again.');
    }

    $insert->bind_param(
        'ssssssssssss',
        $firstname,
        $middlename,
        $lastname,
        $email,
        $hashed,
        $role,
        $birthday,
        $cellphone_no,
        $address,
        $parents_name_guardian,
        $parents_phone_no,
        $fb_messenger_account
    );

    if (!$insert->execute()) {
        error_log('API register execute failed: ' . $insert->error);
        api_fail(500, 'server_error', 'Registration failed. Please try again.');
    }

    $new_user_id = $insert->insert_id;
    $insert->close();

    log_activity($conn, 'register', "New student account registered", [
        'user_id'     => $new_user_id,
        'user_role'   => 'student',
        'entity_type' => 'user',
        'entity_id'   => $new_user_id,
    ]);

    respond_with_session($conn, $new_user_id, api_device_name($in), 201);
}

function auth_login(mysqli $conn, array $params, ?array $ctx): void
{
    $in = json_body();

    $errors = validate_fields($in, [
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $email = strtolower(input_str($in, 'email'));
    $password = input_str($in, 'password');

    if (login_rate_limited($conn, $email)) {
        api_fail(429, 'too_many_attempts', 'Too many failed login attempts. Try again in about '
            . login_retry_after_minutes($conn, $email) . ' minute(s).');
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, (string) $user['password'])) {
        log_activity($conn, 'login.failed', 'Invalid credentials attempt for ' . $email);
        api_fail(401, 'invalid_credentials', 'Invalid email or password.');
    }

    log_activity($conn, 'login.success', 'Successful login', [
        'user_id'   => (int) $user['id'],
        'user_role' => $user['role'],
    ]);

    respond_with_session($conn, (int) $user['id'], api_device_name($in));
}

function auth_logout(mysqli $conn, array $params, ?array $ctx): void
{
    revoke_api_token($conn, (int) $ctx['token_id']);

    log_activity($conn, 'logout', 'Logged out', [
        'user_id'   => (int) $ctx['user']['id'],
        'user_role' => $ctx['user']['role'],
    ]);

    api_ok(['message' => 'Logged out successfully.']);
}

function auth_me(mysqli $conn, array $params, ?array $ctx): void
{
    api_ok(['user' => serialize_user($ctx['user'])]);
}

function api_device_name(array $in): ?string
{
    $device = input_str($in, 'device_name');

    return $device !== '' ? mb_substr($device, 0, 100) : null;
}
