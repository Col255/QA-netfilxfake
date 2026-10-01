<?php
session_start();
require 'db.php'; // Điều chỉnh đường dẫn đến db.php nếu cần (ví dụ: ../db.php nếu orders.php nằm trong thư mục con admin)

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Xử lý cập nhật trạng thái đơn hàng (cho admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action']; // 'complete' hoặc 'cancel'

    $new_status = '';
    if ($action === 'complete') {
        $new_status = 'Completed';
    } elseif ($action === 'cancel') {
        $new_status = 'Cancelled';
    }

    if ($new_status) {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $conn->begin_transaction();
        try {
            // 1. Cập nhật trạng thái đơn hàng
            $stmt_update_order = $conn->prepare("UPDATE vip_orders SET status = ? WHERE id = ?");
            if (!$stmt_update_order) {
                throw new Exception("Lỗi chuẩn bị truy vấn cập nhật đơn hàng: " . $conn->error);
            }
            $stmt_update_order->bind_param("si", $new_status, $order_id);
            if (!$stmt_update_order->execute()) {
                throw new Exception("Lỗi khi cập nhật trạng thái đơn hàng: " . $stmt_update_order->error);
            }
            $stmt_update_order->close();

            // 2. Nếu đơn hàng được hoàn tất, cập nhật is_vip và vip_expires_at cho người dùng
            if ($new_status === 'Completed') {
                $stmt_get_order_info = $conn->prepare("SELECT user_id, package_name FROM vip_orders WHERE id = ?");
                if (!$stmt_get_order_info) {
                    throw new Exception("Lỗi chuẩn bị truy vấn lấy thông tin đơn hàng: " . $conn->error);
                }
                $stmt_get_order_info->bind_param("i", $order_id);
                $stmt_get_order_info->execute();
                $result_order_info = $stmt_get_order_info->get_result();
                $order_data = $result_order_info->fetch_assoc();
                $stmt_get_order_info->close();

                if ($order_data && $order_data['user_id']) {
                    $user_id_to_update = $order_data['user_id'];
                    $package_name = $order_data['package_name'];
                    $vip_duration = '1 month'; // Mặc định, sẽ được cập nhật dựa vào tên gói
                    
                    // Xác định thời hạn VIP dựa vào tên gói (phải khớp với pricing.php)
                    if (strpos($package_name, '1 Tháng') !== false) {
                        $vip_duration = '1 month';
                    } elseif (strpos($package_name, '6 Tháng') !== false) {
                        $vip_duration = '6 months';
                    } elseif (strpos($package_name, '1 Năm') !== false) {
                        $vip_duration = '1 year';
                    }

                    // Lấy thời điểm hiện tại hoặc thời điểm hết hạn VIP cũ nếu có
                    $current_vip_expires_at = null;
                    $stmt_get_user_vip_info = $conn->prepare("SELECT vip_expires_at FROM users WHERE id = ?");
                    if (!$stmt_get_user_vip_info) {
                        throw new Exception("Lỗi chuẩn bị truy vấn lấy VIP người dùng: " . $conn->error);
                    }
                    $stmt_get_user_vip_info->bind_param("i", $user_id_to_update);
                    $stmt_get_user_vip_info->execute();
                    $result_user_vip_info = $stmt_get_user_vip_info->get_result();
                    if ($row = $result_user_vip_info->fetch_assoc()) {
                        $current_vip_expires_at = $row['vip_expires_at'];
                    }
                    $stmt_get_user_vip_info->close();

                    $start_time_timestamp = time(); // Thời điểm hiện tại
                    if ($current_vip_expires_at && strtotime($current_vip_expires_at) > $start_time_timestamp) {
                        // Nếu VIP cũ vẫn còn hạn, cộng dồn từ thời điểm hết hạn cũ
                        $start_time_timestamp = strtotime($current_vip_expires_at);
                    }
                    
                    // Tính toán ngày hết hạn VIP mới
                    $new_vip_expires_at = date('Y-m-d H:i:s', strtotime("+$vip_duration", $start_time_timestamp));

                    $stmt_update_user_vip = $conn->prepare("UPDATE users SET is_vip = 1, vip_expires_at = ? WHERE id = ?");
                    if (!$stmt_update_user_vip) {
                        throw new Exception("Lỗi chuẩn bị truy vấn cập nhật VIP người dùng: " . $conn->error);
                    }
                    $stmt_update_user_vip->bind_param("si", $new_vip_expires_at, $user_id_to_update);
                    if (!$stmt_update_user_vip->execute()) {
                        throw new Exception("Lỗi khi cập nhật trạng thái VIP người dùng: " . $stmt_update_user_vip->error);
                    }
                    $stmt_update_user_vip->close();
                }
            }

            $conn->commit();
            $success_message = "Cập nhật trạng thái đơn hàng thành công!";

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Lỗi: " . $e->getMessage();
        }
    }
}
$user_filter_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$orders = getAllVipOrders($conn, $user_filter_id);

