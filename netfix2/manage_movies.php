<?php
session_start();
require 'db.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

function getAllMovies($conn) {
    $stmt = $conn->prepare("SELECT id, title, thumbnail, genre, year, is_vip, view_count FROM movies ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

$movies = getAllMovies($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $success_message = "Xóa phim thành công!";
        $movies = getAllMovies($conn);
    } else {
        $error_message = "Lỗi khi xóa phim: " . $stmt->error;
    }
    $stmt->close();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Quản Lý Phim</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
.dashboard-wrapper {
    display: flex;
    max-width: 1400px;
    margin: 20px auto;
}

.main-content {
    margin-left: 270px;
    padding: 30px;
    width: calc(100% - 270px);
    background-color: #1a1a1a;
    min-height: 100vh;
}

.movie-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #222;
    border-radius: 8px;
    overflow: hidden;
}

.movie-table th, .movie-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #333;
    text-align: left;
    vertical-align: middle;
}

.movie-table th {
    background-color: #e50914;
    color: #fff;
    text-transform: uppercase;
    font-weight: bold;
}

.movie-table tr:hover {
    background-color: #2a2a2a;
}

.action-buttons a,
.action-buttons button {
    padding: 6px 12px;
    margin: 0 5px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s;
}

.edit-btn {
    background: #28a745;
    color: #fff;
}
.edit-btn:hover {
    background: #218838;
}

.delete-btn {
    background: #dc3545;
    color: #fff;
}
.delete-btn:hover {
    background: #b20710;
}

.message {
    text-align: center;
    margin-bottom: 20px;
    font-size: 16px;
}

.success {
    color: #28a745;
}

.error {
    color: #dc3545;
}

    </style>
</head>
<body>
    <div class="dashboard-wrapper">
            <?php include 'admin_sidebar.php' ?>

        <div class="main-content">
            <h2>QUẢN LÝ PHIM</h2>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <table class="movie-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên phim</th>
                        <th>Ảnh</th>
                        <th>Thể loại</th>
                        <th>Năm</th>
                        <th>Loại</th>
                        <th>Lượt xem</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movies)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Chưa có phim nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($movie['id']); ?></td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="max-width: 100px;"></td>
                                <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                                <td><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></td>
                                <td><?php echo $movie['is_vip'] ? 'VIP' : 'Miễn phí'; ?></td>
                                <td><?php echo htmlspecialchars($movie['view_count'] ?: '0'); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_movie.php?id=<?php echo $movie['id']; ?>" class="edit-btn">Sửa</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim này?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $movie['id']; ?>">
                                        <button type="submit" class="delete-btn">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>