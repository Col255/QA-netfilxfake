<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý xóa lịch sử nếu có movie_id trên URL
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $movie_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM watch_history WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $user_id, $movie_id);
    $stmt->execute();
    $stmt->close();
    header("Location: watch_history.php");
    exit();
}

// Lấy lịch sử xem
$sql = "
    SELECT m.id AS movie_id, m.title, m.thumbnail, m.view_count, wh.viewed_at
    FROM watch_history wh
    JOIN movies m ON wh.movie_id = m.id
    WHERE wh.user_id = ?
    ORDER BY wh.viewed_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$watch_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử xem - NetfixVN</title>
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
        .main-wrapper {
            display: flex;
        }

        .watch-container {
            flex: 1;
            margin-left: 100px;
            padding: 20px;
        }

        .watch-container h2 {
            color: #e50914;
            margin-bottom: 20px;
        }

        .watch-item {
            display: flex;
            align-items: center;
            background-color: #2c2c2c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .watch-item img {
            width: 120px;
            height: auto;
            margin-right: 20px;
            border-radius: 6px;
        }

        .watch-item h3 {
            margin: 0;
            color: #fff;
        }

        .watch-item p {
            margin: 5px 0;
            color: #bbb;
        }

        .actions {
            margin-top: 10px;
        }

        .watch-btn, .delete-btn {
            display: inline-block;
            padding: 8px 15px;
            font-size: 14px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .watch-btn {
            background-color: #e50914;
        }

        .watch-btn:hover {
            background-color: #b20710;
        }

        .delete-btn {
            background-color: #6c757d;
        }

        .delete-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="main-wrapper">
    <?php include 'user_sidebar.php'; ?>
    <div class="watch-container">
        <h2>🕓 Lịch sử xem</h2>

        <?php if (empty($watch_history)): ?>
            <p>Bạn chưa xem phim nào.</p>
        <?php else: ?>
            <?php foreach ($watch_history as $item): ?>
                <div class="watch-item">
                    <img src="<?= htmlspecialchars($item['thumbnail']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div>
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p>🕒 Xem lúc: <?= date('d/m/Y H:i', strtotime($item['viewed_at'])) ?></p>
                        <p>👁 Lượt xem: <?= $item['view_count'] ?></p>
                        <div class="actions">
                            <a class="watch-btn" href="watch.php?id=<?= $item['movie_id'] ?>">▶️ Xem tiếp</a>
                            <a class="delete-btn" href="watch_history.php?delete=<?= $item['movie_id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa phim này khỏi lịch sử không?')">🗑 Xóa</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
