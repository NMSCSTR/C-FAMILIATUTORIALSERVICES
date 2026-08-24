<?php
require_once __DIR__ . '/lib/session.php';
secure_session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    log_activity($conn, 'logout', 'User signed out', [
        'user_id' => (int) $_SESSION['user_id'],
        'user_role' => $_SESSION['role'] ?? null,
    ]);
}

session_destroy();
header("Location: login.php");
exit();
?>