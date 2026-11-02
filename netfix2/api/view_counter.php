<?php
require '../db.php';

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id > 0) {
    $stmt = $conn->prepare("UPDATE movies SET view_count = view_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'movie_id' => $movie_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'ID không hợp lệ']);
}
?>
