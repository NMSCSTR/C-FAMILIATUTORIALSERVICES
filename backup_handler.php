<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if (!csrf_validate_request_token($_GET['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid or expired request token.');
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
    $selected_tables = array_values(array_filter(
        array_map('trim', explode(',', (string) $tables_param)),
        fn($t) => in_array($t, $all_tables)
    ));
    if (empty($selected_tables)) {
        http_response_code(400);
        exit('No valid tables selected.');
    }
}

// --- Stream SQL dump ---
$timestamp = date('Y-m-d_H-i-s');
$filename  = "cfts_backup_{$timestamp}.sql";

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "-- C-Familia Tutorial Services Database Backup\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Database : " . ($config['dbname'] ?? '') . "\n";
echo "-- Tables   : " . implode(', ', $selected_tables) . "\n";
echo "-- Server   : " . mysqli_get_server_info($conn) . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n";
echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
echo "SET NAMES utf8mb4;\n\n";
flush();

const BACKUP_ROWS_PER_INSERT = 50;

foreach ($selected_tables as $table) {
    $create_res = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    if (!$create_res) {
        continue;
    }
    $create_row = mysqli_fetch_row($create_res);
    if (!$create_row) {
        continue;
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- Table: `{$table}`\n";
    echo "-- --------------------------------------------------------\n\n";
    echo "DROP TABLE IF EXISTS `{$table}`;\n";
    echo $create_row[1] . ";\n\n";

    $data_res = mysqli_query($conn, "SELECT * FROM `$table`");
    if (!$data_res || mysqli_num_rows($data_res) === 0) {
        continue;
    }

    $cols = [];
    foreach (mysqli_fetch_fields($data_res) as $f) {
        $cols[] = "`{$f->name}`";
    }
    $col_list = implode(', ', $cols);

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

        if (count($rows_sql) >= BACKUP_ROWS_PER_INSERT) {
            echo "INSERT INTO `{$table}` ({$col_list}) VALUES\n" . implode(",\n", $rows_sql) . ";\n\n";
            $rows_sql = [];
            flush();
        }
    }

    if (!empty($rows_sql)) {
        echo "INSERT INTO `{$table}` ({$col_list}) VALUES\n" . implode(",\n", $rows_sql) . ";\n\n";
        flush();
    }
}

echo "SET FOREIGN_KEY_CHECKS=1;\n";
flush();

log_activity($conn, 'backup.download', "Database backup downloaded: {$filename}");

exit;
