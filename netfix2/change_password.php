<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Lấy mật khẩu đã hash từ database để xác minh
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($current_password, $user['password'])) {
        // Mật khẩu hiện tại đúng
        if (empty($new_password) || strlen($new_password) < 6) {
            $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        } elseif ($new_password !== $confirm_new_password) {
            $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
        } else {
            // Hash mật khẩu mới và cập nhật
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_new_password, $user_id);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Đổi mật khẩu thất bại: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error = "Mật khẩu hiện tại không đúng.";
    }
}

// Chuyển hướng về trang profile với thông báo
$redirect_url = "profile.php?tab=settings";
if ($success) {
    $redirect_url .= "&status=success&message=" . urlencode("Đổi mật khẩu thành công!");
} elseif (!empty($error)) {
    $redirect_url .= "&status=error&message=" . urlencode($error);
}
header("Location: " . $redirect_url);
exit();
?>