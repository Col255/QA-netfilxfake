<?php
session_start();
require 'db.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Xử lý form khi được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $sub_genre = $_POST['sub_genre'] ?? '';
    $year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : null;
    $duration = isset($_POST['duration']) && $_POST['duration'] !== '' ? (int)$_POST['duration'] : null;
    $language = $_POST['language'] ?? '';
    $country = $_POST['country'] ?? '';
    $actors = $_POST['actors'] ?? '';
    $director = $_POST['director'] ?? '';
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    $episode_count = ($genre === 'Series' && isset($_POST['episode_count']) && $_POST['episode_count'] !== '') ? (int)$_POST['episode_count'] : null;

    // Xử lý upload thumbnail
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnail_dir = 'Uploads/thumbnails/';
        if (!is_dir($thumbnail_dir)) mkdir($thumbnail_dir, 0777, true);
        $thumbnail_name = time() . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = $thumbnail_dir . $thumbnail_name;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
            $thumbnail = 'Uploads/thumbnails/' . $thumbnail_name;
        } else {
            $error_message = "Lỗi khi upload ảnh thumbnail.";
        }
    } else {
        $error_message = "Vui lòng chọn ảnh thumbnail.";
    }

    // Xử lý upload video (cho phim lẻ, hoạt hình, hoặc phim chiếu rạp)
    $video_path = '';
    if ($genre !== 'Series' && isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_dir = 'Uploads/videos/';
        if (!is_dir($video_dir)) mkdir($video_dir, 0777, true);
        $video_name = time() . '_' . basename($_FILES['video']['name']);
        $video_path = $video_dir . $video_name;
        if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
            $video_path = 'Uploads/videos/' . $video_name;
        } else {
            $error_message = "Lỗi khi upload video.";
        }
    }

    // Lưu phim vào bảng movies
    if (!isset($error_message)) {
        $stmt = $conn->prepare("INSERT INTO movies (title, thumbnail, description, duration, episode_count, genre, sub_genre, year, language, country, actors, director, status, video_path, is_vip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $duration = $duration ?? 0;
        $episode_count = $episode_count ?? 0;
        $year = $year ?? 0;

        $stmt->bind_param("ssssisssssssssi", $title, $thumbnail, $description, $duration, $episode_count, $genre, $sub_genre, $year, $language, $country, $actors, $director, $status, $video_path, $is_vip);

        if ($stmt->execute()) {
            $movie_id = $conn->insert_id;

            // Xử lý các tập cho phim bộ
            if ($genre === 'Series' && isset($_FILES['episodes']) && is_array($_FILES['episodes']['name'])) {
                $episode_dir = 'Uploads/videos/';
                if (!is_dir($episode_dir)) mkdir($episode_dir, 0777, true);

                foreach ($_FILES['episodes']['name'] as $key => $name) {
                    if ($_FILES['episodes']['error'][$key] === UPLOAD_ERR_OK) {
                        $episode_number = isset($_POST['episode_numbers'][$key]) ? (int)$_POST['episode_numbers'][$key] : ($key + 1);
                        $episode_title = $_POST['episode_titles'][$key] ?? "Tập $episode_number";
                        $episode_is_vip = isset($_POST['episode_is_vip'][$key]) ? 1 : 0;

                        $episode_name = time() . '_' . basename($name);
                        $episode_path = $episode_dir . $episode_name;

                        if (move_uploaded_file($_FILES['episodes']['tmp_name'][$key], $episode_path)) {
                            $episode_video_path = 'Uploads/videos/' . $episode_name;

                            $episode_stmt = $conn->prepare("INSERT INTO episodes (movie_id, episode_number, title, video_path, is_vip) VALUES (?, ?, ?, ?, ?)");
                            $episode_stmt->bind_param("iissi", $movie_id, $episode_number, $episode_title, $episode_video_path, $episode_is_vip);
                            $episode_stmt->execute();
                            $episode_stmt->close();
                        }
                    }
                }
            }

        header("Location: add_movie.php?added=1");
        exit();
        } else {
            $error_message = "Lỗi khi thêm phim: " . $stmt->error;
        }

        $stmt->close();
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetfixVN - Thêm Phim</title>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- BOX ICONS -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <!-- APP CSS -->
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="app.css">
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
    margin: 20px auto;
}

.main-content {
    margin-left: 270px;
    padding: 30px;
    width: calc(100% - 270px);
    background-color: #1a1a1a;
    min-height: 100vh;
}

.admin-form {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #1e1e1e;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
}

.admin-form h2 {
    color: #fff;
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
    font-size: 24px;
}

.admin-form label {
    display: block;
    margin: 12px 0 6px;
    font-weight: 500;
    color: #ccc;
}

.admin-form input,
.admin-form textarea,
.admin-form select {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #444;
    border-radius: 6px;
    background: #2c2c2c;
    color: #fff;
    font-size: 15px;
    transition: border 0.3s ease;
}

.admin-form input:focus,
.admin-form textarea:focus,
.admin-form select:focus {
    border-color: #e50914;
    outline: none;
}

.admin-form input[type="file"] {
    padding: 5px;
    background-color: transparent;
}

.admin-form button {
    background: #e50914;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 15px;
    transition: background 0.3s ease;
}

.admin-form button:hover {
    background: #b20710;
}

.message {
    text-align: center;
    margin-bottom: 20px;
    font-size: 15px;
}

.success {
    color: #28a745;
}

.error {
    color: #dc3545;
}

.episode-section {
    display: none;
    margin-top: 20px;
}

.episode-entry {
    border: 1px solid #444;
    padding: 15px;
    margin-bottom: 15px;
    background: #2a2a2a;
    border-radius: 6px;
    box-shadow: 0 0 5px #00000033;
}

.episode-entry label {
    margin: 6px 0;
    display: block;
    font-weight: 500;
    color: #ddd;
}

.add-episode-btn {
    background: #444;
    padding: 10px 16px;
    margin: 15px 0;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.add-episode-btn:hover {
    background: #666;
}

.remove-episode-btn {
    background: #dc3545;
    padding: 8px 14px;
    color: #fff;
    border: none;
    border-radius: 6px;
    margin-left: 10px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.remove-episode-btn:hover {
    background: #b20710;
}

#episode-count-section {
    display: none;
}

.admin-form input[type="checkbox"] {
    margin-right: 8px;
    transform: scale(1.2);
    accent-color: #e50914; /* Hiển thị màu đỏ Netflix */
}

.vip-label {
    display: inline-flex;
    align-items: center;
    background-color: #e50914;
    color: #fff;
    padding: 4px 10px;
    font-size: 13px;
    font-weight: bold;
    border-radius: 4px;
    margin-top: 8px;
    margin-bottom: 12px;
    width: fit-content;
}
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
    font-weight: 500;
    color: #ccc;
}

/* Switch container */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
}

