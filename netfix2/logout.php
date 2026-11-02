<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng đến trang đăng nhập của người dùng thông thường
header("Location: login.php");
exit();
?>