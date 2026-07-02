<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include 'db.php';

$tables_param = $_GET['tables'] ?? 'all';

// --- Gather tables ---
$all_tables = [];
$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($res)) {
    $all_tables[] = $row[0];
}

if ($tables_param === 'all' || empty($tables_param)) {
    $selected_tables = $all_tables;
} else {
    $selected_tables = array_filter(
        array_map('trim', explode(',', $tables_param)),
        fn($t) => in_array($t, $all_tables)
    );
    if (empty($selected_tables)) {
        http_response_code(400);
        exit('No valid tables selected.');
    }
}

// --- Build SQL dump ---
$timestamp = date('Y-m-d_H-i-s');
$filename  = "cfts_backup_{$timestamp}.sql";

$sql  = "-- C-Familia Tutorial Services Database Backup\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Database : {$dbname}\n";
$sql .= "-- Tables   : " . implode(', ', $selected_tables) . "\n";
$sql .= "-- Server   : " . mysqli_get_server_info($conn) . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
$sql .= "SET NAMES utf8mb4;\n\n";

foreach ($selected_tables as $table) {
    $table_safe = mysqli_real_escape_string($conn, $table);

    // DROP + CREATE
    $create_res = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $create_row = mysqli_fetch_row($create_res);
    $sql .= "-- --------------------------------------------------------\n";
    $sql .= "-- Table: `{$table}`\n";
    $sql .= "-- --------------------------------------------------------\n\n";
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $sql .= $create_row[1] . ";\n\n";

    // Data
    $data_res = mysqli_query($conn, "SELECT * FROM `$table`");
    $num_rows = mysqli_num_rows($data_res);

    if ($num_rows > 0) {
        // Column names
        $cols = [];
        $fields = mysqli_fetch_fields($data_res);
        foreach ($fields as $f) {
            $cols[] = "`{$f->name}`";
        }
        $col_list = implode(', ', $cols);

        $sql .= "INSERT INTO `{$table}` ({$col_list}) VALUES\n";

        $rows_sql = [];
        while ($row = mysqli_fetch_row($data_res)) {
            $values = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . mysqli_real_escape_string($conn, $val) . "'";
                }
            }
            $rows_sql[] = '(' . implode(', ', $values) . ')';
        }
        $sql .= implode(",\n", $rows_sql) . ";\n\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
$sql .= "COMMIT;\n";

// Log the backup action
log_activity($conn, $_SESSION['user_id'] ?? null, 'admin', 'backup', "Database backup downloaded: {$filename}");

// --- Stream as download ---
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($sql));
header('Pragma: no-cache');
header('Expires: 0');

echo $sql;
exit;
