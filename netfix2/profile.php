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

// Cập nhật trạng thái VIP nếu đã hết hạn
if ($user_info['is_vip'] == 1 && strtotime($user_info['vip_expires_at']) < time()) {
    // Cập nhật lại database
    $stmt_update = $conn->prepare("UPDATE users SET is_vip = 0 WHERE id = ?");
    $stmt_update->bind_param("i", $user_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Cập nhật lại thông tin trong biến $user_info để hiển thị đúng
    $user_info['is_vip'] = 0;
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
    <title>NetfixVN - Hồ sơ của bạn</title>
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
    max-width: 900px;
    margin: 50px auto;
    margin-left: 100px; /* tránh đè lên sidebar */
    background: linear-gradient(135deg, #1f1f1f, #2a2a2a);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
    transition: all 0.3s ease;
        
}

.profile-container h2 {
    color: #e50914;
    text-align: center;
    font-size: 2rem;
    margin-bottom: 20px;
    text-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
}

.profile-info p {
    font-size: 1.1rem;
    margin-bottom: 12px;
    line-height: 1.5;
}

.profile-info strong {
    color:rgb(192, 217, 25);
}

.profile-actions {
    margin-top: 30px;
    text-align: center;
}

.profile-actions a {
    display: inline-block;
    padding: 12px 24px;
    background-color: #e50914;
    color: #fff;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(229, 9, 20, 0.3);
}

.profile-actions a:hover {
    background-color: #b20710;
    box-shadow: 0 6px 18px rgba(229, 9, 20, 0.5);
    transform: translateY(-1px);
}

/* Message alert */
.message {
    text-align: center;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.message.success {
    background-color: #198754;
    color: #fff;
}

.message.error {
    background-color: #dc3545;
    color: #fff;
}

/* Lịch sử đơn hàng */
.order-history {
    margin-top: 40px;
    animation: fadeIn 0.5s ease-in-out;
}

.order-history h3 {
    color: #f3425f;
    text-align: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 10px;
}

.order-table th,
.order-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #444;
}

.order-table th {
    background-color: #e50914;
    color: white;
}

.order-table tr:hover {
    background-color: #1e1e1e;
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

/* Animation fade */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.status-active {
    color: #28a745;
    font-weight: bold;
}

.status-cancelled {
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
        <h2>Hồ sơ của bạn</h2>

        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo htmlspecialchars($msg['type']); ?>">
                    <?php echo htmlspecialchars($msg['text']); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="profile-info">
            <p>Tên người dùng: <strong><?php echo htmlspecialchars($user_info['username']); ?></strong></p>
            <p>Email: <strong><?php echo htmlspecialchars($user_info['email']); ?></strong></p>
            <p>Trạng thái VIP: <strong><?php echo ($user_info['is_vip'] == 1 && strtotime($user_info['vip_expires_at']) > time()) ? 'Đang hoạt động' : 'Chưa kích hoạt'; ?></strong></p>
            <?php if ($user_info['is_vip'] == 1 && strtotime($user_info['vip_expires_at']) > time()): ?>
                <p>Ngày hết hạn VIP: <strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($user_info['vip_expires_at']))); ?></strong></p>
            <?php endif; ?>
        </div>
    

    <!-- Trong <div class="profile-container"> sau phần .profile-info -->

        <div class="profile-actions">
        <a href="#" onclick="toggleOrderHistory(event)">Xem lịch sử đơn hàng VIP</a>
        </div>
        <div class="order-history" id="order-history" style="display: none;">
            <h3>Lịch sử đơn hàng VIP</h3>
            <?php if (empty($vip_orders)): ?>
                <p style="text-align:center;">Bạn chưa có đơn hàng nào.</p>
            <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Gói</th>
                        <th>Giá</th>
                        <th>Hình thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Tình trạng gói</th> <!-- Mới thêm -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vip_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                            <td><?php echo number_format($order['price'], 0, ',', '.') . 'đ'; ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td class="status-<?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td class="status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                <?php
                                    if ($order['order_status'] === 'active') {
                                        echo 'Đang hoạt động';
                                    } elseif ($order['order_status'] === 'cancelled') {
                                        echo 'Hết hiệu lực';
                                    } else {
                                        echo htmlspecialchars($order['order_status']); // fallback
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include'footer.php' ?>
<script>
function toggleOrderHistory(event) {
    event.preventDefault();
    const history = document.getElementById('order-history');
    const btn = event.target;
    if (history.style.display === 'none') {
        history.style.display = 'block';
        btn.textContent = 'Ẩn lịch sử đơn hàng VIP';
    } else {
        history.style.display = 'none';
        btn.textContent = 'Xem lịch sử đơn hàng VIP';
    }
}
</script>
<script>
    // Tự ẩn thông báo sau 4 giây
    setTimeout(() => {
        const msg = document.querySelector('.message');
        if (msg) {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 4000);
</script>

</body>
</html>