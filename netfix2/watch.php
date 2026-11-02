<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Đảm bảo file db.php chứa kết nối CSDL

// Hàm lấy danh sách thể loại phụ từ cột sub_genre
function getSubGenres($conn) {
    $query = "SELECT sub_genre FROM movies WHERE sub_genre IS NOT NULL AND sub_genre != ''";
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query failed (getSubGenres): " . $conn->error);
        return [];
    }
    $subGenres = [];
    while ($row = $result->fetch_assoc()) {
        $genres = array_map('trim', explode(',', $row['sub_genre']));
        foreach ($genres as $genre) {
            if (!empty($genre)) {
                $subGenres[$genre] = true; // Loại bỏ trùng lặp
            }
        }
    }
    return array_keys($subGenres);
}

// Hàm lấy danh sách quốc gia từ cơ sở dữ liệu
function getCountries($conn) {
    $query = "SELECT DISTINCT country FROM movies WHERE country IS NOT NULL ORDER BY country";
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query failed (getCountries): " . $conn->error);
        return [];
    }
    $countries = $result->fetch_all(MYSQLI_ASSOC);
    return array_column($countries, 'country');
}

// Hàm kiểm tra và lấy đường dẫn ảnh
function getImagePath($thumbnail) {
    $defaultImage = 'images/default-thumbnail.jpg';
    if ($thumbnail && file_exists(__DIR__ . '/' . $thumbnail)) {
        return htmlspecialchars($thumbnail);
    }
    return htmlspecialchars($defaultImage);
}

// Lấy danh sách quốc gia và thể loại phụ
$countries = getCountries($conn);
$subGenres = getSubGenres($conn);

// Lấy ID phim từ URL
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$episode_id = isset($_GET['episode']) ? (int)$_GET['episode'] : 0;

