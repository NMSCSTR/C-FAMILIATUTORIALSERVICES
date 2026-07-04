<?php

function activity_log_client_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }

    return $_SERVER['REMOTE_ADDR'] ?? null;
}

function activity_log_user_name($conn, $user_id) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return 'Unknown user';
    }

    $result = mysqli_query(
        $conn,
        "SELECT firstname, middlename, lastname, email FROM users WHERE id = $user_id LIMIT 1"
    );
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        return 'Unknown user';
    }

    $name = trim($row['firstname'] . ' ' . ($row['middlename'] ? $row['middlename'] . ' ' : '') . $row['lastname']);

    return $name !== '' ? $name : ($row['email'] ?: 'Unknown user');
}

function activity_log_enrollment_label($conn, $enrollment_id) {
    $enrollment_id = (int) $enrollment_id;
    if ($enrollment_id <= 0) {
        return null;
    }

    $result = mysqli_query(
        $conn,
        "SELECT e.program_type, e.batch, u.firstname, u.lastname
         FROM enrollments e
         JOIN users u ON e.user_id = u.id
         WHERE e.id = $enrollment_id
         LIMIT 1"
    );
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        return null;
    }

    $student = trim($row['firstname'] . ' ' . $row['lastname']);
    $parts = array_filter([
        $student,
        $row['program_type'],
        $row['batch'] ? 'Batch ' . $row['batch'] : null,
    ]);

    return implode(' — ', $parts);
}

function activity_log_entity_label($conn, $entity_type, $entity_id) {
    $entity_id = (int) $entity_id;
    if ($entity_id <= 0 || !$entity_type) {
        return null;
    }

    switch ($entity_type) {
        case 'user':
            return activity_log_user_name($conn, $entity_id);

        case 'enrollment':
            return activity_log_enrollment_label($conn, $entity_id);

        case 'post':
            $result = mysqli_query($conn, "SELECT title FROM posts WHERE id = $entity_id LIMIT 1");
            $row = mysqli_fetch_assoc($result);
            return $row['title'] ?? null;

        case 'announcement':
            $result = mysqli_query($conn, "SELECT title FROM announcements WHERE id = $entity_id LIMIT 1");
            $row = mysqli_fetch_assoc($result);
            return $row['title'] ?? null;

        case 'passer':
            $result = mysqli_query($conn, "SELECT name FROM passers WHERE id = $entity_id LIMIT 1");
            $row = mysqli_fetch_assoc($result);
            return $row['name'] ?? null;

        case 'payment':
            $result = mysqli_query(
                $conn,
                "SELECT p.amount, p.reference_number, u.firstname, u.lastname
                 FROM payments p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.id = $entity_id
                 LIMIT 1"
            );
            $row = mysqli_fetch_assoc($result);
            if (!$row) {
                return null;
            }
            $student = trim($row['firstname'] . ' ' . $row['lastname']);
            $amount = '₱' . number_format((float) $row['amount'], 2);
            return $student !== '' ? "$student ($amount)" : $amount;

        case 'gallery':
            $result = mysqli_query(
                $conn,
                "SELECT caption, image_path FROM gallery_images WHERE id = $entity_id LIMIT 1"
            );
            $row = mysqli_fetch_assoc($result);
            if (!$row) {
                return null;
            }
            return $row['caption'] ?: basename($row['image_path']);
    }

    return null;
}

function activity_log_build_description($conn, $action, $options = []) {
    $entity_type = $options['entity_type'] ?? null;
    $entity_id = $options['entity_id'] ?? null;
    $entity_label = $options['entity_label'] ?? null;

    if (!$entity_label && $entity_type && $entity_id) {
        $entity_label = activity_log_entity_label($conn, $entity_type, $entity_id);
    }

    switch ($action) {
        case 'enrollment.grades_update':
            $name = $entity_label ?: activity_log_user_name($conn, $entity_id ?? ($options['user_id'] ?? 0));
            return "Updated exam grades for $name";

        case 'enrollment.insurance_update':
            $label = $entity_label ?: activity_log_enrollment_label($conn, $entity_id);
            $status = !empty($options['insured']) ? 'enabled' : 'disabled';
            return $label ? "Set insurance to $status for $label" : "Set insurance to $status";

        case 'enrollment.approve':
            $label = $entity_label ?: activity_log_enrollment_label($conn, $entity_id);
            return $label ? "Approved enrollment for $label" : 'Approved enrollment';

        case 'payment.walkin':
            $payment_label = ($entity_type === 'payment' && $entity_id)
                ? activity_log_entity_label($conn, 'payment', $entity_id)
                : null;
            if ($payment_label) {
                return "Logged walk-in payment for $payment_label";
            }
            $name = activity_log_user_name($conn, $options['target_user_id'] ?? 0);
            $amount = isset($options['amount']) ? '₱' . number_format((float) $options['amount'], 2) : '';
            return trim("Logged walk-in payment of $amount for $name");

        case 'payment.verify':
            $label = $entity_label ?: activity_log_entity_label($conn, 'payment', $entity_id);
            return $label ? "Verified payment for $label" : 'Verified payment';

        case 'payment.refund_request':
            $label = $entity_label ?: activity_log_entity_label($conn, 'payment', $entity_id);
            return $label ? "Requested refund for $label" : 'Requested payment refund';

        case 'payment.refund':
            $label = $entity_label ?: activity_log_entity_label($conn, 'payment', $entity_id);
            $amount = isset($options['amount']) ? '₱' . number_format((float) $options['amount'], 2) : '';
            return $label ? "Processed refund of $amount for $label" : "Processed refund of $amount";

        case 'payment.cancel':
            $label = $entity_label ?: activity_log_entity_label($conn, 'payment', $entity_id);
            return $label ? "Cancelled pending payment for $label" : 'Cancelled pending payment';

        case 'post.delete':
            return $entity_label ? "Deleted learning resource: $entity_label" : 'Deleted learning resource';

        case 'announcement.delete':
            return $entity_label ? "Deleted announcement: $entity_label" : 'Deleted announcement';

        case 'passer.delete':
            return $entity_label ? "Deleted passer: $entity_label" : 'Deleted passer';

        case 'gallery.delete':
            return $entity_label ? "Deleted gallery image: $entity_label" : 'Deleted gallery image';
    }

    return null;
}

function activity_log_display_description($conn, $log) {
    $rebuilt = activity_log_build_description($conn, $log['action'], [
        'entity_type' => $log['entity_type'] ?? null,
        'entity_id' => $log['entity_id'] ?? null,
        'user_id' => $log['user_id'] ?? null,
    ]);

    if ($rebuilt !== null) {
        return $rebuilt;
    }

    $description = trim($log['description'] ?? '');
    return $description !== '' ? $description : '—';
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
        'payment.refund_request' => 'Requested refund',
        'payment.refund' => 'Processed refund',
        'payment.cancel' => 'Cancelled payment',
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

    if ($description === null) {
        $description = activity_log_build_description($conn, $action, array_merge($options, [
            'user_id' => $user_id,
        ]));
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
