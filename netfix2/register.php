<?php
session_start();
require 'db.php'; // Kết nối database

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Xử lý form đăng ký
$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra đầu vào
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Vui lòng nhập đầy đủ thông tin.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        // Kiểm tra username hoặc email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Tên đăng nhập hoặc email đã được sử dụng.";
        } else {
            // Mã hóa mật khẩu và lưu vào database (is_admin mặc định là 0)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = 0; // Người dùng thông thường
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $is_admin);
            
            if ($stmt->execute()) {
                $success_message = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Refresh: 2; url=login.php"); // Chuyển hướng sau 2 giây
            } else {
                $error_message = "Lỗi khi đăng ký: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Đăng Ký</title>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- BOX ICONS -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <!-- APP CSS -->
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
        .register-form { max-width: 400px; margin: 50px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; }
        .register-form h2 { color: #fff; text-align: center; margin-bottom: 20px; }
        .register-form label { color: #fff; display: block; margin: 10px 0 5px; }
        .register-form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; background: #333; color: #fff; }
        .register-form button { background: #e50914; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
        .register-form button:hover { background: #b20710; }
        .error { color: #dc3545; text-align: center; margin-bottom: 20px; }
        .success { color: #28a745; text-align: center; margin-bottom: 20px; }
        .login-link { text-align: center; margin-top: 15px; }
        .login-link a { color: #e50914; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <!-- REGISTER FORM -->
    <div class="section">
        <div class="container">
            <div class="register-form">
                <h2>Đăng Ký</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <form action="register.php" method="POST">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <button type="submit">Đăng ký</button>
                </form>
                <div class="login-link">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </div>
    <!-- END REGISTER FORM -->

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>