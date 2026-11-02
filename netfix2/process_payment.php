<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pricing.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0;
$payment_method = $_POST['payment_method'] ?? 'Chuyển khoản ngân hàng';

// Gói VIP cố định
$vip_packages = [
    1 => ['name' => 'Gói VIP 1 Tháng', 'price' => 79000,  'duration' => '1 month'],
    2 => ['name' => 'Gói VIP 3 Tháng', 'price' => 237000, 'duration' => '3 months'],
    3 => ['name' => 'Gói VIP 6 Tháng', 'price' => 474000, 'duration' => '6 months'],
];

if (!isset($vip_packages[$package_id])) {
    $_SESSION['profile_message'] = "Gói VIP không hợp lệ.";
    $_SESSION['profile_message_type'] = 'error';
    header("Location: profile.php");
    exit();
}

$selected_package = $vip_packages[$package_id];
$package_name = $selected_package['name'];
$duration = $selected_package['duration'];
$price = (int)($_POST['final_price'] ?? $selected_package['price']);

if ($payment_method === 'Momo') {
    // ========== THANH TOÁN MOMO ==========
    $amount = $price;
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMO';
    $accessKey = 'F8BBA842ECF85';
    $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';

    $orderId = time() . "_" . $user_id;
    $requestId = time() . "_" . rand(1000, 9999);
    $orderInfo = "Thanh toán " . $package_name;
    $redirectUrl = "http://localhost/netfix2/payment_return.php";
    $ipnUrl = "http://localhost/netfix2/payment_notify.php";
    $extraData = $user_id . "_" . $package_id;
    $requestType = "payWithATM";

    $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = [
        'partnerCode' => $partnerCode,
        'accessKey' => $accessKey,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature,
        'lang' => 'vi'
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);

    $jsonResult = json_decode($result, true);
    file_put_contents("log_momo.txt", date('Y-m-d H:i:s') . " - Response: " . $result . "\n", FILE_APPEND);

    if (isset($jsonResult['payUrl'])) {
        $status = 'Pending';
        $stmt = $conn->prepare("INSERT INTO vip_orders (user_id, package_name, price, payment_method, status, order_status, order_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("isdss", $user_id, $package_name, $price, $payment_method, $status);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $jsonResult['payUrl']);
        exit();
    } else {
        echo "<h3 style='color: red'>Không thể kết nối MoMo (ATM): " . htmlspecialchars($jsonResult['message'] ?? 'Lỗi không xác định') . "</h3>";
        exit();
    }

} else {
    // ========== THANH TOÁN NGAY ==========
    $status = 'Completed';
    $order_status = 'active';

    // Ghi đơn hàng
    $stmt = $conn->prepare("INSERT INTO vip_orders (user_id, package_name, price, payment_method, status, order_status, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdsss", $user_id, $package_name, $price, $payment_method, $status, $order_status);
    $stmt->execute();
    $stmt->close();

    // Cập nhật VIP
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

    $new_expiry->modify('+' . $duration);
    $vip_expires_at_str = $new_expiry->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("UPDATE users SET is_vip = 1, vip_expires_at = ? WHERE id = ?");
    $stmt->bind_param("si", $vip_expires_at_str, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['profile_message'] = "🎉 Gói VIP đã được kích hoạt! Hạn đến ngày " . $new_expiry->format('d/m/Y H:i');
    $_SESSION['profile_message_type'] = 'success';
    header("Location: profile.php");
    exit();
}
?>
