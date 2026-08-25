<?php

const DASHBOARD_PAYMENT_LIMIT = 5;
const DASHBOARD_ANNOUNCEMENT_LIMIT = 3;

function dashboard_get(mysqli $conn, array $params, ?array $ctx): void
{
    $user_id = (int) $ctx['user']['id'];

    $payload = enrollment_payload($conn, $user_id);

    $pay_stmt = $conn->prepare(
        "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT " . DASHBOARD_PAYMENT_LIMIT
    );
    $pay_stmt->bind_param('i', $user_id);
    $pay_stmt->execute();
    $result = $pay_stmt->get_result();

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = serialize_payment($row);
    }
    $pay_stmt->close();

    $ann_stmt = $conn->prepare(
        "SELECT * FROM announcements WHERE audience = 'Students' ORDER BY created_at DESC LIMIT " . DASHBOARD_ANNOUNCEMENT_LIMIT
    );
    $ann_stmt->execute();
    $ann_result = $ann_stmt->get_result();

    $announcements = [];
    while ($row = $ann_result->fetch_assoc()) {
        $announcements[] = serialize_announcement($row);
    }
    $ann_stmt->close();

    $payload['user'] = serialize_user($ctx['user']);
    $payload['recent_payments'] = $payments;
    $payload['announcements'] = $announcements;

    api_ok($payload);
}
