<?php
header('Content-Type: application/json; charset=utf-8');
require '../db.php';
/** @var mysqli $conn */

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id > 0) {
    $stmt = $conn->prepare("UPDATE movies SET view_count = view_count + 1 WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'movie_id' => $movie_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Phim không tồn tại']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Lỗi chuẩn bị truy vấn']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID không hợp lệ']);
}
?>