/* Hide the default checkbox */
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

/* Slider style */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0;
    right: 0; bottom: 0;
    background-color: #555;
    transition: 0.4s;
    border-radius: 34px;
}

.slider::before {
    position: absolute;
    content: "";
    height: 20px; width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

/* Toggle ON */
input:checked + .slider {
    background-color: #e50914;
}

input:checked + .slider::before {
    transform: translateX(22px);
}

    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="dashboard-wrapper">
        <?php include 'admin_sidebar.php' ?>


        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="admin-form">
                <h2>THÊM PHIM</h2>
                <?php if (isset($success_message)): ?>
                    <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php elseif (isset($error_message)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="add_movie.php" method="POST" enctype="multipart/form-data">
                    <h3>Thông tin cơ bản</h3>
                    <label for="title">Tên phim</label>
                    <input type="text" id="title" name="title" required>

                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" rows="5" required></textarea>

                    <label for="genre">Thể loại</label>
                    <select id="genre" name="genre" required onchange="toggleEpisodeSection()">
                        <option value="Movie">Phim lẻ</option>
                        <option value="Series">Phim bộ</option>
                        <option value="Cartoon">Hoạt hình</option>
                        <option value="Cinema">Phim chiếu rạp</option>
                    </select>

                    <label for="sub_genre">Thể loại phụ (nhập các thể loại, cách nhau bằng dấu phẩy)</label>
                    <input type="text" id="sub_genre" name="sub_genre" placeholder="Ví dụ: Tình cảm, Hành động, Hài hước">

                    <label for="year">Năm sản xuất</label>
                    <input type="number" id="year" name="year" value="2025">

                    <label for="duration">Thời lượng (phút)</label>
                    <input type="number" id="duration" name="duration">

                    <div id="episode-count-section">
                        <label for="episode_count">Số tập</label>
                        <input type="number" id="episode_count" name="episode_count" min="1">
                    </div>

                    <label for="language">Ngôn ngữ</label>
                    <input type="text" id="language" name="language">

                    <label for="country">Quốc gia</label>
                    <input type="text" id="country" name="country">

                    <label for="director">Đạo diễn</label>
                    <input type="text" id="director" name="director">

                    <label for="actors">Diễn viên</label>
                    <input type="text" id="actors" name="actors">
                    <div class="toggle-wrapper">
                        <label for="is_vip_toggle">Phim VIP</label>
                        <label class="switch">
                            <input type="checkbox" id="is_vip_toggle" name="is_vip" value="1" <?= isset($movie['is_vip']) && $movie['is_vip'] ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>


                    <label for="status">Tình trạng</label>
                    <select id="status" name="status">
                        <option value="active" selected>Đang phát hành</option>
                        <option value="inactive">Hoàn thành</option>
                    </select>

                    <label for="thumbnail">Ảnh (ảnh đại diện)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>

                    <label for="video" id="video-label">Video (phim lẻ/hoạt hình/phim chiếu rạp)</label>
                    <input type="file" id="video" name="video" accept="video/*">

                    <!-- Episode Section for Series -->
                    <div class="episode-section" id="episode-section">
                        <h3>Thêm các tập (phim bộ)</h3>
                        <div id="episode-container">
                            
                        </div>
                        <button type="button" class="add-episode-btn" onclick="addEpisode()">Thêm tập</button>
                    </div>

                    <button type="submit">Thêm phim</button>
                </form>
            </div>
        </div>
    </div>
    <!-- END FORM SECTION -->


    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="../app.js"></script>
    <script>
        function toggleEpisodeSection() {
            const genre = document.getElementById('genre').value;
            const episodeSection = document.getElementById('episode-section');
            const episodeCountSection = document.getElementById('episode-count-section');
            const videoInput = document.getElementById('video');
            const videoLabel = document.getElementById('video-label');
            if (genre === 'Series') {
                episodeSection.style.display = 'block';
                episodeCountSection.style.display = 'block';
                videoInput.style.display = 'none';
                videoLabel.style.display = 'none';
                videoInput.removeAttribute('required');
            } else {
                episodeSection.style.display = 'none';
                episodeCountSection.style.display = 'none';
                videoInput.style.display = 'block';
                videoLabel.style.display = 'block';
                videoInput.setAttribute('required', 'required');
            }
        }

        function addEpisode() {
            const container = document.getElementById('episode-container');
            const index = container.children.length + 1;
            const episodeDiv = document.createElement('div');
            episodeDiv.className = 'episode-entry';

            episodeDiv.innerHTML = `
                <label for="episode_number_${index}">Số tập</label>
                <input type="number" id="episode_number_${index}" name="episode_numbers[]" value="${index}" required>

                <label for="episode_title_${index}">Tiêu đề tập</label>
                <input type="text" id="episode_title_${index}" name="episode_titles[]" placeholder="Tập ${index}">

                <label for="episode_video_${index}">Video tập</label>
                <input type="file" id="episode_video_${index}" name="episodes[]" accept="video/*" required>

                <div class="toggle-wrapper">
                    <label for="episode_is_vip_${index}">Tập VIP</label>
                    <label class="switch">
                        <input type="checkbox" id="episode_is_vip_${index}" name="episode_is_vip[]" value="1">
                        <span class="slider round"></span>
                    </label>
                </div>

                <button type="button" class="remove-episode-btn" onclick="this.parentElement.remove()">Xóa tập</button>
            `;
            container.appendChild(episodeDiv);
        }


        document.addEventListener('DOMContentLoaded', toggleEpisodeSection);
    </script>

    <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: '🎉 Thành công!',
        text: 'Phim đã được thêm vào hệ thống.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then(() => {
        // Xóa tham số added=1 để tránh hiện lại khi F5
        window.history.replaceState(null, null, window.location.pathname);
    });
});
</script>
<?php endif; ?>

</body>
</html>