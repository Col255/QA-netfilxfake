<?php
session_start();
require 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = '';

// Lấy username
$stmt_user = $conn->prepare("SELECT username FROM users WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($user_data = $result_user->fetch_assoc()) {
        $username = $user_data['username'];
    }
    $stmt_user->close();
}

// Gói VIP cố định
$vip_packages = [
    1 => [
        'id' => 1,
        'name' => 'Gói VIP 1 Tháng',
        'price' => 79000,
        'duration' => '1 tháng',
        'description' => 'Xem phim HD, không quảng cáo',
        'features' => ['Xem trên mọi thiết bị', 'Không quảng cáo', 'Huỷ bất cứ lúc nào']
    ],
    2 => [
        'id' => 2,
        'name' => 'Gói VIP 3 Tháng',
        'price' => 237000,
        'duration' => '3 tháng',
        'description' => 'Tiết kiệm hơn so với từng tháng',
        'features' => ['Xem trên mọi thiết bị', 'Không quảng cáo', 'Ưu đãi dài hạn']
    ],
    3 => [
        'id' => 3,
        'name' => 'Gói VIP 6 Tháng',
        'price' => 474000,
        'duration' => '6 tháng',
        'description' => 'Xem phim thả ga, tiết kiệm hơn',
        'features' => ['Xem trên mọi thiết bị', 'Không quảng cáo', 'Ưu đãi dài hạn']
    ],
];

$selected_package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : (isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0);
$selected_package = $vip_packages[$selected_package_id] ?? null;

$final_price = $selected_package['price'] ?? 0;

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận thanh toán - NetfixVN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="iqiyi-style.css">
    <style>
        .checkout-page {
    font-family: 'Inter', sans-serif;
    background-color: #0f0f0f;
    color: #fff;
    padding: 40px 20px;
    min-height: 100vh;
}

.checkout-page .checkout-container {
    max-width: 650px;
    margin: 40px auto;
    background-color: #1f1f1f;
    padding: 35px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    border: 1px solid #2c2c2c;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(10px);}
    to {opacity: 1; transform: translateY(0);}
}

.checkout-page h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 30px;
    color: #e50914;
    font-weight: 600;
}

.checkout-page .order-summary p {
    font-size: 16px;
    margin-bottom: 12px;
    line-height: 1.6;
    border-bottom: 1px solid #333;
    padding-bottom: 6px;
}

.checkout-page .payment-methods {
    margin: 30px 0 20px;
}

.checkout-page .payment-methods label {
    display: flex;
    align-items: center;
    background-color: #2a2a2a;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border: 1px solid transparent;
}

.checkout-page .payment-methods label:hover {
    background-color: #333;
    border-color: #e50914;
}

.checkout-page .payment-methods input[type="radio"] {
    margin-right: 12px;
    transform: scale(1.2);
    accent-color: #e50914;
}

.checkout-page .confirm-btn {
    background-color: #e50914;
    color: #fff;
    padding: 14px;
    width: 100%;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.25s ease;
    box-shadow: 0 4px 12px rgba(229, 9, 20, 0.3);
    margin-top: 10px;
}

.checkout-page .confirm-btn:hover {
    background-color: #b20710;
}

.checkout-page .back-link {
    display: block;
    margin-top: 25px;
    text-align: center;
    color: #aaa;
    font-size: 15px;
}

.checkout-page .back-link a {
    color: #e50914;
    text-decoration: none;
    font-weight: 500;
}

.checkout-page .back-link a:hover {
    text-decoration: underline;
}

.checkout-page .no-package {
    text-align: center;
    color: #bbb;
    font-size: 16px;
    padding: 20px;
}

@media (max-width: 600px) {
    .checkout-page .checkout-container {
        padding: 25px 20px;
    }

    .checkout-page h2 {
        font-size: 24px;
    }

    .checkout-page .confirm-btn {
        font-size: 15px;
    }
}
.checkout-page .price-original {
    color: #fff;
    font-size: 15px;
}

.checkout-page .price-discount {
    color: #fbc531; /* vàng */
    font-size: 15px;
    font-style: italic;
    margin-bottom: 8px;
}

.checkout-page .price-final {
    color: #e50914;
    font-size: 20px;
    font-weight: bold;
    border-top: 1px dashed #444;
    padding-top: 8px;
    margin-top: 8px;
}

</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="checkout-page">
<div class="checkout-container">
    <h2>Xác nhận Đơn hàng VIP</h2>

    <?php if ($selected_package): ?>
        <div class="order-summary">
            <p>Tên khách hàng: <strong><?php echo htmlspecialchars($user_data['username']); ?></strong></p>
            <p>Gói đã chọn: <strong><?php echo htmlspecialchars($selected_package['name']); ?></strong></p>
            <p>Thời hạn: <strong><?php echo htmlspecialchars($selected_package['duration']); ?></strong></p>
            <p class="price-final">Tổng thanh toán: <?php echo number_format($final_price); ?> VNĐ</p>

        </div>

        <form action="process_payment.php" method="POST">
            <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($selected_package_id); ?>">
            <input type="hidden" name="final_price" value="<?php echo $final_price; ?>">
            <h3>Chọn hình thức thanh toán:</h3>
            <div class="payment-methods">
                <label><input type="radio" name="payment_method" value="Chuyển khoản ngân hàng" required> Chuyển khoản ngân hàng</label>
                <label><input type="radio" name="payment_method" value="Momo"> Momo</label>
                <label><input type="radio" name="payment_method" value="VNPAY"> VNPAY</label>
            </div>
            <button type="submit" class="confirm-btn">Thanh toán</button>
        </form>
    <?php else: ?>
        <p style="text-align: center; color: #bbb;">
            Không tìm thấy thông tin gói VIP.<br>
            Vui lòng quay lại trang <a href="pricing.php" style="color: #e50914;">chọn gói</a>.
        </p>
    <?php endif; ?>

    <div class="back-link">
        <a href="pricing.php">← Quay lại chọn gói</a>
    </div>
</div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
