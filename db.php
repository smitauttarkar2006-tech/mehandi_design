<?php
declare(strict_types=1);

$host = 'localhost';
$port = 4306;
$database = 'mehndi_db';
$username = 'root';
$password = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Could not connect to MySQL. Check that MySQL is running and that the host, port, username, and password in includes/db.php match your XAMPP settings.');
}
