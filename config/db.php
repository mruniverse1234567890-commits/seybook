<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'seybook_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Database connection failed. Start MySQL in XAMPP, import database.sql and check config/db.php.');
}
