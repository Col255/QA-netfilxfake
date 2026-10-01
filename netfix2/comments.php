<?php
session_start();
require 'db.php'; // Điều chỉnh đường dẫn đến file kết nối database nếu comments.php không nằm trong thư mục con của thư mục gốc

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php"); // Chuyển hướng đến trang đăng nhập admin
    exit();
}

// --- Hàm lấy tất cả bình luận ---
/**
 * Lấy tất cả bình luận từ database, bao gồm thông tin phim và người dùng.
 *
 * @param mysqli $conn Đối tượng kết nối MySQLi.
 * @return array Mảng chứa các bình luận.
 */
function getAllComments($conn) {
    // JOIN với bảng movies và users để lấy tên phim và tên người dùng
    $stmt = $conn->prepare("SELECT c.id, c.comment_text, c.created_at, m.title AS movie_title, u.username AS user_username
                            FROM comments c
                            JOIN movies m ON c.movie_id = m.id
                            JOIN users u ON c.user_id = u.id
                            ORDER BY c.created_at DESC"); // Sắp xếp theo bình luận mới nhất
    if (!$stmt) {
        error_log("Prepare failed (getAllComments): " . $conn->error);
        return [];
    }
    if (!$stmt->execute()) {
        error_log("Execute failed (getAllComments): " . $stmt->error);
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $comments;
}

// --- Xử lý yêu cầu xóa bình luận ---
$message = ''; // Biến để lưu thông báo thành công/lỗi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $delete_id = intval($_POST['delete_comment_id']); // Ép kiểu sang số nguyên
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed (delete comment): " . $conn->error);
        $message = "<div class='message error'>Lỗi hệ thống khi chuẩn bị xóa bình luận.</div>";
    } else {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Bình luận đã được xóa thành công!</div>";
        } else {
            error_log("Execute failed (delete comment): " . $stmt->error);
            $message = "<div class='message error'>Lỗi khi xóa bình luận: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Lấy danh sách tất cả bình luận sau khi có thể đã xóa hoặc mới vào trang
$comments = getAllComments($conn);

// Dùng cho việc đánh dấu active trên sidebar (đảm bảo sidebar của bạn cũng có link đến comments.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Quản Lý Bình Luận</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../grid.css">
    <link rel="stylesheet" href="../app.css">
    <style>
        /* CSS cho trang admin (đã có từ các ví dụ trước của bạn) */
        body { background-color: #1a1a1a; color: #fff; font-family: 'Cairo', sans-serif; }
        .dashboard-wrapper { display: flex; max-width: 1400px; margin: 20px auto; }
        .sidebar { width: 250px; background-color: #2c2c2c; padding: 20px; height: 100vh; position: fixed; }
        .sidebar .menu-item { padding: 10px 15px; margin: 10px 0; color: #bbb; text-decoration: none; display: block; }
        .sidebar .menu-item:hover, .sidebar .menu-item.active { background-color: #e50914; color: #fff; border-radius: 4px; }
        .main-content { margin-left: 270px; padding: 20px; width: calc(100% - 270px); }

        /* CSS riêng cho bảng bình luận */
        .comment-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .comment-table th, .comment-table td { padding: 12px; border: 1px solid #444; text-align: left; vertical-align: top; }
        .comment-table th { background-color: #e50914; color: #fff; }
        .comment-table tr:nth-child(even) { background-color: #2a2a2a; } /* Màu nền xen kẽ */
        .comment-table tr:hover { background-color: #3a3a3a; } /* Hiệu ứng hover */

        .action-buttons button {
            padding: 8px 12px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }
        .delete-btn { background: #dc3545; color: #fff; } /* Màu đỏ cho nút xóa */
        .delete-btn:hover { background: #b20710; }

        .message { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .success { background-color: #28a745; color: #fff; } /* Màu xanh lá cho thông báo thành công */
        .error { background-color: #dc3545; color: #fff; } /* Màu đỏ cho thông báo lỗi */
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'admin_sidebar.php' ?>

        <div class="main-content">
            <h2>Quản Lý Bình Luận</h2>

            <?php echo $message; // Hiển thị thông báo xóa bình luận ?>

            <table class="comment-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phim</th>
                        <th>Người dùng</th>
                        <th>Nội dung bình luận</th>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Chưa có bình luận nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comment['id']); ?></td>
                                <td><?php echo htmlspecialchars($comment['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($comment['user_username']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                                        <input type="hidden" name="delete_comment_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../app.js"></script> </body>
</html>