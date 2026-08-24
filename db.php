<?php
$config_file = __DIR__ . '/config.php';

if (!is_file($config_file)) {
    http_response_code(500);
    die('Missing configuration file. Copy config.sample.php to config.php and fill in your database credentials.');
}

$config = require $config_file;

$conn = mysqli_connect(
    $config['host'] ?? 'localhost',
    $config['user'] ?? '',
    $config['pass'] ?? '',
    $config['dbname'] ?? ''
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

function ensure_payment_schema($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $result = mysqli_query($conn, "SHOW COLUMNS FROM payments LIKE 'status'");
    $column = mysqli_fetch_assoc($result);
    if (!$column || strpos($column['Type'], 'refund_requested') === false) {
        mysqli_query(
            $conn,
            "ALTER TABLE `payments` MODIFY `status` enum('paid','pending','failed','refunded','refund_requested','cancelled') DEFAULT 'pending'"
        );
    }

    $result = mysqli_query($conn, "SHOW COLUMNS FROM payments LIKE 'payment_type'");
    $column = mysqli_fetch_assoc($result);
    if (!$column || strpos($column['Type'], "'other'") === false) {
        mysqli_query(
            $conn,
            "ALTER TABLE `payments` MODIFY `payment_type` enum('full','installment','other') DEFAULT 'full'"
        );
    }
}

ensure_payment_schema($conn);

require_once __DIR__ . '/activity_log.php';
?>
