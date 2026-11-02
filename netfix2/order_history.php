<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_info = null;
$messages = [];

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT id, username, email, is_vip, vip_expires_at FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
    $stmt->close();

    if (!$user_info) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} else {
    $messages[] = ['type' => 'error', 'text' => 'Lỗi hệ thống khi lấy thông tin người dùng.'];
}

// Lấy thông báo từ session (từ process_payment.php)
if (isset($_SESSION['profile_message'])) {
    $messages[] = ['type' => $_SESSION['profile_message_type'], 'text' => $_SESSION['profile_message']];
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

// Lấy danh sách đơn hàng VIP của người dùng
$vip_orders = [];
$stmt_orders = $conn->prepare("SELECT id, package_name, price, payment_method, status, order_status, order_date FROM vip_orders WHERE user_id = ? ORDER BY order_date DESC");
if ($stmt_orders) {
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
    $vip_orders = $result_orders->fetch_all(MYSQLI_ASSOC);
    $stmt_orders->close();
} else {
    $messages[] = ['type' => 'error', 'text' => 'Lỗi hệ thống khi lấy lịch sử đơn hàng.'];
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử Đơn hàng VIP</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
   <link rel="stylesheet" href="iqiyi-style.css">
    <style>
        /* GIỮ NGUYÊN CSS CỦA BẠN HOẶC THÊM CÁC STYLE SAU NẾU CHƯA CÓ */
      body {
    background-color: #121212;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    margin: 0;
    margin-top:100px ;
}

.profile-container {
    max-width: 880px;
    margin: 60px auto;
    margin-left: 100px;
    background: linear-gradient(135deg, #1e1e1e, #2b2b2b);
    padding: 40px 30px;
    border-radius: 16px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.6);
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.profile-container h2, .order-history h3 {
    text-align: center;
    color: #e50914;
    font-size: 28px;
    margin-bottom: 20px;
    font-weight: 700;
}

.order-history {
    margin-top: 30px;
}

.order-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.order-table th, .order-table td {
    padding: 14px 18px;
    text-align: left;
    border-bottom: 1px solid #3a3a3a;
    font-size: 15px;
}

.order-table th {
    background-color: #e50914;
    color: #fff;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.order-table tbody tr:hover {
    background-color: #1a1a1a;
}

.order-table td {
    color: #ddd;
}

.status-Pending {
    color: #ffc107;
    font-weight: bold;
}

.status-Completed {
    color: #28a745;
    font-weight: bold;
}

.status-Cancelled {
    color: #dc3545;
    font-weight: bold;
}

.message {
    text-align: center;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 15px;
    font-weight: 600;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}

.message.success {
    background-color: #28a745;
    color: #fff;
}

.message.error {
    background-color: #dc3545;
    color: #fff;
}
.order-table td.status-Completed {
    color: #28a745;
    font-weight: 600;
}

.order-table td.status-Pending {
    color: #ffc107;
    font-weight: 600;
}

.order-table td.status-Cancelled {
    color: #dc3545;
    font-weight: 600;
}

.order-table .status-active {
    color: #28a745;
    font-weight: bold;
}
.order-table .status-cancelled {
    color: #dc3545;
    font-weight: bold;
}

    </style>
</head>
<body>

<?php include 'header.php' ?>
<div class="main-wrapper" style="display: flex;">
    <?php include 'user_sidebar.php' ?>
    <div class="profile-container" style="flex: 1;">
        <div class="order-history">
            <h3>Lịch sử Đơn hàng VIP</h3>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Gói đăng ký</th>
                        <th>Giá</th>
                        <th>Hình thức TT</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt hàng</th>
                        <th>Tình trạng gói</th> <!-- Mới -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vip_orders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #bbb;">Bạn chưa có đơn hàng VIP nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vip_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                                <td><?php echo number_format($order['price']); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td class="status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td class="status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                    <?php
                                        if ($order['order_status'] === 'active') {
                                            echo 'Đang hoạt động';
                                        } elseif ($order['order_status'] === 'cancelled') {
                                            echo 'Đã hủy gói';
                                        } else {
                                            echo htmlspecialchars($order['order_status']);
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include'footer.php' ?>

    </body>
</html>