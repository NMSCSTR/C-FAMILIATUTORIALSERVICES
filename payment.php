<?php
require_once __DIR__ . '/lib/session.php';
secure_session_start();

header("Location: upload_payment.php");
exit();
