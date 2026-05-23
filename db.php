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
?>