if ($movie_id <= 0) {
    $error_message = "ID phim không hợp lệ.";
} else {
    // Lấy thông tin phim
    $stmt = $conn->prepare("SELECT id, title, thumbnail, description, duration, episode_count, genre, sub_genre, year, language, country, actors, director, status, video_path, view_count, is_vip FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    // Kiểm tra phim có tồn tại không
    if (!$movie) {
        $error_message = "Phim không tồn tại.";
    } else {
        // Kiểm tra quyền VIP
        $user_is_vip = isset($_SESSION['user_id']) ? false : false;
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT is_vip FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            $user_is_vip = $user && $user['is_vip'] == 1;
        }

        if ($movie['is_vip'] == 1 && !$user_is_vip) {
            $error_message = "Phim này yêu cầu gói VIP. Vui lòng nâng cấp tài khoản của bạn.";
        } else {
            // Cập nhật view_count
            // $new_view_count = ($movie['view_count'] ?? 0) + 1;
            // $update_stmt = $conn->prepare("UPDATE movies SET view_count = ? WHERE id = ?");
            // $update_stmt->bind_param("ii", $new_view_count, $movie_id);
            // $update_stmt->execute();
            // $update_stmt->close();

            // Ghi lại lịch sử xem vào bảng watch_history
            if (isset($_SESSION['user_id']) && $movie_id > 0) {
                $user_id = (int)$_SESSION['user_id'];
                $current_time = date('Y-m-d H:i:s');

                // Kiểm tra xem người dùng đã có lịch sử xem phim này chưa
                $stmt_check_history = $conn->prepare("SELECT id FROM watch_history WHERE user_id = ? AND movie_id = ?");
                $stmt_check_history->bind_param("ii", $user_id, $movie_id);
                $stmt_check_history->execute();
                $result_check_history = $stmt_check_history->get_result();

                if ($result_check_history->num_rows > 0) {
                    // Nếu đã có, cập nhật thời gian xem cuối cùng
                    $stmt_update_history = $conn->prepare("UPDATE watch_history SET last_watched_at = ? WHERE user_id = ? AND movie_id = ?");
                    $stmt_update_history->bind_param("sii", $current_time, $user_id, $movie_id);
                    $stmt_update_history->execute();
                    $stmt_update_history->close();
                } else {
                    // Nếu chưa có, chèn bản ghi mới
                    $stmt_insert_history = $conn->prepare("INSERT INTO watch_history (user_id, movie_id, last_watched_at) VALUES (?, ?, ?)");
                    $stmt_insert_history->bind_param("iis", $user_id, $movie_id, $current_time);
                    $stmt_insert_history->execute();
                    $stmt_insert_history->close();
                }
                $stmt_check_history->close();
            }

            // Nếu là phim bộ, lấy danh sách tập
            $episodes = [];
            if ($movie['genre'] === 'Series') {
                $stmt = $conn->prepare("
                    SELECT id, episode_number, title, video_path, is_vip
                    FROM episodes
                    WHERE movie_id = ?
                    ORDER BY episode_number ASC
                ");
                $stmt->bind_param("i", $movie_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $episodes = [];

                while ($ep = $result->fetch_assoc()) {
                    // Nếu là tập VIP và người dùng không VIP → ẩn video
                    if ($ep['is_vip'] == 1 && !$user_is_vip) {
                        $ep['video_path'] = null; // hoặc thay bằng thông báo, ảnh đại diện
                    }
                    $episodes[] = $ep;
                }
                $stmt->close();
            }

        }
    }
}

// Xử lý thêm bình luận
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_error = "Bạn cần đăng nhập để bình luận.";
    } else {
        $comment = trim($_POST['comment_text']);
        if ($comment !== '') {
            $stmt = $conn->prepare("INSERT INTO comments (user_id, movie_id, comment_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $_SESSION['user_id'], $movie_id, $comment);
            $stmt->execute();
            $stmt->close();
            header("Location: watch.php?id=$movie_id");
            exit();
        } else {
            $comment_error = "Nội dung bình luận không được để trống.";
        }
    }
}

// Lấy danh sách bình luận
$stmt = $conn->prepare("
    SELECT c.comment_text, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.movie_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($episode_id > 0) {
    $stmt = $conn->prepare("SELECT is_vip, episode_number FROM episodes WHERE id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $episode_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ep = $result->fetch_assoc();
    $stmt->close();

    if ($ep && $ep['is_vip'] == 1 && !$user_is_vip) {
        // Người thường cố tình truy cập tập VIP
        $error_message = "Tập này yêu cầu VIP. Vui lòng nâng cấp tài khoản.";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NetfixVN - Xem phim <?php echo isset($movie) ? htmlspecialchars($movie['title']) : ''; ?> với chất lượng HD.">
    <meta name="keywords" content="phim, series, hoạt hình, streaming, NetfixVN">
    <title>NetfixVN - Xem <?php echo isset($movie) ? htmlspecialchars($movie['title']) : 'Phim'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="iqiyi-style.css">
    <style>
   /* RESET */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  
}

body {
  font-family: 'Cairo', sans-serif;
  background-color: #0f0f0f;
  color: #f1f1f1;
  line-height: 1.6;
  padding-top: 100px; /* để tránh che bởi header */
}

/* Container chính */
.watch-container {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
}

/* Thông tin phim */
.movie-details {
  display: flex;
  gap: 30px;
  margin-bottom: 40px;
}

.movie-details img {
  width: 320px;
  height: auto;
  border-radius: 8px;
  object-fit: cover;
  box-shadow: 0 0 10px rgba(0,0,0,0.7);
}

.movie-details .info h2 {
  color: #f9d342;
  margin-bottom: 10px;
  font-size: 28px;
}

.movie-details .info p {
  color: #ccc;
  margin: 6px 0;
}

.movie-details .sub-genre {
  /* color: #30d5c8; */
  font-style: italic;
}

/* Trình phát video */
.video-player {
  margin-bottom: 40px;
}

.video-player video {
  width: 100%;
  max-height: 500px;
  border-radius: 8px;
  background-color: #000;
  box-shadow: 0 0 10px rgba(255,255,255,0.1);
}

/* Danh sách tập phim */
.episode-list h3 {
  color: #f9d342;
  font-size: 20px;
  margin-bottom: 20px;
}

.episode-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.episode-card {
  background-color: #1a1a1a;
  border-radius: 6px;
  overflow: hidden;
  text-align: center;
  position: relative;
  transition: all 0.3s;
}

.episode-card:hover {
  transform: translateY(-2px);
}

.ep-thumb {
  position: relative;
}

.ep-thumb img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  display: block;
}

.play-btn {
  position: absolute;
  bottom: 6px;
  right: 8px;
}

.play-btn a {
  background: rgba(0, 0, 0, 0.6);
  color: #f9d342;
  border-radius: 50%;
  padding: 5px 7px;
  font-size: 14px;
  text-decoration: none;
}

.play-btn a:hover {
  background-color: #f9d342;
  color: #000;
}

.badge-vip {
  position: absolute;
  top: 6px;
  left: 6px;
  background-color: #e50914;
  color: #fff;
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 4px;
}

.ep-title {
  font-size: 13px;
  padding: 8px 0;
  color: #eee;
}
.ep-preview {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  background-color: #000;
}


/* Nút VIP */
.btn-vip {
  background-color: #444;
  color: white;
  border: none;
}

.btn-vip:hover {
  background-color: #e50914;
}

/* Tag VIP */
.episode-card.locked::after {
  content: 'VIP';
  position: absolute;
  top: 6px;
  right: 8px;
  background: #e50914;
  color: white;
  font-size: 11px;
  padding: 2px 5px;
  border-radius: 4px;
}

/* Bình luận */
.comment-section {
  margin-top: 40px;
}

.comment-section h3 {
  color: #f9d342;
  margin-bottom: 10px;
}

.comment-section textarea {
  width: 100%;
  padding: 10px;
  border-radius: 5px;
  resize: vertical;
  font-size: 15px;
  border: none;
  color: #000;
}

.comment-section button {
  margin-top: 8px;
  background: #f9d342;
  color: #000;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}

.comment-section button:hover {
  background: #e0c42f;
}

/* Tiêu đề đang xem */
.currently-watching {
  font-family: monospace;
    font-size: 18px;
    color: #f1f1f1;
    margin-bottom: 5px;
    margin-top:20px ;
    transition: color 0.3s ease;
}

.currently-watching span {
    color:rgb(236, 240, 239);
    font-weight: 600;
    transition: color 0.3s ease;

}

.currently-watching:hover span {
    color: #f9d342;
}
    </style>
</head>
<body>
   
<?php include 'header.php' ?>

    <div class="watch-container">
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php if ($error_message === "Phim này yêu cầu gói VIP. Vui lòng nâng cấp tài khoản của bạn."): ?>
                <div class="upgrade-vip">
                    <a href="pricing.php">Nâng cấp gói VIP ngay</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="movie-details">
                <img src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                <div class="info">
                    <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                    <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($movie['description']); ?></p>
                    <p><strong>Thể loại:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                    <p><strong>Thể loại phụ:</strong> <span class="sub-genre"><?php echo htmlspecialchars($movie['sub_genre'] ?: 'N/A'); ?></span></p>
                    <p><strong>Năm:</strong> <?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></p>
                    <?php if ($movie['genre'] !== 'Series'): ?>
                        <p><strong>Thời lượng:</strong> <?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</p>
                    <?php else: ?>
                        <p><strong>Số tập:</strong> <?php echo htmlspecialchars($movie['episode_count'] ?: 'N/A'); ?> tập</p>
                    <?php endif; ?>
                    <p><strong>Ngôn ngữ:</strong> <?php echo htmlspecialchars($movie['language'] ?: 'N/A'); ?></p>
                    <p><strong>Quốc gia:</strong> <?php echo htmlspecialchars($movie['country'] ?: 'N/A'); ?></p>
                    <p><strong>Diễn viên:</strong> <?php echo htmlspecialchars($movie['actors'] ?: 'N/A'); ?></p>
                    <p><strong>Đạo diễn:</strong> <?php echo htmlspecialchars($movie['director'] ?: 'N/A'); ?></p>
                    <p><strong>Lượt xem:</strong> <?php echo htmlspecialchars($movie['view_count']); ?> lần</p>
                </div>
            </div>

            <?php if ($movie['genre'] !== 'Series'): ?>
                <div class="currently-watching">
                   <strong>📺 Bạn đang xem phim: </strong> <span><?php echo htmlspecialchars($movie['title']); ?></span>
                </div>
                <div class="loading-spinner" id="video-loading">
                    <!-- <p>Đang tải video...</p> -->
                </div>
                <?php if ($movie['video_path'] && file_exists(__DIR__ . '/' . $movie['video_path'])): ?>
                    <div class="video-player">
                        <video controls>
                            <source src="<?php echo htmlspecialchars($movie['video_path']); ?>" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ thẻ video.
                        </video>
                    </div>
                <?php else: ?>
                    <div class="no-content">Video không khả dụng. Vui lòng kiểm tra lại đường dẫn video.</div>
                <?php endif; ?>
            <?php else: ?>
                <?php if (empty($episodes)): ?>
                    <div class="no-content">Chưa có tập nào.</div>
                <?php else: ?>
                    <div class="episode-list">
                        <h3>Danh sách tập</h3>
                        <div class="episode-grid">
                            <?php foreach ($episodes as $episode): ?>
                                <div class="episode-card <?php echo ($episode['is_vip'] == 1 && !$user_is_vip) ? 'locked' : ''; ?>">
                                    <div class="ep-thumb">
                                        <video class="ep-preview" muted playsinline preload="metadata">
                                            <source src="<?php echo htmlspecialchars($episode['video_path']); ?>" type="video/mp4">
                                        </video>
                                        <?php if ($episode['is_vip'] == 1): ?>
                                            <span class="badge-vip">VIP</span>
                                        <?php endif; ?>
                                        <div class="play-btn">
                                            <?php if ($episode['is_vip'] == 1 && !$user_is_vip): ?>
                                                <a href="#" class="unlock-btn" data-href="pricing.php">🔒</a>
                                            <?php else: ?>
                                                <a href="?id=<?php echo $movie_id; ?>&episode=<?php echo $episode['id']; ?>">▶</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ep-title">Tập <?php echo $episode['episode_number']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (isset($_GET['episode']) && is_numeric($_GET['episode'])): ?>
                        <?php
                        $episode_id = (int)$_GET['episode'];
                        $stmt = $conn->prepare("SELECT video_path, episode_number FROM episodes WHERE id = ? AND movie_id = ?");
                        $stmt->bind_param("ii", $episode_id, $movie_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $selected_episode = $result->fetch_assoc();
                        $stmt->close();
                        ?>
                        <div class="currently-watching">
                           <strong>📺 Bạn đang xem phim:</strong>  <span><?php echo htmlspecialchars($movie['title']); ?></span>: Tập <span><?php echo htmlspecialchars($selected_episode['episode_number']); ?></span>
                        </div>
                        <div class="loading-spinner" id="video-loading">
                            <!-- <p>Đang tải video...</p> -->
                        </div>
                        <?php if ($selected_episode && $selected_episode['video_path'] && file_exists(__DIR__ . '/' . $selected_episode['video_path'])): ?>
                            <div class="video-player">
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($selected_episode['video_path']); ?>" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ thẻ video.
                                </video>
                            </div>
                        <?php else: ?>
                            <div class="no-content">Video không khả dụng. Vui lòng kiểm tra lại đường dẫn video.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div style="max-width: 1100px; margin: 30px auto; color: #fff;">
        <h3 style="color: #e50914;">💬 Bình luận</h3>

        <?php if ($comment_error): ?>
            <p style="color: #dc3545;"><?php echo htmlspecialchars($comment_error); ?></p>
        <?php endif; ?>

        <form method="POST" style="margin-bottom: 20px;">
            <textarea name="comment_text" rows="4" placeholder="Nhập bình luận của bạn..." style=" color:#000000; width: 100%; padding: 10px; border-radius: 5px;"></textarea>
            <button type="submit" style="margin-top: 10px; background-color: #e50914; color: #fff; border: none; padding: 8px 16px; border-radius: 4px;">Gửi</button>
        </form>

        <?php if (empty($comments)): ?>
            <p style="color: #bbb;">Chưa có bình luận nào.</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div style="border-bottom: 1px solid #444; padding: 10px 0;">
                    <strong style="color: #ffd700;"><?php echo htmlspecialchars($comment['username']); ?></strong>
                    <span style="color: #888; font-size: 13px;"> - <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                    <p style="margin-top: 5px;"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
                            
<?php include 'footer.php' ?>
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
    <script>
        // Hiển thị spinner khi video đang tải
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('video');
            const loadingSpinner = document.getElementById('video-loading');
            if (video) {
                video.addEventListener('loadstart', function() {
                    loadingSpinner.classList.add('active');
                });
                video.addEventListener('canplay', function() {
                    loadingSpinner.classList.remove('active');
                });
                video.addEventListener('error', function() {
                    loadingSpinner.classList.remove('active');
                    alert('Lỗi khi tải video. Vui lòng kiểm tra lại.');
                });
            }
        });
    </script>

    <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.unlock-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('data-href');

            Swal.fire({
                title: 'Tập VIP 🔒',
                text: 'Bạn cần nâng cấp tài khoản VIP để xem tập này. Chuyển đến trang nâng cấp?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Nâng cấp VIP',
                cancelButtonText: 'Đóng'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }
            });
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const mainVideo = document.querySelector('.video-player video');
  if (mainVideo) {
    mainVideo.addEventListener('ended', function () {
      // Gửi yêu cầu cập nhật lượt xem
      fetch('api/view_counter.php?id=<?php echo $movie_id; ?>')
        .then(response => response.json())
        .then(data => {
          console.log('Đã ghi nhận lượt xem:', data);
        })
        .catch(err => {
          console.error('Lỗi khi cập nhật lượt xem:', err);
        });
    });
  }
});
</script>
</body>
</html>