// Hàm lấy danh sách đơn hàng VIP từ database
function getAllVipOrders($conn, $user_id = null) {
    $query = "
        SELECT vo.id, u.username, vo.package_name, vo.price, vo.payment_method, vo.status, vo.order_date
        FROM vip_orders vo
        JOIN users u ON vo.user_id = u.id
    ";
    if ($user_id) {
        $query .= " WHERE vo.user_id = ?";
    }
    $query .= " ORDER BY vo.order_date DESC";

    $stmt = $conn->prepare($query);
    if ($user_id) {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}


$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Quản lý Đơn hàng VIP</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="grid.css"> <link rel="stylesheet" href="app.css"> 
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
    margin: 30px auto;
    padding: 20px;
}

.main-content {
    margin-left: 270px;
    padding: 30px;
    width: calc(100% - 270px);
    animation: fadeIn 0.3s ease-in-out;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background-color: #2a2a2a;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.order-table th {
    background-color: #e50914;
    padding: 14px 12px;
    text-align: left;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-table td {
    padding: 12px;
    border-bottom: 1px solid #333;
    font-size: 14px;
    color: #ddd;
}

.order-table tr:hover {
    background-color: #1f1f1f;
}

.action-buttons button {
    padding: 6px 14px;
    margin: 0 4px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s ease;
}

.message {
    text-align: center;
    margin-bottom: 25px;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.success {
    background-color: #28a745;
    color: #fff;
}
.error {
    background-color: #dc3545;
    color: #fff;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
   
        form {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

form label {
    color: #fff;
    font-weight: 600;
    font-size: 15px;
}

form select {
    background-color: #2a2a2a;
    color: #fff;
    border: 1px solid #444;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    transition: border 0.3s ease;
}

form select:focus {
    outline: none;
    border-color: #e50914;
}

.status-Completed {
    color: #28a745; /* Xanh lá */
    font-weight: bold;
}
.status-Completed::before {
    content: "✅ ";
}

.status-Cancelled {
    color: #dc3545; /* Đỏ */
    font-weight: bold;
}
.status-Cancelled::before {
    content: "❌ ";
}

.status-Failed {
    color: #ff4d4d; /* Đỏ tươi */
    font-weight: bold;
}
.status-Failed::before {
    content: "⚠️ ";
}


</style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'admin_sidebar.php' ?>

        <div class="main-content">
            <h2>QUẢN LÍ ĐƠN HÀNG VIP</h2>

            <?php if (!empty($success_message)): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php elseif (!empty($error_message)): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="GET" style="margin-bottom: 20px;">
    <label for="user_filter">Lọc theo người dùng:</label>
    <select name="user_id" id="user_filter" onchange="this.form.submit()">
        <option value="">-- Tất cả --</option>
        <?php
        // Lấy danh sách người dùng đã từng mua VIP
        $stmt_users = $conn->query("SELECT DISTINCT u.id, u.username 
                                    FROM users u 
                                    JOIN vip_orders vo ON vo.user_id = u.id 
                                    ORDER BY u.username ASC");
        while ($user = $stmt_users->fetch_assoc()):
        ?>
            <option value="<?php echo $user['id']; ?>" <?php if (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($user['username']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

            <table class="order-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên khách hàng</th>
                        <th>Gói đăng ký</th>
                        <th>Giá</th>
                        <th>Hình thức TT</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Chưa có đơn hàng VIP nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                                <td><?php echo number_format($order['price']); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td class="status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="../app.js"></script> </body>
</html>