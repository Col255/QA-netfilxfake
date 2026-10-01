<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Kết nối database

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

// Hàm lấy phim theo thể loại (genre)
function getMoviesByGenre($conn, $genre, $limit = 10) {
    $query = "SELECT id, title, thumbnail, description, duration, genre, year, episode_count, view_count 
              FROM movies 
              WHERE genre = ? AND status = 'active'
              ORDER BY view_count DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed (getMoviesByGenre): " . $conn->error);
        return [];
    }
    $stmt->bind_param("si", $genre, $limit);
    if (!$stmt->execute()) {
        error_log("Execute failed (getMoviesByGenre): " . $stmt->error);
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

// Hàm lấy phim theo thể loại phụ (sub_genre)
function getMoviesBySubGenre($conn, $sub_genre, $limit = 10) {
    $query = "SELECT id, title, thumbnail, description, duration, genre, year, episode_count, view_count 
              FROM movies 
              WHERE sub_genre LIKE ? AND status = 'active'
              ORDER BY view_count DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed (getMoviesBySubGenre): " . $conn->error);
        return [];
    }
    $searchTerm = '%' . $sub_genre . '%';
    $stmt->bind_param("si", $searchTerm, $limit);
    if (!$stmt->execute()) {
        error_log("Execute failed (getMoviesBySubGenre): " . $stmt->error);
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

// Hàm lấy phim theo quốc gia (country)
function getMoviesByCountry($conn, $country, $limit = 10) {
    $query = "SELECT id, title, thumbnail, description, duration, genre, year, episode_count, view_count 
              FROM movies 
              WHERE country = ? AND status = 'active'
              ORDER BY view_count DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed (getMoviesByCountry): " . $conn->error);
        return [];
    }
    $stmt->bind_param("si", $country, $limit);
    if (!$stmt->execute()) {
        error_log("Execute failed (getMoviesByCountry): " . $stmt->error);
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
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

// Xác định tiêu đề và lấy phim dựa trên tham số URL
$title = "Danh sách phim";
$movies = [];
if (isset($_GET['genre'])) {
    $genre = $_GET['genre'];
    $movies = getMoviesByGenre($conn, $genre);
    $title = "Phim " . htmlspecialchars($genre);
} elseif (isset($_GET['sub_genre'])) {
    $sub_genre = $_GET['sub_genre'];
    $movies = getMoviesBySubGenre($conn, $sub_genre);
    $title = "Phim " . htmlspecialchars($sub_genre);
} elseif (isset($_GET['country'])) {
    $country = $_GET['country'];
    $movies = getMoviesByCountry($conn, $country);
    $title = "Phim từ " . htmlspecialchars($country);
} else {
    $movies = getMoviesByGenre($conn, 'Movie');
    $title = "Phim lẻ";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NetfixVN - Xem phim theo thể loại, quốc gia hoặc danh mục yêu thích với chất lượng HD.">
    <meta name="keywords" content="phim, series, hoạt hình, streaming, NetfixVN">
    <title><?php echo htmlspecialchars($title); ?> - NetfixVN</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="iqiyi-style.css">
</head>
<body>

<?php include'header.php' ?>

    <!-- MOVIES SECTION -->
    <div class="section">
        <div class="container">
            <div class="section-header">
                <?php echo htmlspecialchars($title); ?>
            </div>
            <div class="movies-slide carousel-nav-center owl-carousel">
                <?php if (empty($movies)) { ?>
                    <div class="no-content">Không có phim trong danh mục này.</div>
                <?php } else { ?>
                    <?php foreach ($movies as $movie) { ?>
                        <a href="watch.php?id=<?php echo $movie['id']; ?>" class="movie-item">
                            <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <div class="movie-item-content">
                                <div class="movie-item-title">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </div>
                                <div class="movie-infos">
                                    <div class="movie-info">
                                        <?php if ($movie['genre'] == 'Series') { ?>
                                            <span><?php echo htmlspecialchars($movie['episode_count'] ?: 'N/A'); ?> tập</span>
                                        <?php } else { ?>
                                            <i class="bx bxs-time"></i>
                                            <span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span>
                                        <?php } ?>
                                    </div>
                                    <div class="movie-info">
                                        <span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span>
                                    </div>
                                    <div class="movie-info">
                                        <span><?php echo htmlspecialchars($movie['genre']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- END MOVIES SECTION -->

<?php include'footer.php' ?>

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>