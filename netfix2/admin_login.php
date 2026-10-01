<?php
session_start();
require 'db.php'; // Kết nối database

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['user_id']) && $_SESSION['is_admin']) {
    header("Location: admin_dashboard.php");
    exit();
}

// Xử lý form đăng nhập
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? ''; // Có thể là username hoặc email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error_message = "Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu.";
    } else {
        // Truy vấn kiểm tra username hoặc email, chỉ cho phép admin
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE (username = ? OR email = ?) AND is_admin = 1");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Kiểm tra mật khẩu
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error_message = "Mật khẩu không đúng.";
            }
        } else {
            $error_message = "Tên đăng nhập hoặc email không tồn tại hoặc tài khoản này không phải admin.";
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
    <title>NetfixVN - Đăng Nhập Admin</title>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- BOX ICONS -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <!-- APP CSS -->
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
        .login-form { max-width: 400px; margin: 50px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; }
        .login-form h2 { color: #fff; text-align: center; margin-bottom: 20px; }
        .login-form label { color: #fff; display: block; margin: 10px 0 5px; }
        .login-form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; background: #333; color: #fff; }
        .login-form button { background: #e50914; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
        .login-form button:hover { background: #b20710; }
        .error { color: #dc3545; text-align: center; margin-bottom: 20px; }
        .user-login-link { text-align: center; margin-top: 15px; }
        .user-login-link a { color: #e50914; text-decoration: none; }
        .user-login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- ADMIN LOGIN FORM -->
    <div class="section">
        <div class="container">
            <div class="login-form">
                <h2>Đăng Nhập Admin</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="admin_login.php" method="POST">
                    <label for="identifier">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="identifier" name="identifier" required>
                    
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required>
                    
                    <button type="submit">Đăng nhập Admin</button>
                </form>
                <div class="user-login-link">
                    Là người dùng thường? <a href="login.php">Đăng nhập người dùng</a>
                </div>
            </div>
        </div>
    </div>
    <!-- END ADMIN LOGIN FORM -->

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>