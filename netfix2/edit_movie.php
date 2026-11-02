<?php
session_start();
require 'db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Lấy movie_id
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movie_id <= 0) die("ID phim không hợp lệ.");

// Lấy thông tin phim
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$movie) die("Phim không tồn tại.");

// Lấy danh sách tập nếu là Series
$episodes = [];
if ($movie['genre'] === 'Series') {
    $stmt = $conn->prepare("SELECT * FROM episodes WHERE movie_id = ? ORDER BY episode_number");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $episodes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? $movie['title'];
    $description = $_POST['description'] ?? $movie['description'];
    $genre = $_POST['genre'] ?? $movie['genre'];
    $sub_genre = $_POST['sub_genre'] ?? $movie['sub_genre'];
    $year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
    $language = $_POST['language'] ?? $movie['language'];
    $country = $_POST['country'] ?? $movie['country'];
    $actors = $_POST['actors'] ?? $movie['actors'];
    $director = $_POST['director'] ?? $movie['director'];
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $status = $_POST['status'] ?? $movie['status'];
    $episode_count = ($genre === 'Series' && isset($_POST['episode_count'])) ? (int)$_POST['episode_count'] : 0;
    $view_count = $movie['view_count'] ?? 0;

    // Thumbnail
    $thumbnail = $movie['thumbnail'];
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnail_name = time() . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = 'Uploads/thumbnails/' . $thumbnail_name;
        if (!is_dir('Uploads/thumbnails')) mkdir('Uploads/thumbnails', 0777, true);
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
            $thumbnail = $thumbnail_path;
        }
    }

    // Video (cho các thể loại không phải Series)
    $video_path = $movie['video_path'];
    if ($genre !== 'Series' && isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_dir = 'Uploads/videos/';
        if (!is_dir($video_dir)) mkdir($video_dir, 0777, true);

        $video_name = time() . '_' . basename($_FILES['video']['name']);
        $video_path_temp = $video_dir . $video_name;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path_temp)) {
            $video_path = $video_path_temp;
        }
    }

    // Cập nhật bảng movies
   $stmt = $conn->prepare("UPDATE movies 
    SET title = ?, thumbnail = ?, description = ?, duration = ?, episode_count = ?, 
        genre = ?, sub_genre = ?, year = ?, language = ?, country = ?, 
        actors = ?, director = ?, status = ?, video_path = ?, 
        updated_at = NOW(), is_vip = ?, view_count = ?
    WHERE id = ?");

    $stmt->bind_param("sssiississssssiii", 
    $title, $thumbnail, $description, $duration, $episode_count,
    $genre, $sub_genre, $year, $language, $country,
    $actors, $director, $status, $video_path,
    $is_vip, $view_count, $movie_id
);




    $stmt->execute();
    $stmt->close();

    // Nếu là phim bộ - xử lý tập phim
    if ($genre === 'Series') {
        if (isset($_POST['episode_numbers'])) {
            foreach ($_POST['episode_numbers'] as $key => $episode_number) {
                $episode_number = (int)$episode_number;
                $episode_title = $_POST['episode_titles'][$key] ?? "Tập $episode_number";
                $episode_id = isset($_POST['episode_ids'][$key]) ? (int)$_POST['episode_ids'][$key] : 0;
                $episode_is_vip = isset($_POST['episode_is_vip'][$key]) ? 1 : 0;

                $has_new_video = isset($_FILES['episodes']['name'][$key]) && $_FILES['episodes']['error'][$key] === UPLOAD_ERR_OK;
                $episode_video_path = '';

                if ($has_new_video) {
                    $video_name = time() . '_' . basename($_FILES['episodes']['name'][$key]);
                    $episode_path = 'Uploads/videos/' . $video_name;

                    if (move_uploaded_file($_FILES['episodes']['tmp_name'][$key], $episode_path)) {
                        $episode_video_path = $episode_path;
                    }
                }

                if ($episode_id > 0) {
                    if ($has_new_video) {
                        $stmt = $conn->prepare("UPDATE episodes SET episode_number = ?, title = ?, video_path = ?, is_vip = ? WHERE id = ? AND movie_id = ?");
                        $stmt->bind_param("sssiii", $episode_number, $episode_title, $episode_video_path, $episode_is_vip, $episode_id, $movie_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE episodes SET episode_number = ?, title = ?, is_vip = ? WHERE id = ? AND movie_id = ?");
                        $stmt->bind_param("ssiii", $episode_number, $episode_title, $episode_is_vip, $episode_id, $movie_id);
                    }
                } else {
                    if (!$has_new_video) continue;
                    $stmt = $conn->prepare("INSERT INTO episodes (movie_id, episode_number, title, video_path, is_vip) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iissi", $movie_id, $episode_number, $episode_title, $episode_video_path, $episode_is_vip);
                }

                $stmt->execute();
                $stmt->close();
            }
        }

        // Xóa tập phim nếu có
        if (isset($_POST['delete_episodes'])) {
            foreach ($_POST['delete_episodes'] as $delete_id) {
                $delete_id = (int)$delete_id;
                $stmt = $conn->prepare("SELECT video_path FROM episodes WHERE id = ? AND movie_id = ?");
                $stmt->bind_param("ii", $delete_id, $movie_id);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($res && file_exists($res['video_path'])) {
                    unlink($res['video_path']);
                }

                $stmt = $conn->prepare("DELETE FROM episodes WHERE id = ? AND movie_id = ?");
                $stmt->bind_param("ii", $delete_id, $movie_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Chuyển hướng về lại trang sửa với thông báo
    header("Location: edit_movie.php?id=$movie_id&updated=1");
    exit();
}

// Hiển thị thông báo nếu có
$success_message = '';
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $success_message = "🎉 Cập nhật phim thành công!";
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Sửa Phim</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: 'Cairo', sans-serif;
        }
        .dashboard-wrapper {
            display: flex;
            max-width: 1400px;
            margin: 20px auto;
        }
        .sidebar {
            width: 250px;
            background-color: #2c2c2c;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar .menu-item {
            padding: 10px 15px;
            margin: 10px 0;
            color: #bbb;
            text-decoration: none;
            display: block;
        }
        .sidebar .menu-item:hover, .sidebar .menu-item.active {
            background-color: #e50914;
            color: #fff;
            border-radius: 4px;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .admin-form { max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; }
        .admin-form h2, .admin-form h3 { color: #fff; text-align: center; margin-bottom: 20px; }
        .admin-form label { color: #fff; display: block; margin: 10px 0 5px; }
        .admin-form input, .admin-form textarea, .admin-form select {
            width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc;
            border-radius: 4px; background: #333; color: #fff;
        }
        .admin-form input[type="file"] { padding: 3px; }
        .admin-form button { background: #e50914; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .admin-form button:hover { background: #b20710; }
        .message { text-align: center; margin-bottom: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .episode-section { display: none; margin-top: 20px; }
        .episode-entry { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; background: #2a2a2a; border-radius: 4px; }
        .episode-entry label { margin: 5px 0; }
        .add-episode-btn { background: #555; padding: 8px; margin: 10px 0; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .add-episode-btn:hover { background: #777; }
        .remove-episode-btn { background: #dc3545; padding: 5px 10px; margin-left: 10px; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .remove-episode-btn:hover { background: #b20710; }
        #episode-count-section { display: none; }
        .error-list { color: #dc3545; font-size: 0.9em; margin-top: 5px; }
        .toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0 15px;
    font-weight: 500;
    color: #ccc;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0;
    right: 0; bottom: 0;
    background-color: #555;
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #e50914;
}

input:checked + .slider:before {
    transform: translateX(22px);
}
/* Nút chính (Lưu, Submit) */
.btn-primary {
    background: linear-gradient(to right, #e50914, #b20710);
    color: #fff;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(229, 9, 20, 0.3);
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background: linear-gradient(to right, #b20710, #e50914);
    transform: translateY(-1px);
}

/* Nút thêm tập */
.btn-add {
    background-color: #444;
    color: #fff;
    padding: 10px 20px;
    border: 1px dashed #999;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-add:hover {
    background-color: #666;
    transform: scale(1.03);
}

/* Nút xóa */
.btn-delete {
    background-color: transparent;
    color: #ff5c5c;
    border: 1px solid #ff5c5c;
    padding: 6px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-delete:hover {
    background-color: #ff5c5c;
    color: #1a1a1a;
}

    </style>
</head>
<body>
   <div class="dashboard-wrapper">
    <?php include 'admin_sidebar.php' ?>
    <div class="main-content">
        <div class="admin-form">
            <h2>Sửa Phim</h2>
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?= $success_message ?></div>
            <?php endif; ?>
            <form action="edit_movie.php?id=<?= $movie_id ?>" method="POST" enctype="multipart/form-data">

                <label>Tên phim</label>
                <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>

                <label>Mô tả</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($movie['description']) ?></textarea>

                <label>Thể loại</label>
                <select name="genre" id="genre" onchange="toggleEpisodeSection()">
                    <option value="Movie" <?= $movie['genre'] === 'Movie' ? 'selected' : '' ?>>Phim lẻ</option>
                    <option value="Series" <?= $movie['genre'] === 'Series' ? 'selected' : '' ?>>Phim bộ</option>
                    <option value="Cartoon" <?= $movie['genre'] === 'Cartoon' ? 'selected' : '' ?>>Hoạt hình</option>
                    <option value="Cinema" <?= $movie['genre'] === 'Cinema' ? 'selected' : '' ?>>Phim chiếu rạp</option>
                </select>

                <!-- 4. sub_genre -->
                <label>Thể loại phụ</label>
                <input type="text" name="sub_genre" value="<?= htmlspecialchars($movie['sub_genre']) ?>">

                <!-- 5. year -->
                <label>Năm phát hành</label>
                <input type="number" name="year" value="<?= $movie['year'] ?>">

                <!-- 6. duration -->
                <label>Thời lượng (phút)</label>
                <input type="number" name="duration" value="<?= $movie['duration'] ?>">

                <!-- 7. episode_count -->
                <label>Số tập</label>
                <input type="number" name="episode_count" id="episode_count" value="<?= $movie['episode_count'] ?>">

                <!-- 8. language -->
                <label>Ngôn ngữ</label>
                <input type="text" name="language" value="<?= htmlspecialchars($movie['language']) ?>">

                <!-- 9. country -->
                <label>Quốc gia</label>
                <input type="text" name="country" value="<?= htmlspecialchars($movie['country']) ?>">

                <!-- 10. actors -->
                <label>Diễn viên</label>
                <input type="text" name="actors" value="<?= htmlspecialchars($movie['actors']) ?>">

                <!-- 11. director -->
                <label>Đạo diễn</label>
                <input type="text" name="director" value="<?= htmlspecialchars($movie['director']) ?>">

                <!-- 12. status -->
                <label>Trạng thái</label>
                <select name="status">
                    <option value="active" <?= $movie['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                    <option value="inactive" <?= $movie['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn / Ngưng chiếu</option>
                </select>

                <div class="toggle-wrapper">
                    <label for="is_vip_toggle">Phim VIP</label>
                    <label class="switch">
                        <input type="checkbox" name="is_vip" id="is_vip_toggle" value="1" <?= $movie['is_vip'] ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>


                <?php if ($movie['genre'] !== 'Series'): ?>
                <label>Video hiện tại</label>
                <?php if (!empty($movie['video_path'])): ?>
                    <p>
                    <button type="button" class="btn-primary" onclick="openVideoModal('<?= htmlspecialchars($movie['video_path']) ?>')">
                        🎬 Xem video hiện tại
                    </button>
                    </p>
                <?php else: ?>
                    <p><em>Chưa có video</em></p>
                <?php endif; ?>

                <label>Tải lên video mới (nếu thay đổi)</label>
                <input type="file" name="video">
                <small style="color:#999;">(Nếu không chọn, hệ thống sẽ giữ nguyên video hiện tại)</small>
            <?php endif; ?>


                <label>Ảnh đại diện hiện tại</label>
                <?php if (!empty($movie['thumbnail'])): ?>
                    <img src="<?= $movie['thumbnail'] ?>" alt="thumbnail" style="max-width: 200px; display:block; margin-bottom:10px;">
                <?php endif; ?>
                <label>Ảnh đại diện mới (nếu có)</label>
                <input type="file" name="thumbnail">

                <div class="episode-section" id="episode-section">
                    <h3>Danh sách tập phim</h3>
                    <div id="episode-container">
                        <?php foreach ($episodes as $index => $ep): ?>
                            <div class="episode-entry">
                                <input type="hidden" name="episode_ids[]" value="<?= $ep['id'] ?>">

                                <label>Số tập</label>
                                <input type="number" name="episode_numbers[]" value="<?= $ep['episode_number'] ?>" required>

                                <label>Tiêu đề</label>
                                <input type="text" name="episode_titles[]" value="<?= htmlspecialchars($ep['title']) ?>">

                                <?php if (!empty($ep['video_path'])): ?>
                                    <p><strong>Video hiện tại:</strong><br>
                                        <button type="button" class="btn-primary" onclick="openVideoModal('<?= htmlspecialchars($ep['video_path']) ?>')">
                                            🎬 Xem video hiện tại
                                        </button>
                                    </p>
                                <?php endif; ?>

                                <label>Video mới (nếu có)</label>
                                <input type="file" name="episodes[]">

                                <div class="toggle-wrapper">
                                    <label for="episode_is_vip_<?= $index ?>">Tập VIP</label>
                                    <label class="switch">
                                        <input type="checkbox" id="episode_is_vip_<?= $index ?>" name="episode_is_vip[<?= $index ?>]" value="1" <?= $ep['is_vip'] ? 'checked' : '' ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <button type="button" class="btn-delete" onclick="markEpisodeForDeletion(this, <?= $ep['id'] ?>)">🗑 Xóa tập</button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn-add" onclick="addEpisode()">➕ Thêm tập</button>
                </div>

                <div style="text-align:center; margin-top: 20px;">
                    <button type="submit" class="btn-primary">💾 Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="videoModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.8);">
  <div id="videoModalContent" style="position:relative; width:80%; max-width:800px; margin:50px auto; background:#000; padding:20px; border-radius:8px;">
    <button id="closeVideoBtn" style="position:absolute; top:10px; right:15px; background:#e50914; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">✖</button>
    <video id="videoPlayer" controls style="width:100%; max-height:80vh; background:#000;">
      <source src="" type="video/mp4">
      Trình duyệt không hỗ trợ video.
    </video>
  </div>
</div>


<script>
function toggleEpisodeSection() {
    const genre = document.getElementById('genre').value;
    const section = document.getElementById('episode-section');
    section.style.display = genre === 'Series' ? 'block' : 'none';
}

function addEpisode() {
    const container = document.getElementById('episode-container');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'episode-entry';
    div.innerHTML = `
        <label>Số tập</label>
        <input type="number" name="episode_numbers[]" value="${index + 1}" required>

        <label>Tiêu đề</label>
        <input type="text" name="episode_titles[]" placeholder="Tập ${index + 1}">

        <label>Video</label>
        <input type="file" name="episodes[]" required>

        <div class="toggle-wrapper">
            <label for="episode_is_vip_new${index}">Tập VIP</label>
            <label class="switch">
                <input type="checkbox" id="episode_is_vip_new${index}" name="episode_is_vip[]" value="1">
                <span class="slider round"></span>
            </label>
        </div>

        <button type="button" class="btn-delete" onclick="this.closest('.episode-entry').remove()">🗑 Xóa tập</button>
    `;
    container.appendChild(div);
}

function markEpisodeForDeletion(btn, episodeId) {
    const entry = btn.closest('.episode-entry');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_episodes[]';
    input.value = episodeId;
    document.querySelector('form').appendChild(input);
    entry.remove();
}

function openVideoModal(url) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    player.src = url;
    modal.style.display = 'block';
}

function closeVideoModal(event) {
    if (event) event.stopPropagation();
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    modal.style.display = 'none';
    player.pause();
    player.src = '';
}

document.addEventListener('DOMContentLoaded', () => {
    toggleEpisodeSection();

    // Click ra ngoài nội dung video thì đóng modal
    const modal = document.getElementById('videoModal');
    const modalContent = document.getElementById('modalContent');
    modal.addEventListener('click', function (e) {
        if (!modalContent.contains(e.target)) {
            closeVideoModal();
        }
    });
});
</script>

<script>
function openVideoModal(url) {
    const modal = document.getElementById("videoModal");
    const player = document.getElementById("videoPlayer");

    player.src = url;
    modal.style.display = "block";
}

function closeVideoModal() {
    const modal = document.getElementById("videoModal");
    const player = document.getElementById("videoPlayer");

    modal.style.display = "none";
    player.pause();
    player.currentTime = 0;
    player.src = ""; // reset source
}

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("videoModal");
    const modalContent = document.getElementById("videoModalContent");
    const closeBtn = document.getElementById("closeVideoBtn");

    // Nút ✖
    closeBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        closeVideoModal();
    });

    // Click ra ngoài nội dung thì đóng
    modal.addEventListener("click", function (e) {
        if (!modalContent.contains(e.target)) {
            closeVideoModal();
        }
    });
});
</script>

</body>
</html>

        