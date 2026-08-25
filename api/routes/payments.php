<?php

function serialize_payment(array $p): array
{
    $receipt = trim((string) ($p['receipt'] ?? ''));

    return [
        'id'               => (int) $p['id'],
        'amount'           => $p['amount'] !== null ? (float) $p['amount'] : null,
        'reference_number' => $p['reference_number'] ?? null,
        'payment_method'   => $p['payment_method'] ?? null,
        'payment_type'     => $p['payment_type'] ?? null,
        'status'           => (string) ($p['status'] ?? 'pending'),
        'receipt'          => $receipt !== '' ? $receipt : null,
        'receipt_url'      => $receipt !== ''
            ? public_base_url() . '/api/payments/' . (int) $p['id'] . '/receipt'
            : null,
        'payment_date'     => $p['payment_date'] ?? null,
        'created_at'       => $p['created_at'] ?? null,
    ];
}

function fetch_own_payment(mysqli $conn, int $payment_id, int $user_id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param('ii', $payment_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function payments_list(mysqli $conn, array $params, ?array $ctx): void
{
    $user_id = (int) $ctx['user']['id'];

    $stmt = $conn->prepare(
        "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC, id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = serialize_payment($row);
    }
    $stmt->close();

    api_ok(['payments' => $payments]);
}

function payment_submit(mysqli $conn, array $params, ?array $ctx): void
{
    $in = $_POST;
    $errors = [];

    $amount = filter_var($in['amount'] ?? '', FILTER_VALIDATE_FLOAT);

    if ($amount === false || $amount <= 0 || $amount > 1000000) {
        $errors['amount'] = 'Enter a valid amount between ₱0.01 and ₱1,000,000.';
    }

    $ref_no = input_str($in, 'reference_number');
    if ($ref_no === '' || mb_strlen($ref_no) > 100) {
        $errors['reference_number'] = 'Enter a valid reference number (max 100 characters).';
    }

    $p_type = isset($in['payment_type']) && is_string($in['payment_type']) ? $in['payment_type'] : '';
    if (!in_array($p_type, ['full', 'installment', 'other'], true)) {
        $errors['payment_type'] = 'Select a valid payment category.';
    }

    $p_method = input_str($in, 'payment_method');
    if ($p_method === '' || mb_strlen($p_method) > 50) {
        $errors['payment_method'] = 'Enter a valid payment method (max 50 characters).';
    }

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    [$ok, $file_name, $upload_error] = store_uploaded_file(
        $_FILES['receipt'] ?? [],
        api_upload_dir('receipts'),
        ['jpg', 'jpeg', 'png', 'webp', 'pdf']
    );

    if (!$ok) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', [
            'receipt' => $upload_error,
        ]);
    }

    $user_id = (int) $ctx['user']['id'];

    $stmt = $conn->prepare(
        "INSERT INTO payments (user_id, amount, reference_number, payment_type, payment_method, receipt, status, created_at, payment_date)
         VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), CURDATE())"
    );

    if (!$stmt) {
        error_log('API payment prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }

    $stmt->bind_param('idssss', $user_id, $amount, $ref_no, $p_type, $p_method, $file_name);

    if (!$stmt->execute()) {
        error_log('API payment insert failed: ' . $stmt->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }

    $payment_id = $stmt->insert_id;
    $stmt->close();

    log_activity($conn, 'payment.submit', "Submitted ₱" . number_format((float) $amount, 2) . " via {$p_method} ({$p_type})", [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'payment',
        'entity_id'   => $payment_id,
    ]);

    api_ok([
        'payment' => serialize_payment(fetch_own_payment($conn, $payment_id, $user_id)),
        'message' => 'Payment submitted successfully and is pending verification.',
    ], 201);
}

function payment_cancel(mysqli $conn, array $params, ?array $ctx): void
{
    $user_id = (int) $ctx['user']['id'];
    $payment_id = (int) ($params[1] ?? 0);

    $payment = fetch_own_payment($conn, $payment_id, $user_id);

    if (!$payment) {
        api_fail(404, 'not_found', 'Payment not found.');
    }

    if (($payment['status'] ?? '') !== 'pending') {
        api_fail(409, 'invalid_state', 'Only pending payments can be cancelled.');
    }

    $update = $conn->prepare("UPDATE payments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $update->bind_param('ii', $payment_id, $user_id);

    if (!$update->execute()) {
        error_log('API payment cancel failed: ' . $update->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }
    $update->close();

    log_activity($conn, 'payment.cancel', null, [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'payment',
        'entity_id'   => $payment_id,
    ]);

    api_ok([
        'payment' => serialize_payment(fetch_own_payment($conn, $payment_id, $user_id)),
        'message' => 'Pending payment cancelled.',
    ]);
}

function payment_refund_request(mysqli $conn, array $params, ?array $ctx): void
{
    $user_id = (int) $ctx['user']['id'];
    $payment_id = (int) ($params[1] ?? 0);

    $payment = fetch_own_payment($conn, $payment_id, $user_id);

    if (!$payment) {
        api_fail(404, 'not_found', 'Payment not found.');
    }

    if (($payment['status'] ?? '') !== 'paid') {
        api_fail(409, 'invalid_state', 'Only verified (paid) payments can be refunded.');
    }

    $update = $conn->prepare("UPDATE payments SET status = 'refund_requested' WHERE id = ? AND user_id = ?");
    $update->bind_param('ii', $payment_id, $user_id);

    if (!$update->execute()) {
        error_log('API refund request failed: ' . $update->error);
        api_fail(500, 'server_error', 'System error. Please try again later.');
    }
    $update->close();

    log_activity($conn, 'payment.refund_request', null, [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'payment',
        'entity_id'   => $payment_id,
        'amount'      => $payment['amount'],
    ]);

    api_ok([
        'payment' => serialize_payment(fetch_own_payment($conn, $payment_id, $user_id)),
        'message' => 'Refund request submitted. An admin will review your refund request.',
    ]);
}

function payment_receipt(mysqli $conn, array $params, ?array $ctx): void
{
    $user_id = (int) $ctx['user']['id'];
    $payment_id = (int) ($params[1] ?? 0);

    $payment = fetch_own_payment($conn, $payment_id, $user_id);

    if (!$payment) {
        api_fail(404, 'not_found', 'Payment not found.');
    }

    $receipt = trim((string) ($payment['receipt'] ?? ''));

    if ($receipt === '') {
        api_fail(404, 'not_found', 'No receipt uploaded for this payment.');
    }

    api_stream_upload_file('receipts', $receipt);
}
