<?php
session_start();
require 'db.php'; // Đảm bảo đường dẫn đúng đến file kết nối database

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Chuyển hướng về trang đăng nhập nếu chưa đăng nhập
    exit();
}

$user_id = $_SESSION['user_id'];
$user_info = null;
$messages = []; // Mảng để lưu các thông báo thành công/lỗi

// --- 1. Lấy thông tin người dùng hiện tại ---
$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
    $stmt->close();

    if (!$user_info) {
        // Lỗi: không tìm thấy người dùng dù đã đăng nhập
        session_destroy(); // Hủy session
        header("Location: login.php");
        exit();
    }
} else {
    $messages[] = ['type' => 'error', 'text' => 'Lỗi hệ thống khi lấy thông tin người dùng.'];
}

// --- 2. Xử lý cập nhật thông tin cá nhân (Username, Email) ---
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    if (empty($new_username) || empty($new_email)) {
        $messages[] = ['type' => 'error', 'text' => 'Tên người dùng và email không được để trống.'];
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = ['type' => 'error', 'text' => 'Địa chỉ email không hợp lệ.'];
    } else {
        // Kiểm tra xem username hoặc email mới đã tồn tại cho người dùng khác chưa
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt_check->bind_param("ssi", $new_username, $new_email, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $messages[] = ['type' => 'error', 'text' => 'Tên người dùng hoặc email này đã được sử dụng bởi người khác.'];
        } else {
            // Cập nhật thông tin người dùng
            $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt_update) {
                $stmt_update->bind_param("ssi", $new_username, $new_email, $user_id);
                if ($stmt_update->execute()) {
                    $messages[] = ['type' => 'success', 'text' => 'Thông tin cá nhân đã được cập nhật thành công!'];
                    // Cập nhật lại session
                    $_SESSION['username'] = $new_username;
                    $_SESSION['email'] = $new_email;
                    // Cập nhật lại user_info để hiển thị giá trị mới ngay lập tức
                    $user_info['username'] = $new_username;
                    $user_info['email'] = $new_email;
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Lỗi khi cập nhật thông tin: ' . $stmt_update->error];
                }
                $stmt_update->close();
            } else {
                $messages[] = ['type' => 'error', 'text' => 'Lỗi chuẩn bị câu lệnh cập nhật thông tin.'];
            }
        }
        $stmt_check->close();
    }
}

// --- 3. Xử lý thay đổi mật khẩu ---
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $messages[] = ['type' => 'error', 'text' => 'Vui lòng điền đầy đủ các trường mật khẩu.'];
    } elseif (!password_verify($current_password, $user_info['password'])) {
        $messages[] = ['type' => 'error', 'text' => 'Mật khẩu hiện tại không đúng.'];
    } elseif ($new_password !== $confirm_new_password) {
        $messages[] = ['type' => 'error', 'text' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.'];
    } elseif (strlen($new_password) < 6) {
        $messages[] = ['type' => 'error', 'text' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'];
    } else {
        // Hash mật khẩu mới
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu trong database
        $stmt_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt_pass) {
            $stmt_pass->bind_param("si", $hashed_new_password, $user_id);
            if ($stmt_pass->execute()) {
                $messages[] = ['type' => 'success', 'text' => 'Mật khẩu đã được thay đổi thành công!'];
                // Cập nhật lại user_info['password'] để nếu người dùng thử thay đổi lại, nó sẽ đúng
                $user_info['password'] = $hashed_new_password;
            } else {
                $messages[] = ['type' => 'error', 'text' => 'Lỗi khi thay đổi mật khẩu: ' . $stmt_pass->error];
            }
            $stmt_pass->close();
        } else {
            $messages[] = ['type' => 'error', 'text' => 'Lỗi chuẩn bị câu lệnh thay đổi mật khẩu.'];
        }
    }
}

?>
<title>Chỉnh sửa Hồ sơ - NetfixVN</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="iqiyi-style.css">
<style>
    /* CSS cho form chỉnh sửa hồ sơ */
      body {
    background-color: #121212;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    margin: 0;
    margin-top:100px ;
}
    .edit-profile-container {
        max-width: 600px;
        margin: 80px auto 50px auto; /* Khoảng cách trên 80px để tránh navbar fixed */
        padding: 40px;
        background-color: #1c1c1c;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        color: #fff;
    }
    .edit-profile-container h2 {
        text-align: center;
        color: #e50914;
        margin-bottom: 30px;
        font-size: 2.2em;
    }
    .profile-info {
        background-color: #2a2a2a;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
    }
    .profile-info p {
        margin-bottom: 10px;
        font-size: 1.1em;
    }
    .profile-info p strong {
        color: #e50914;
        margin-right: 5px;
    }

    .form-section {
        margin-bottom: 40px;
        padding-top: 20px;
        border-top: 1px solid #333; /* Đường phân cách */
    }
    .form-section:first-of-type {
        border-top: none;
        padding-top: 0;
    }
    .form-section h3 {
        color: #e50914;
        margin-bottom: 20px;
        font-size: 1.5em;
        text-align: center;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #bbb;
    }
    .form-group input[type="text"],
    .form-group input[type="password"],
    .form-group input[type="email"] {
        width: calc(100% - 20px);
        padding: 12px 10px;
        background-color: #333;
        border: 1px solid #555;
        border-radius: 5px;
        color: #fff;
        font-size: 1em;
    }
    .form-group input[type="text"]:focus,
    .form-group input[type="password"]:focus,
    .form-group input[type="email"]:focus {
        border-color: #e50914;
        outline: none;
        box-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
    }
    .message {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        font-weight: 600;
    }
    .message.error {
        background-color: #dc3545;
        color: #fff;
    }
    .message.success {
        background-color: #28a745;
        color: #fff;
    }
    .submit-btn {
        width: 100%;
        padding: 15px;
        background-color: #e50914;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 1.1em;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .submit-btn:hover {
        background-color: #f40612;
    }

</style>
</head>
<body>

<?php include 'header.php'; ?>
<div class="main-wrapper" style="display: flex;">
    <?php include 'user_sidebar.php' ?>
    <div class="edit-profile-container" style="flex: 1;">
        <h2>Chỉnh sửa Hồ sơ</h2>

        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo htmlspecialchars($msg['type']); ?>">
                <?php echo htmlspecialchars($msg['text']); ?>
            </div>
        <?php endforeach; ?>

        <div class="profile-info">
            <p><strong>Tên người dùng hiện tại:</strong> <?php echo htmlspecialchars($user_info['username']); ?></p>
            <p><strong>Email hiện tại:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
        </div>

        <div class="form-section">
            <h3>Cập nhật thông tin cá nhân</h3>
            <form action="edit_profile.php" method="POST">
                <div class="form-group">
                    <label for="username">Tên người dùng mới</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email mới</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="submit-btn">Cập nhật thông tin</button>
            </form>
        </div>

        <div class="form-section">
            <h3>Thay đổi mật khẩu</h3>
            <form action="edit_profile.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>
                <button type="submit" name="change_password" class="submit-btn">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</div>
<?php include'footer.php' ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="app.js"></script>
</body>
</html>