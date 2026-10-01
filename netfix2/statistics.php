<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$package_filter = $_GET['package'] ?? '';

$sql = "SELECT * FROM vip_orders WHERE status = 'Completed' AND DATE(order_date) BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($package_filter !== '') {
    $sql .= " AND package_name = ?";
    $params[] = $package_filter;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$total_revenue = 0;
$order_counts = 0;
$daily_chart = [];
$package_breakdown = [];
foreach ($orders as $order) {
    $total_revenue += $order['price'];
    $order_counts++;
    $day = date('Y-m-d', strtotime($order['order_date']));
    if (!isset($daily_chart[$day])) $daily_chart[$day] = 0;
    $daily_chart[$day] += $order['price'];

    $pkg = $order['package_name'];
    if (!isset($package_breakdown[$pkg])) $package_breakdown[$pkg] = 0;
    $package_breakdown[$pkg]++;
}

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_vip = 1 AND vip_expires_at > NOW()");
$stmt->execute();
$active_vip = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE vip_expires_at < NOW() AND is_vip = 1");
$stmt->execute();
$expired_vip = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$new_users = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$current_page = basename($_SERVER['PHP_SELF']);
$packages = ['Gói VIP 1 Tháng', 'Gói VIP 6 Tháng', 'Gói VIP 1 Năm'];

// Lấy 4 phim có lượt xem cao nhất
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT m.*, COUNT(w.id) AS total_views
    FROM movies m
    JOIN watch_history w ON w.movie_id = m.id
    WHERE DATE(w.watch_date) BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY total_views DESC
    LIMIT 4
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_trending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê doanh thu VIP - NetfixVN</title>
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;600;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
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
    width: calc(100% - 270px);
    padding: 20px 30px;
    animation: fadeIn 0.3s ease-in-out;
}

.kpi-container {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.kpi-box {
    flex: 1;
    background: #2c2c2c;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease;
}
.kpi-box:hover {
    transform: translateY(-4px);
}

.kpi-box h3 {
    font-size: 14px;
    color: #bbb;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-box p {
    font-size: 22px;
    color: #fff;
    font-weight: bold;
    margin: 0;
}

.filter-form {
    margin-bottom: 30px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-form input,
.filter-form select,
.filter-form button {
    padding: 10px 14px;
    border-radius: 6px;
    border: none;
    background: #1f1f1f;
    color: #fff;
    font-size: 14px;
}

.filter-form button {
    background-color: #e50914;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}
.filter-form button:hover {
    background-color: #b00610;
}

.chart-container {
    background-color: #2c2c2c;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 40px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

.package-breakdown {
    margin-top: 30px;
    background: #2c2c2c;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

.package-breakdown h3 {
    color: #e50914;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.package-breakdown h3::before {
    content: "🔥";
    font-size: 22px;
}

.package-breakdown ul {
    list-style: none;
    padding-left: 0;
    color: #ccc;
    font-size: 15px;
    line-height: 1.7;
}

.trending-wrapper {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.trending-card {
    background: #1f1f1f;
    padding: 10px;
    border-radius: 10px;
    width: 220px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.trending-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.08);
}

.trending-thumb {
    width: 100%;
    aspect-ratio: 2/3;
    overflow: hidden;
    border-radius: 8px;
    margin-bottom: 10px;
}
.trending-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.trending-title {
    color: #fff;
    font-size: 16px;
    margin: 6px 0;
    font-weight: 600;
}

.trending-views {
    color: #aaa;
    font-size: 14px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.filter-form {
    margin-bottom: 40px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    background: #262626;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

.filter-form label {
    color: #f1f1f1;
    font-size: 16px;
    font-weight: 600;
}

.filter-form input[type="date"] {
    padding: 12px 16px;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    background: #fff;
    color: #000;
    font-weight: 500;
    margin-left: 8px;
}

.filter-form button {
    padding: 12px 20px;
    font-size: 15px;
    background-color: #e50914;
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
    box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
}

.filter-form button:hover {
    background-color: #b00610;
}

    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include 'admin_sidebar.php' ?>


    <div class="main-content">
        <h2>📊 THỐNG KÊ </h2>

        <form method="GET" class="filter-form">
            <label>Từ: <input type="date" name="start_date" value="<?php echo $start_date; ?>"></label>
            <label>Đến: <input type="date" name="end_date" value="<?php echo $end_date; ?>"></label>
            <button type="submit">Lọc</button>
        </form>

        <div class="kpi-container">
            <div class="kpi-box">
                <h3>Doanh thu VIP</h3>
                <p><?php echo number_format($total_revenue); ?> VNĐ</p>
            </div>
            <div class="kpi-box">
                <h3>Số đơn hoàn tất</h3>
                <p><?php echo $order_counts; ?></p>
            </div>
            <div class="kpi-box">
                <h3>Người còn VIP</h3>
                <p><?php echo $active_vip; ?></p>
            </div>
            <div class="kpi-box">
                <h3>Tài khoản mới</h3>
                <p><?php echo $new_users; ?></p>
            </div>
        </div>

        <div class="chart-container">
            <h3>📈 Doanh thu theo ngày</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>

        <div class="package-breakdown">
            <h3>📦 Số đơn theo từng gói</h3>
            <ul>
                <?php foreach ($package_breakdown as $pkg => $count): ?>
                    <li><?php echo $pkg . ": <strong>" . $count . " đơn</strong>"; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
            <div class="package-breakdown">
                <h3>🔥 Top Trending Phim</h3>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <?php foreach ($top_trending as $movie): ?>
                        <div style="background: #1f1f1f; padding: 10px; border-radius: 8px; width: 220px; text-align: center; display: flex; flex-direction: column; height: 360px;">
                            
                            <!-- Ảnh cố định tỷ lệ -->
                            <div style="width: 100%; aspect-ratio: 2/3; overflow: hidden; border-radius: 6px;">
                                <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" 
                                    alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                    style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                            </div>

                            <!-- Tiêu đề phim -->
                            <div style="flex-grow: 1;">
                                <h4 style="color: #fff; margin-top: 10px; min-height: 40px; font-size: 16px;">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </h4>
                            </div>

                            <!-- Lượt xem -->
                             <p style="color: #bbb;">
                                <?php echo number_format($movie['total_views']); ?> lượt xem
                            </p>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_keys($daily_chart)); ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?php echo json_encode(array_values($daily_chart)); ?>,
            fill: true,
            backgroundColor: 'rgba(229, 9, 20, 0.1)',
            borderColor: '#e50914',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#fff' } }
        },
        scales: {
            x: { ticks: { color: '#fff' } },
            y: { ticks: { color: '#fff' }, beginAtZero: true }
        }
    }
});
</script>
</body>
</html>