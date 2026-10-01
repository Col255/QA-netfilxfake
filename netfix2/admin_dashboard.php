<?php
session_start();
require 'db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// === Lấy Top 10 phim có lượt xem cao nhất ===
function getTop10Movies($conn) {
    $stmt = $conn->prepare("SELECT id, title, thumbnail, genre, year, is_vip, view_count 
                            FROM movies 
                            ORDER BY view_count DESC 
                            LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

// === Thống kê số lượng tài khoản VIP ===
function getVipCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_vip = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

// === Tổng số khách hàng ===
function getTotalCustomers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

// === Doanh thu từ gói VIP đã thanh toán ===
function getVipRevenue($conn) {
    $stmt = $conn->prepare("SELECT SUM(price) as total FROM vip_orders WHERE status = 'Completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    return $total;
}

// === Doanh thu tổng (mặc định bằng doanh thu VIP) ===
function getTotalRevenue($conn) {
    return getVipRevenue($conn);
}

// === Lấy dữ liệu ===
$movies = getTop10Movies($conn);
$vipCount = getVipCount($conn);
$totalCustomers = getTotalCustomers($conn);
$vipRevenue = getVipRevenue($conn);
$totalRevenue = getTotalRevenue($conn);

// Lấy trang hiện tại
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>NetfixVN - Thống kê hệ thống</title>
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;600;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
    background-color: #121212;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    margin: 0;
}

.dashboard-wrapper {
    display: flex;
    max-width: 1400px;
    margin: 0 auto;
}

.main-content {
    margin-left: 270px;
    padding: 30px;
    width: calc(100% - 270px);
}

h2 {
    font-size: 28px;
    margin-bottom: 25px;
}

.kpi-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.kpi-box {
    background-color: #1f1f1f;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    transition: transform 0.2s;
}

.kpi-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}

.kpi-box h3 {
    margin: 0 0 10px;
    font-size: 15px;
    color: #bbb;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-box p {
    font-size: 22px;
    color: #fff;
    font-weight: bold;
    margin: 0;
}

.chart-container {
    background-color: #1f1f1f;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    animation: fadeIn 0.5s ease-in-out;
}

.chart-container h3 {
    margin-bottom: 20px;
    font-size: 20px;
    color: #f1f1f1;
    font-weight: 600;
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
        <h2>📊 TỔNG QUAN</h2>

        <div class="kpi-container">
            <div class="kpi-box">
                <h3>Tài khoản VIP</h3>
                <p><?php echo $vipCount; ?></p>
            </div>
            <div class="kpi-box">
                <h3>Doanh thu từ VIP</h3>
                <p><?php echo number_format($vipRevenue); ?> VNĐ</p>
            </div>
            <div class="kpi-box">
                <h3>Doanh thu tổng</h3>
                <p><?php echo number_format($totalRevenue); ?> VNĐ</p>
            </div>
            <div class="kpi-box">
                <h3>Tổng người dùng</h3>
                <p><?php echo $totalCustomers; ?></p>
            </div>
        </div>

        <div class="chart-container">
            <h3>🎬 Top 10 Phim Thịnh Hành (Theo lượt xem)</h3>
            <canvas id="topMoviesChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('topMoviesChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($movies, 'title')); ?>,
            datasets: [{
                label: 'Lượt xem',
                data: <?php echo json_encode(array_column($movies, 'view_count')); ?>,
                backgroundColor: '#e50914'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#fff' }
                },
                x: {
                    ticks: { color: '#fff' }
                }
            }
        }
    });
</script>
</body>
</html>
