<?php
$host = "localhost";
$user = "rhondelp";
$pass = "StrongPass123!";
$dbname = "cfts";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function ensure_payment_refund_schema($conn) {
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
}

ensure_payment_refund_schema($conn);

require_once __DIR__ . '/activity_log.php';
?>