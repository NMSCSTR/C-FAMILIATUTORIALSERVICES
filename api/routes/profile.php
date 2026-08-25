<?php

function profile_get(mysqli $conn, array $params, ?array $ctx): void
{
    api_ok(['user' => serialize_user($ctx['user'])]);
}

function profile_update(mysqli $conn, array $params, ?array $ctx): void
{
    $in = $_POST;

    $errors = validate_fields($in, [
        'firstname'             => ['required', 'max:100'],
        'middlename'            => ['max:100'],
        'lastname'              => ['required', 'max:100'],
        'birthday'              => ['date'],
        'cellphone_no'          => ['max:30'],
        'address'               => ['max:2000'],
        'parents_name_guardian' => ['max:150'],
        'parents_phone_no'      => ['max:30'],
        'fb_messenger_account'  => ['max:150'],
    ]);

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $user_id = (int) $ctx['user']['id'];

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        [$ok, $filename, $upload_error] = store_uploaded_file(
            $_FILES['profile_pic'],
            api_upload_dir('profiles'),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );

        if (!$ok) {
            api_fail(422, 'validation_error', 'Please check the highlighted fields.', [
                'profile_pic' => $upload_error,
            ]);
        }

        $pic_stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $pic_stmt->bind_param('si', $filename, $user_id);
        if (!$pic_stmt->execute()) {
            error_log('API avatar update failed: ' . $pic_stmt->error);
            api_fail(500, 'server_error', 'Profile update failed. Please try again.');
        }
        $pic_stmt->close();
    }

    $firstname             = input_str($in, 'firstname');
    $middlename            = input_str($in, 'middlename');
    $lastname              = input_str($in, 'lastname');
    $birthday              = input_str($in, 'birthday') ?: null;
    $cellphone_no          = input_str($in, 'cellphone_no') ?: null;
    $address               = input_str($in, 'address') ?: null;
    $parents_name_guardian = input_str($in, 'parents_name_guardian') ?: null;
    $parents_phone_no      = input_str($in, 'parents_phone_no') ?: null;
    $fb_messenger_account  = input_str($in, 'fb_messenger_account') ?: null;

    $stmt = $conn->prepare(
        "UPDATE users SET
            firstname = ?,
            middlename = ?,
            lastname = ?,
            birthday = ?,
            cellphone_no = ?,
            address = ?,
            parents_name_guardian = ?,
            parents_phone_no = ?,
            fb_messenger_account = ?
         WHERE id = ?"
    );

    if (!$stmt) {
        error_log('API profile prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'Profile update failed. Please try again.');
    }

    $stmt->bind_param(
        'sssssssssi',
        $firstname,
        $middlename,
        $lastname,
        $birthday,
        $cellphone_no,
        $address,
        $parents_name_guardian,
        $parents_phone_no,
        $fb_messenger_account,
        $user_id
    );

    if (!$stmt->execute()) {
        error_log('API profile update failed: ' . $stmt->error);
        api_fail(500, 'server_error', 'Profile update failed. Please try again.');
    }
    $stmt->close();

    log_activity($conn, 'profile.update', 'Updated profile information', [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'user',
        'entity_id'   => $user_id,
    ]);

    $fresh = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $fresh->bind_param('i', $user_id);
    $fresh->execute();
    $user = $fresh->get_result()->fetch_assoc();
    $fresh->close();

    api_ok(['user' => serialize_user($user), 'message' => 'Profile updated successfully!']);
}

function profile_password(mysqli $conn, array $params, ?array $ctx): void
{
    $in = json_body();

    $errors = validate_fields($in, [
        'current_password'    => ['required'],
        'new_password'        => ['required', 'min:8', 'max:255'],
        'confirm_new_password' => ['required'],
    ]);

    $new     = input_str($in, 'new_password');
    $confirm = input_str($in, 'confirm_new_password');

    if ($new !== $confirm && !isset($errors['new_password'])) {
        $errors['confirm_new_password'] = 'New passwords do not match.';
    }

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $user_id = (int) $ctx['user']['id'];
    $current = input_str($in, 'current_password');

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $hash = (string) ($stmt->get_result()->fetch_assoc()['password'] ?? '');
    $stmt->close();

    if ($hash === '' || !password_verify($current, $hash)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', [
            'current_password' => 'Your current password is incorrect.',
        ]);
    }

    $new_hash = password_hash($new, PASSWORD_DEFAULT);

    $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $up->bind_param('si', $new_hash, $user_id);

    if (!$up->execute()) {
        error_log('API password change failed: ' . $up->error);
        api_fail(500, 'server_error', 'Password change failed. Please try again.');
    }
    $up->close();

    revoke_user_api_tokens($conn, $user_id, (int) $ctx['token_id']);

    log_activity($conn, 'password.change', null, [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'user',
        'entity_id'   => $user_id,
    ]);

    api_ok(['message' => 'Password updated successfully!']);
}
