<?php
session_start();
require 'db.php'; // Kết nối database

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Hàm lấy danh sách người dùng từ database
function getAllUsers($conn) {
    $stmt = $conn->prepare("SELECT id, username, email, is_vip FROM users ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

// Lấy danh sách người dùng
$users = getAllUsers($conn);

// Xử lý nâng cấp/hạ cấp tài khoản VIP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    
    $new_status = ($action === 'upgrade') ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET is_vip = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $success_message = ($action === 'upgrade') ? "Nâng cấp tài khoản thành VIP thành công!" : "Hạ cấp tài khoản về Thường thành công!";
        // Cập nhật lại danh sách người dùng
        $users = getAllUsers($conn);
    } else {
        $error_message = "Lỗi khi cập nhật trạng thái VIP: " . $stmt->error;
    }
    $stmt->close();
}

// Xác định trang hiện tại để làm nổi bật mục trong sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Quản Lý Tài Khoản VIP</title>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- BOX ICONS -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <!-- APP CSS -->
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
       body {
    background-color: #1a1a1a;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    margin: 0;
    padding: 0;
}

.dashboard-wrapper {
    display: flex;
    max-width: 1400px;
    margin: 0 auto;
}


.main-content {
    margin-left: 270px;
    width: calc(100% - 270px);
    padding: 40px 30px;
    animation: fadeIn 0.3s ease-in-out;
}

.user-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: #262626;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

.user-table th,
.user-table td {
    padding: 14px 18px;
    text-align: left;
    font-size: 15px;
    color: #eee;
    border-bottom: 1px solid #333;
}

.user-table th {
    background-color: #e50914;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 14px;
    font-weight: 700;
}

.user-table tr:hover {
    background-color: #1f1f1f;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.action-buttons form {
    display: inline-block;
}

.action-buttons button {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background 0.3s;
}

.upgrade-btn {
    background-color: #28a745;
    color: #fff;
}
.upgrade-btn:hover {
    background-color: #218838;
}

.downgrade-btn {
    background-color: #dc3545;
    color: #fff;
}
.downgrade-btn:hover {
    background-color: #b20710;
}

.message {
    text-align: center;
    margin-bottom: 20px;
    font-size: 15px;
    font-weight: 500;
}

.success {
    color: #28a745;
}

.error {
    color: #dc3545;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="dashboard-wrapper">
        <?php include 'admin_sidebar.php' ?>


        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>QUẢN LÍ TÀI KHOẢN VIP</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Chưa có người dùng nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['is_vip'] ? 'VIP' : 'Thường'; ?></td>
                                <td class="action-buttons">
                                    <?php if ($user['is_vip']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="downgrade">
                                            <button type="submit" class="downgrade-btn">Hạ cấp</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="upgrade">
                                            <button type="submit" class="upgrade-btn">Nâng cấp</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>