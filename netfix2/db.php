<?php
$host = 'localhost';
$dbname = 'netfixvn';
$username = 'root';
$password = ''; // Nếu bạn chưa đặt mật khẩu cho root

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>