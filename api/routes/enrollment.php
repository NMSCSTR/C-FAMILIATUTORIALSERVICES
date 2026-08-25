<?php

function serialize_enrollment(array $e): array
{
    return [
        'id'              => (int) $e['id'],
        'program_type'    => (string) $e['program_type'],
        'status'          => (string) $e['status'],
        'batch'           => $e['batch'] ?? null,
        'enrolled_at'     => $e['enrolled_at'] ?? null,
        'enrollment_date' => $e['enrollment_date'] ?? null,
        'insured'         => (bool) ($e['insured'] ?? 0),
        'total_fee'       => $e['total_fee'] !== null ? (float) $e['total_fee'] : null,
        'created_at'      => $e['created_at'] ?? null,
    ];
}

function serialize_exam_result(?array $r): ?array
{
    if (!$r) {
        return null;
    }

    return [
        'diagnostic_exam' => $r['diagnostic_exam'] !== null ? (int) $r['diagnostic_exam'] : null,
        'preboard_exam'   => $r['preboard_exam'] !== null ? (int) $r['preboard_exam'] : null,
        'compre_exam'     => $r['compre_exam'] !== null ? (int) $r['compre_exam'] : null,
    ];
}

function enrollment_financials(mysqli $conn, int $user_id, ?array $enrollment): array
{
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM payments
         WHERE user_id = ? AND status = 'paid'"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_paid = (float) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    $balance = null;
    if ($enrollment && $enrollment['total_fee'] !== null) {
        $balance = (float) $enrollment['total_fee'] - $total_paid;
    }

    return [$total_paid, $balance];
}

function enrollment_options(mysqli $conn, array $params, ?array $ctx): void
{
    require_once APP_ROOT . '/lib/programs.php';

    $programs = [];
    foreach (cfts_programs() as $name => $details) {
        $programs[] = [
            'name' => $name,
            'fee'  => (float) $details['fee'],
            'desc' => $details['desc'] ?? '',
            'icon' => $details['icon'] ?? '',
        ];
    }

    $locations = [];
    foreach (cfts_locations() as $value => $label) {
        $locations[] = ['value' => $value, 'label' => $label];
    }

    api_ok([
        'programs'  => $programs,
        'batches'   => cfts_batch_options(),
        'locations' => $locations,
    ]);
}

function enrollment_payload(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare(
        "SELECT * FROM enrollments
         WHERE user_id = ? AND status != 'completed'
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $enrollment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    [$total_paid, $balance] = enrollment_financials($conn, $user_id, $enrollment);

    $exam_stmt = $conn->prepare("SELECT * FROM exam_result WHERE user_id = ? LIMIT 1");
    $exam_stmt->bind_param('i', $user_id);
    $exam_stmt->execute();
    $exam = $exam_stmt->get_result()->fetch_assoc();
    $exam_stmt->close();

    return [
        'enrollment'  => $enrollment ? serialize_enrollment($enrollment) : null,
        'total_paid'  => $total_paid,
        'balance'     => $balance,
        'exam_result' => serialize_exam_result($exam),
    ];
}

function enrollment_get(mysqli $conn, array $params, ?array $ctx): void
{
    api_ok(enrollment_payload($conn, (int) $ctx['user']['id']));
}

function enrollment_create(mysqli $conn, array $params, ?array $ctx): void
{
    require_once APP_ROOT . '/lib/programs.php';

    $in = json_body();

    $program  = input_str($in, 'program_type');
    $batch    = input_str($in, 'batch');
    $location = input_str($in, 'enrolled_at');

    $programs = cfts_programs();
    $batches = cfts_batch_options();
    $locations = cfts_locations();

    $errors = [];

    if (!isset($programs[$program])) {
        $errors['program_type'] = 'Please select a valid program.';
    }
    if ($batch === '' || mb_strlen($batch) > 50 || !in_array($batch, $batches, true)) {
        $errors['batch'] = 'Please select a valid batch.';
    }
    if ($location === '' || mb_strlen($location) > 100 || !isset($locations[$location])) {
        $errors['enrolled_at'] = 'Please select a valid review location.';
    }

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $user_id = (int) $ctx['user']['id'];

    $check = $conn->prepare(
        "SELECT id FROM enrollments
         WHERE user_id = ? AND program_type = ? AND status != 'completed'
         LIMIT 1"
    );
    $check->bind_param('is', $user_id, $program);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        api_fail(409, 'duplicate_enrollment', "You already have an active application for the {$program}.");
    }

    $fee = $programs[$program]['fee'];

    $insert = $conn->prepare(
        "INSERT INTO enrollments (user_id, program_type, batch, total_fee, status, enrollment_date, enrolled_at)
         VALUES (?, ?, ?, ?, 'pending', CURDATE(), ?)"
    );

    if (!$insert) {
        error_log('API enrollment prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }

    $insert->bind_param('issds', $user_id, $program, $batch, $fee, $location);

    if (!$insert->execute()) {
        error_log('API enrollment insert failed: ' . $insert->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }

    $enrollment_id = $insert->insert_id;
    $insert->close();

    log_activity($conn, 'enrollment.submit', null, [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'enrollment',
        'entity_id'   => $enrollment_id,
    ]);

    $fetch = $conn->prepare("SELECT * FROM enrollments WHERE id = ? LIMIT 1");
    $fetch->bind_param('i', $enrollment_id);
    $fetch->execute();
    $row = $fetch->get_result()->fetch_assoc();
    $fetch->close();

    [$total_paid, $balance] = enrollment_financials($conn, $user_id, $row);

    api_ok([
        'enrollment'  => serialize_enrollment($row),
        'total_paid'  => $total_paid,
        'balance'     => $balance,
        'message'     => "Your application for {$program} has been submitted successfully.",
    ], 201);
}
