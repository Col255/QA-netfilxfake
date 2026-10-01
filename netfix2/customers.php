<?php
session_start();
require 'db.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Xử lý xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']); // Chuyển đổi sang số nguyên để tránh SQL Injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        // Xóa thành công, chuyển hướng lại trang
        header("Location: customers.php?message=success");
    } else {
        // Xóa thất bại, thông báo lỗi
        header("Location: customers.php?message=error&msg=Xóa người dùng thất bại");
    }
    $stmt->close();
    exit();
}

function getAllCustomers($conn) {
    $stmt = $conn->prepare("SELECT id, username, email, is_vip, created_at FROM users ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $customers;
}

$customers = getAllCustomers($conn);

$current_page = basename($_SERVER['PHP_SELF']);

// Xử lý thông báo
$message = '';
if (isset($_GET['message'])) {
    $message_class = $_GET['message'] === 'success' ? 'success' : 'error';
    $message = '<div class="message ' . $message_class . '">' . htmlspecialchars($_GET['msg'] ?? ($message_class === 'success' ? 'Xóa người dùng thành công' : 'Có lỗi xảy ra')) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Quản Lý Khách Hàng</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
        /* Giữ nguyên các style CSS hiện tại */
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

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: #262626;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }

        .customer-table th,
        .customer-table td {
            padding: 14px 18px;
            text-align: left;
            font-size: 15px;
            color: #eee;
            border-bottom: 1px solid #333;
        }

        .customer-table th {
            background-color: #e50914;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
            font-weight: 700;
        }

        .customer-table tr:hover {
            background-color: #1f1f1f;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .action-buttons a,
        .action-buttons button {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
        }

        .edit-btn {
            background-color:rgb(40, 167, 114);
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #218838;
        }

        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .delete-btn:hover {
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
    <div class="dashboard-wrapper">
        <?php include 'admin_sidebar.php' ?>

        <div class="main-content">
            <?php echo $message; ?>
            <h2>QUẢN LÍ NGƯỜI DÙNG</h2>
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>VIP</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Chưa có khách hàng nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo $customer['is_vip'] ? 'Có' : 'Không'; ?></td>
                                <td><?php echo htmlspecialchars($customer['created_at'] ?: 'N/A'); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="edit-btn">Sửa</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $customer['id']; ?>">
                                        <button type="submit" class="delete-btn">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>