<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script may only be run from the command line.\n");
}

$config = require dirname(__DIR__) . '/config.php';

$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname']);

if ($conn->connect_error) {
    fwrite(STDERR, "CONNECT FAILED: " . $conn->connect_error . PHP_EOL);
    exit(1);
}

$failures = 0;

function table_exists(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function column_exists(mysqli $conn, string $table, string $column): string
{
    $stmt = $conn->prepare(
        "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row !== null ? (string) $row['COLUMN_TYPE'] : '';
}

function run_step(string $label, callable $fn): void
{
    global $failures;

    try {
        $result = $fn();

        if ($result === 'skip') {
            echo "SKIP  $label (already applied)\n";
        } else {
            echo "OK    $label\n";
        }
    } catch (Throwable $e) {
        $failures++;
        echo "FAIL  $label -> " . $e->getMessage() . "\n";
    }
}

echo "== Production schema sync ==\n";
echo "DB: {$config['dbname']}\n\n";

run_step('create api_tokens', function () use ($conn) {
    if (table_exists($conn, 'api_tokens')) {
        return 'skip';
    }

    $sql = file_get_contents(__DIR__ . '/add_api_tokens.sql');

    if (!$conn->multi_query($sql)) {
        throw new RuntimeException($conn->error);
    }

    while ($conn->more_results() && $conn->next_result()) {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    }

    return 'done';
});

$user_columns = [
    ['birthday', "`birthday` date DEFAULT NULL AFTER `profile_pic`"],
    ['cellphone_no', "`cellphone_no` varchar(30) DEFAULT NULL AFTER `birthday`"],
    ['address', "`address` text DEFAULT NULL AFTER `cellphone_no`"],
    ['parents_name_guardian', "`parents_name_guardian` varchar(150) DEFAULT NULL AFTER `address`"],
    ['parents_phone_no', "`parents_phone_no` varchar(30) DEFAULT NULL AFTER `parents_name_guardian`"],
    ['fb_messenger_account', "`fb_messenger_account` varchar(150) DEFAULT NULL AFTER `parents_phone_no`"],
];

foreach ($user_columns as [$column, $definition]) {
    run_step("users.$column", function () use ($conn, $column, $definition) {
        if (column_exists($conn, 'users', $column) !== '') {
            return 'skip';
        }

        if (!$conn->query("ALTER TABLE `users` ADD COLUMN $definition")) {
            throw new RuntimeException($conn->error);
        }

        return 'done';
    });
}

run_step('announcements.audience', function () use ($conn) {
    if (column_exists($conn, 'announcements', 'audience') !== '') {
        return 'skip';
    }

    if (!$conn->query(
        "ALTER TABLE `announcements`
         ADD COLUMN `audience` enum('General','Students') NOT NULL DEFAULT 'General' AFTER `category`"
    )) {
        throw new RuntimeException($conn->error);
    }

    return 'done';
});

run_step('passers.exam_date', function () use ($conn) {
    if (column_exists($conn, 'passers', 'exam_date') !== '') {
        return 'skip';
    }

    if (!$conn->query(
        "ALTER TABLE `passers` ADD COLUMN `exam_date` date DEFAULT NULL AFTER `photo`"
    )) {
        throw new RuntimeException($conn->error);
    }

    return 'done';
});

run_step('payments.status refund enum', function () use ($conn) {
    $type = column_exists($conn, 'payments', 'status');


    if ($type === '' || strpos($type, 'refund_requested') !== false) {
        return 'skip';
    }

    if (!$conn->query(
        "ALTER TABLE `payments`
         MODIFY `status` enum('paid','pending','failed','refunded','refund_requested','cancelled') DEFAULT 'pending'"
    )) {
        throw new RuntimeException($conn->error);
    }

    return 'done';
});

run_step('payments.payment_type other enum', function () use ($conn) {
    $type = column_exists($conn, 'payments', 'payment_type');

    if ($type === '' || strpos($type, "'other'") !== false) {
        return 'skip';
    }

    if (!$conn->query(
        "ALTER TABLE `payments`
         MODIFY `payment_type` enum('full','installment','other') DEFAULT 'full'"
    )) {
        throw new RuntimeException($conn->error);
    }

    return 'done';
});

run_step('activity_logs table', function () use ($conn) {
    if (table_exists($conn, 'activity_logs')) {
        return 'skip';
    }

    $sql = file_get_contents(__DIR__ . '/add_activity_logs.sql');

    if (!$conn->multi_query($sql)) {
        throw new RuntimeException($conn->error);
    }

    while ($conn->more_results() && $conn->next_result()) {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    }

    return 'done';
});

run_step('gallery_images table', function () use ($conn) {
    if (table_exists($conn, 'gallery_images')) {
        return 'skip';
    }

    $sql = file_get_contents(__DIR__ . '/add_gallery_images.sql');

    if (!$conn->multi_query($sql)) {
        throw new RuntimeException($conn->error);
    }

    while ($conn->more_results() && $conn->next_result()) {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    }

    return 'done';
});

run_step('exam_result nullable scores', function () use ($conn) {
    if (!table_exists($conn, 'exam_result')) {
        throw new RuntimeException('exam_result table missing entirely');
    }

    if (!$conn->query(
        "ALTER TABLE `exam_result`
         MODIFY `diagnostic_exam` int(11) DEFAULT NULL,
         MODIFY `preboard_exam` int(11) DEFAULT NULL,
         MODIFY `compre_exam` int(11) DEFAULT NULL"
    )) {
        throw new RuntimeException($conn->error);
    }

    return 'done';
});

echo "\n" . ($failures === 0 ? "ALL MIGRATIONS APPLIED — database is in sync.\n" : "$failures STEP(S) FAILED.\n");

$conn->close();
exit($failures === 0 ? 0 : 1);
