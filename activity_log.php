<?php

function activity_log_client_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }

    return $_SERVER['REMOTE_ADDR'] ?? null;
}

function activity_action_label($action) {
    $labels = [
        'login.success' => 'Logged in',
        'login.failed' => 'Failed login',
        'logout' => 'Logged out',
        'register' => 'Registered account',
        'enrollment.submit' => 'Submitted enrollment',
        'enrollment.approve' => 'Approved enrollment',
        'enrollment.grades_update' => 'Updated exam grades',
        'enrollment.insurance_update' => 'Updated insurance status',
        'payment.submit' => 'Submitted payment',
        'payment.walkin' => 'Logged walk-in payment',
        'payment.verify' => 'Verified payment',
        'profile.update' => 'Updated profile',
        'announcement.create' => 'Posted announcement',
        'announcement.delete' => 'Deleted announcement',
        'post.create' => 'Created learning resource',
        'post.delete' => 'Deleted learning resource',
        'passer.create' => 'Added passer',
        'passer.delete' => 'Deleted passer',
        'gallery.create' => 'Added gallery images',
        'gallery.delete' => 'Deleted gallery image',
        'gallery.delete_caption' => 'Deleted gallery caption group',
        'testimonial.submit' => 'Submitted testimonial',
    ];

    return $labels[$action] ?? ucwords(str_replace(['.', '_'], ' ', $action));
}

function log_activity($conn, $action, $description = null, $options = []) {
    static $table_exists = null;

    if ($table_exists === null) {
        $check = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
        $table_exists = $check && mysqli_num_rows($check) > 0;
    }

    if (!$table_exists) {
        return false;
    }

    $user_id = array_key_exists('user_id', $options)
        ? $options['user_id']
        : ($_SESSION['user_id'] ?? null);
    $user_role = $options['user_role'] ?? ($_SESSION['role'] ?? null);
    $entity_type = $options['entity_type'] ?? null;
    $entity_id = array_key_exists('entity_id', $options) ? $options['entity_id'] : null;
    $ip_address = $options['ip_address'] ?? activity_log_client_ip();

    if ($user_id !== null) {
        $user_id = (int) $user_id;
    }
    if ($entity_id !== null) {
        $entity_id = (int) $entity_id;
    }

    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, user_role, action, entity_type, entity_id, description, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "isssiss",
        $user_id,
        $user_role,
        $action,
        $entity_type,
        $entity_id,
        $description,
        $ip_address
    );

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
