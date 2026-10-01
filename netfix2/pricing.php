<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$is_user_vip = false;
$vip_expires_at = null;

// 1. Lấy thông tin VIP
$stmt = $conn->prepare("SELECT is_vip, vip_expires_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    $vip_expires_at = $user['vip_expires_at'];
    $is_user_vip = ($user['is_vip'] == 1 && strtotime($vip_expires_at) > time());

    // ❗ Nếu VIP đã hết hạn thì cập nhật user + orders
    if ($user['is_vip'] == 1 && strtotime($vip_expires_at) <= time()) {
        // 1. Reset trạng thái VIP
        $stmt = $conn->prepare("UPDATE users SET is_vip = 0, vip_expires_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // 2. Chuyển đơn active → expired
        $stmt = $conn->prepare("UPDATE vip_orders SET order_status = 'expired' WHERE user_id = ? AND order_status = 'active'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // 3. Cập nhật lại biến trạng thái
        $is_user_vip = false;
        $vip_expires_at = null;
    }
}

// 2. Gói VIP có sẵn
$vip_packages = [
    [
        'id' => 1,
        'name' => 'Gói VIP 1 Tháng',
        'price' => 79000,
        'duration' => '1 month',
        'description' => 'Truy cập phim HD, không quảng cáo.',
        'features' => ['Mở khóa toàn bộ phim Vip', 'Không quảng cáo', 'Huỷ bất cứ lúc nào']
    ],
    [
        'id' => 2,
        'name' => 'Gói VIP 3 Tháng',
        'price' => 201675,
        'duration' => '3 months',
        'description' => 'Tiết kiệm 15%, trải nghiệm không giới hạn.',
        'features' => ['Mở khóa toàn bộ phim Vip', 'Không quảng cáo', 'Ưu đãi dài hạn']
    ],
    [
        'id' => 3,
        'name' => 'Gói VIP 6 Tháng',
        'price' => 379200,
        'duration' => '6 months',
        'description' => 'Tiết kiệm 20%, xem phim thả ga.',
        'features' => ['Mở khóa toàn bộ phim Vip', 'Không quảng cáo', 'Ưu đãi dài hạn']
    ]
];

// 3. Xác định gói đang sử dụng (nếu còn active)
$current_vip_package_id = null;
$current_vip_package_name = '';

if ($is_user_vip) {
    $stmt = $conn->prepare("SELECT package_name, price, payment_method, status, order_date FROM vip_orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($package_name, $price, $payment_method, $status, $order_date); // Thêm biến cho tất cả cột
    if ($stmt->fetch()) {
        $current_vip_package_name = $package_name;
        foreach ($vip_packages as $pkg) {
            if ($pkg['name'] === $package_name) {
                $current_vip_package_id = $pkg['id'];
                break;
            }
        }
    }
    $stmt->close();
}
// 4. Hủy VIP thủ công
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_vip'])) {
    $stmt = $conn->prepare("UPDATE users SET is_vip = 0, vip_expires_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE vip_orders SET order_status = 'cancelled' WHERE user_id = ? AND order_status = 'active'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['profile_message'] = "Huỷ gói VIP thành công!";
    $_SESSION['profile_message_type'] = 'success';
    header("Location: pricing.php");
    exit();
}

// 5. Gia hạn VIP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['extend_vip'])) {
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

    $stmt = $conn->prepare("UPDATE users SET vip_expires_at = ? WHERE id = ?");
    $stmt->bind_param("si", $vip_expires_at_str, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['profile_message'] = "Gia hạn thành công đến ngày " . $new_expiry->format('d/m/Y H:i');
    $_SESSION['profile_message_type'] = 'success';
    header("Location: pricing.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>NetfixVN - Gói VIP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
   <link rel="stylesheet" href="iqiyi-style.css">
    <style>
              body {
    background-color: #121212;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    margin: 0;
    margin-top:100px ;
}
      .vip-section {
    font-family: 'Inter', sans-serif;
    background-color: #0f0f0f;
    padding: 40px 20px;
    
}

.vip-section .container {
    max-width: 1100px;
    margin: auto;
}

.vip-section .section-title {
    text-align: center;
    margin-bottom: 50px;
}

.vip-section .section-title h2 {
    color: #e50914;
    font-size: 40px;
    margin-bottom: 10px;
}

.vip-section .section-title p {
    color: #ccc;
    font-size: 18px;
}

.vip-section .vip-status {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid #28a745;
    padding: 15px;
    border-radius: 8px;
    color: #28a745;
    text-align: center;
    font-size: 16px;
    margin-bottom: 30px;
}

.vip-section .plans {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.vip-section .plan-card {
    background: #1e1e1e;
    padding: 30px;
    border-radius: 16px;
    width: 320px;
    border: 2px solid #2c2c2c;
    transition: all 0.3s ease;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 450px;
}

.vip-section .plan-card:hover {
    border-color: #e50914;
    transform: translateY(-5px);
}

.vip-section .plan-card h3 {
    font-size: 1.5rem;
    color: #fff;
    margin-bottom: 10px;
    min-height: 48px;
}

.vip-section .plan-desc {
    font-size: 1rem;
    color: #aaa;
    margin-bottom: 10px;
    min-height: 50px;
}

.vip-section .price {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 20px;
    min-height: 60px;
}

.vip-section .discounted-price {
    font-size: 2rem;
    color: #ffcc00;
    font-weight: 800;
    text-align: center;
    line-height: 1.3;
}

.vip-section .original-price {
    font-size: 1rem;
    color: #999;
    text-decoration: line-through;
    position: absolute;
    bottom: 0;
    right: 10px;
}

.vip-section .features {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
    text-align: left;
    flex-grow: 1;
}

.vip-section .features li {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    color: #ccc;
    font-size: 14px;
}

.vip-section .features i {
    color: #28a745;
    margin-right: 10px;
    font-size: 18px;
}

.vip-section .btn,
.vip-section .btn-extend,
.vip-section .btn-cancel,
.vip-section .btn-secondary {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 16px;
    border: none;
    cursor: pointer;
    margin-top: 10px;
    transition: 0.2s ease;
}

.vip-section .btn {
    background-color: #e50914;
    color: #fff;
}

.vip-section .btn:hover {
    background-color: #b20710;
}

.vip-section .btn-extend {
    background-color: #0a7f0a;
    color: #fff;
}

.vip-section .btn-extend:hover {
    background-color: #066406;
}

.vip-section .btn-cancel {
    background-color: #555;
    color: #fff;
}

.vip-section .btn-cancel:hover {
    background-color: #333;
}

.vip-section .btn-secondary {
    background-color: #555 !important;
}

.vip-section .btn-secondary:hover {
    background-color: #333 !important;
}

.toast-message {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #28a745;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    font-size: 16px;
    z-index: 9999;
    animation: fadeInOut 3s forwards;
}

.toast-message.success { background-color: #28a745; }
.toast-message.error { background-color: #dc3545; }

@keyframes fadeInOut {
    0% { opacity: 0; transform: translate(-50%, 50px); }
    10% { opacity: 1; transform: translate(-50%, 0); }
    90% { opacity: 1; }
    100% { opacity: 0; transform: translate(-50%, 50px); }
}

@keyframes floatPulse {
    0%   { transform: translateY(0); box-shadow: 0 0 10px #28a745; }
    50%  { transform: translateY(-4px); box-shadow: 0 0 20px #28a745; }
    100% { transform: translateY(0); box-shadow: 0 0 10px #28a745; }
}

.animate-vip-status {
    animation: floatPulse 2.5s ease-in-out infinite;
}


</style>
</head>
<body>
<?php include 'header.php'; ?>

<?php if (isset($_SESSION['profile_message'])): ?>
    <div class="toast-message <?php echo $_SESSION['profile_message_type']; ?>">
        <?php echo $_SESSION['profile_message']; ?>
    </div>
    <script>
        setTimeout(function() {
            const toast = document.querySelector('.toast-message');
            if (toast) toast.remove();
        }, 3000);
    </script>
    <?php unset($_SESSION['profile_message'], $_SESSION['profile_message_type']); ?>
<?php endif; ?>


<div class="vip-section">
    <div class="container">
        <section class="section-title">
            <h2>Gói VIP NetfixVN</h2>
            <p>Xem phim chất lượng cao, không quảng cáo và nhiều đặc quyền hấp dẫn.</p>
        </section>

            <?php if ($is_user_vip): ?>
                <div class="vip-status animate-vip-status">
                    🎫 Bạn đang sử dụng: <strong><?php echo htmlspecialchars($current_vip_package_name); ?></strong><br>
                    ⏳ Hết hạn: <strong><?php echo date('d/m/Y \l\ú\c H:i', strtotime($vip_expires_at)); ?></strong>
                </div>
            <?php else: ?>
                <div class="vip-status" style="border-color: #ffc107; color: #ffc107;">
                    ⚠️ Gói VIP của bạn đã hết hạn. Hãy chọn lại gói để tiếp tục!
                </div>
            <?php endif; ?>


            <section class="plans" style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center;">
           <?php foreach ($vip_packages as $package): ?>
                <article class="plan-card">
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <p class="plan-desc"><?php echo htmlspecialchars($package['description']); ?></p>

                    <div class="price">
                        <?php if ($package['id'] == 2): ?>
                            <span class="original-price"><?php echo number_format(79000 * 3); ?> VNĐ</span>
                            <span class="discounted-price"><?php echo number_format($package['price']); ?> VNĐ</span>
                        <?php elseif ($package['id'] == 3): ?>
                            <span class="original-price"><?php echo number_format(79000 * 6); ?> VNĐ</span>
                            <span class="discounted-price"><?php echo number_format($package['price']); ?> VNĐ</span>
                        <?php else: ?>
                            <span class="discounted-price"><?php echo number_format($package['price']); ?> VNĐ</span>
                        <?php endif; ?>
                    </div>

                    <ul class="features">
                        <?php foreach ($package['features'] as $feature): ?>
                            <li><i class='bx bx-check-circle'></i><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($is_user_vip): ?>
                        <?php if ($package['id'] == $current_vip_package_id): ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <button class="btn current-btn" disabled>Đang sử dụng</button>
                                <form method="POST" id="cancelForm">
                                    <input type="hidden" name="cancel_vip" value="1">
                                    <button type="button" class="btn btn-cancel" id="cancelBtn">Hủy gói</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <button type="button" class="btn btn-disabled" onclick="showVipBlockAlert()">Chọn gói</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="checkout.php?package_id=<?php echo $package['id']; ?>" class="btn">Chọn gói</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Xử lý xác nhận -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('cancelBtn')?.addEventListener('click', function () {
    Swal.fire({
        title: 'Huỷ VIP',
        text: 'Bạn có chắc muốn huỷ gói VIP?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Huỷ VIP',
        cancelButtonText: 'Đóng'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancelForm').submit();
        }
    });
});
</script>
<script>
function showVipBlockAlert() {
    Swal.fire({
        title: 'Không thể chọn gói mới',
        text: 'Bạn cần huỷ gói VIP hiện tại trước khi chọn gói khác.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}
</script>



<?php include 'footer.php'; ?>
</body>
</html>
