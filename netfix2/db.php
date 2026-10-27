<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbname = getenv('DB_NAME') ?: 'netfixvn';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; // Mặc định rỗng cho XAMPP local
$port = (int)(getenv('DB_PORT') ?: 3306);

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>