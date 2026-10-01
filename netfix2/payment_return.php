<?php 
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$resultCode = $_GET['resultCode'] ?? '';
$message = $_GET['message'] ?? '';
$success = ($resultCode == '0');

if ($user_id) {
    $status = $success ? 'Đã thanh toán' : 'Thất bại';
    $order_status = $success ? 'Hoàn thành' : 'Thất bại';

    // 1. Lấy ID đơn mới nhất
    $stmt = $conn->prepare("SELECT id FROM vip_orders WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $order_id = $order['id'];

        // 2. Cập nhật cả status và order_status
        $stmt = $conn->prepare("UPDATE vip_orders SET status = ?, order_status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $order_status, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    if ($success) {
        // Lấy hạn VIP hiện tại
        $stmt = $conn->prepare("SELECT vip_expires_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        $new_expiry = new DateTime();
        if ($user && $user['vip_expires_at'] !== null && strtotime($user['vip_expires_at']) > time()) {
            $new_expiry = new DateTime($user['vip_expires_at']);
        }
        $new_expiry->modify('+1 month');
        $vip_expires_at_str = $new_expiry->format('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE users SET is_vip = 1, vip_expires_at = ? WHERE id = ?");
        $stmt->bind_param("si", $vip_expires_at_str, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['profile_message'] = "🎉 Thanh toán thành công! Gói VIP đã được kích hoạt đến ngày " . $new_expiry->format('d/m/Y H:i');
        $_SESSION['profile_message_type'] = 'success';
    } else {
        $_SESSION['profile_message'] = "❌ Thanh toán thất bại: " . htmlspecialchars($message);
        $_SESSION['profile_message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            text-align: center;
            padding-top: 100px;
        }
        .container {
            display: inline-block;
            padding: 30px 50px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .redirect {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        a.button {
            margin-top: 15px;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            background: #007bff;
            color: white;
            text-decoration: none;
        }
    </style>
    <!-- Tự động chuyển về trang cá nhân sau 5 giây -->
    <meta http-equiv="refresh" content="5;url=profile.php">
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="icon">✅</div>
            <h2 class="success">Thanh toán thành công!</h2>
            <p>Gói VIP của bạn đã được kích hoạt.</p>
        <?php else: ?>
            <div class="icon">❌</div>
            <h2 class="error">Thanh toán thất bại</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <div class="redirect">
            Bạn sẽ được chuyển về trang cá nhân trong 5 giây...
        </div>
        <a href="profile.php" class="button">Quay lại ngay</a>
    </div>
</body>
</html>
