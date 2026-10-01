<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Kết nối database

// Hàm lấy danh sách thể loại phụ từ cột sub_genre
function getPopularSubGenres($conn) {
    $query = "SELECT sub_genre FROM movies WHERE sub_genre IS NOT NULL AND sub_genre != ''";
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query failed (getPopularSubGenres): " . $conn->error);
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

// Hàm lấy phim từ database với các cột cụ thể, có thể lọc theo thể loại
function getMovies($conn, $genre = null, $limit) {
    $query = "SELECT id, title, thumbnail, description, duration, genre, year, language, country, actors, director, status, episode_count, video_path AS video_url, view_count, is_vip FROM movies";
    if ($genre) {
        $query .= " WHERE genre = ? ORDER BY view_count DESC LIMIT ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed (getMovies): " . $conn->error);
            return [];
        }
        $stmt->bind_param("si", $genre, $limit);
    } else {
        $query .= " ORDER BY view_count DESC LIMIT ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed (getMovies): " . $conn->error);
            return [];
        }
        $stmt->bind_param("i", $limit);
    }
    if (!$stmt->execute()) {
        error_log("Execute failed (getMovies): " . $stmt->error);
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

// Hàm lấy top 10 phim thịnh hành dựa trên id
function getTopMovies($conn, $limit) {
    $stmt = $conn->prepare("SELECT id, title, thumbnail, description, duration, genre, year, is_vip FROM movies ORDER BY id DESC LIMIT ?");
    if (!$stmt) {
        error_log("Prepare failed (getTopMovies): " . $conn->error);
        return [];
    }
    $stmt->bind_param("i", $limit);
    if (!$stmt->execute()) {
        error_log("Execute failed (getTopMovies): " . $stmt->error);
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

// Lấy dữ liệu
$subGenres = getPopularSubGenres($conn);
$countries = getCountries($conn);
$heroMovies = getMovies($conn, null, 3);
$topMovies = getTopMovies($conn, 10);
$latestMovies = getMovies($conn, 'Movie', 7);
$latestSeries = getMovies($conn, 'Series', 7);
$latestCartoons = getMovies($conn, 'Cartoon', 7);
$latestCinema = getMovies($conn, 'Cinema', 7);
$recommendedMovie = !empty($topMovies) ? [$topMovies[0]] : [];

// Gán nhãn cho các danh sách
function assignMovieLabels(&$movies) {
    foreach ($movies as &$movie) {
        $movie['label'] = (!empty($movie['is_vip']) && $movie['is_vip'] == 1) ? 'VIP' : 'Free';
    }
}

// Gán nhãn cho từng mảng phim (nếu có)
if (!empty($topMovies)) assignMovieLabels($topMovies);
if (!empty($latestMovies)) assignMovieLabels($latestMovies);
if (!empty($latestCinema)) assignMovieLabels($latestCinema);
if (!empty($latestSeries)) assignMovieLabels($latestSeries);
if (!empty($latestCartoons)) assignMovieLabels($latestCartoons);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NetfixVN - Xem phim, series và hoạt hình mới nhất với chất lượng HD. Đăng ký ngay để trải nghiệm!">
    <meta name="keywords" content="phim, series, hoạt hình, streaming, NetfixVN">
    <title>NetfixVN - Xem Phim Online</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="iqiyi-style.css">
</head>
<body>
<?php include 'header.php' ?>
    <!-- HERO SECTION -->
    <div class="hero-section">
        <div class="hero-slide">
            <div class="owl-carousel carousel-nav-center" id="hero-carousel">
                <?php if (empty($heroMovies)) { ?>
                    <div class="no-content">Chưa có phim nổi bật nào.</div>
                <?php } else { ?>
                    <?php foreach ($heroMovies as $movie) { ?>
                        <div class="hero-slide-item">
                            <div class="top-left-blur-overlay">
                                <div class="hero-slide-item-content">
                                    <div class="item-content-wraper">
                                        <div class="item-content-title top-down">
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                        </div>
                                        <div class="movie-infos top-down delay-2">
                                            <div class="movie-info">
                                                <i class="bx bxs-time"></i>
                                                <span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span>
                                            </div>
                                            <div class="movie-info">
                                                <span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span>
                                            </div>
                                            <div class="movie-info">
                                                <span><?php echo htmlspecialchars($movie['genre']); ?></span>
                                            </div>
                                        </div>
                                        <!-- <div class="item-content-description top-down delay-4">
                                            <?php echo htmlspecialchars($movie['description']); ?>
                                        </div> -->
                                        <div class="item-content-action top-down delay-6">
                                            <a href="watch.php?id=<?php echo $movie['id']; ?>" class="btn btn-hover">
                                                <i class="bx bxs-right-arrow"></i>
                                                <span>Xem ngay</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hero-slide-item-image">
                                <div class="blur-bg" style="background-image: url('<?php echo getImagePath($movie['thumbnail']); ?>');"></div>
                                <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <div class="top-movies-slide">
            <div class="owl-carousel" id="top-movies-slide">
                <?php if (empty($topMovies)) { ?>
                    <div class="no-content">Chưa có phim mới ra nào.</div>
                <?php } else { ?>
                    <?php foreach ($topMovies as $movie) { ?>
                        <div class="top-movie-item" style="position: relative;">
                            <?php if (!empty($movie['label'])): ?>
                                <div class="movie-badge <?php echo strtolower($movie['label']); ?>">
                                    <?php echo htmlspecialchars($movie['label']); ?>
                                </div>
                            <?php endif; ?>
                            <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <div class="movie-item-content">
                                <div class="movie-item-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                                <div class="movie-infos">
                                    <div class="movie-info"><i class="bx bxs-time"></i><span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span></div>
                                    <div class="movie-info"><span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span></div>
                                    <div class="movie-info"><span><?php echo htmlspecialchars($movie['genre']); ?></span></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <!-- END HERO SECTION -->

    <!-- LATEST MOVIES SECTION -->
<div class="section">
    <div class="container">
        <div class="section-header">
            Phim mới nhất 🆕
        </div>
        <div class="movies-slide carousel-nav-center owl-carousel">
            <?php if (empty($latestMovies)) { ?>
                <div class="no-content">Chưa có phim mới nào.</div>
            <?php } else { ?>
                <?php foreach ($latestMovies as $movie) { ?>
                    <div class="movie-item" style="position: relative;">
                        <?php if (!empty($movie['label'])): ?>
                            <div class="movie-badge <?php echo strtolower($movie['label']); ?>">
                                <?php echo htmlspecialchars($movie['label']); ?>
                            </div>
                        <?php endif; ?>
                        <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <!-- Nút xem ngay -->
                        <div class="movie-hover-action">
                            <a href="watch.php?id=<?php echo $movie['id']; ?>" class="watch-btn-circle">
                                <span class="play-icon"></span>
                            </a>
                        </div>
                        <div class="movie-item-content">
                            <div class="movie-item-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div class="movie-infos">
                                <div class="movie-info"><i class="bx bxs-time"></i><span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['genre']); ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- END LATEST MOVIES SECTION -->


    <!-- NEW CINEMA MOVIES SECTION -->
<div class="section">
    <div class="container">
        <div class="section-header">
            Phim chiếu rạp mới 🎬
        </div>
        <div class="movies-slide carousel-nav-center owl-carousel">
            <?php if (empty($latestCinema)) { ?>
                <div class="no-content">Chưa có phim chiếu rạp mới nào.</div>
            <?php } else { ?>
                <?php foreach ($latestCinema as $movie) { ?>
                    <div class="movie-item" style="position: relative;">
                        <?php if (!empty($movie['label'])): ?>
                            <div class="movie-badge <?php echo strtolower($movie['label']); ?>">
                                <?php echo htmlspecialchars($movie['label']); ?>
                            </div>
                        <?php endif; ?>
                        <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <!-- Nút xem ngay -->
                        <div class="movie-hover-action">
                            <a href="watch.php?id=<?php echo $movie['id']; ?>" class="watch-btn-circle">
                                <span class="play-icon"></span>
                            </a>
                        </div>
                        <div class="movie-item-content">
                            <div class="movie-item-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div class="movie-infos">
                                <div class="movie-info"><i class="bx bxs-time"></i><span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['genre']); ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- END NEW CINEMA MOVIES SECTION -->

   <!-- LATEST SERIES SECTION -->
<div class="section">
    <div class="container">
        <div class="section-header">
            Series mới nhất 📺
        </div>
        <div class="movies-slide carousel-nav-center owl-carousel">
            <?php if (empty($latestSeries)) { ?>
                <div class="no-content">Chưa có series mới nào.</div>
            <?php } else { ?>
                <?php foreach ($latestSeries as $movie) { ?>
                    <div class="movie-item" style="position: relative;">
                        <?php if (!empty($movie['label'])): ?>
                            <div class="movie-badge <?php echo strtolower($movie['label']); ?>">
                                <?php echo htmlspecialchars($movie['label']); ?>
                            </div>
                        <?php endif; ?>
                        <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <!-- Nút xem ngay -->
                        <div class="movie-hover-action">
                            <a href="watch.php?id=<?php echo $movie['id']; ?>" class="watch-btn-circle">
                                <span class="play-icon"></span>
                            </a>
                        </div>
                        <div class="movie-item-content">
                            <div class="movie-item-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div class="movie-infos">
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['episode_count'] ?: 'N/A'); ?> tập</span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['genre']); ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- END LATEST SERIES SECTION -->

   <!-- LATEST CARTOONS SECTION -->
<div class="section">
    <div class="container">
        <div class="section-header">
            Hoạt hình mới nhất 🎞️
        </div>
        <div class="movies-slide carousel-nav-center owl-carousel">
            <?php if (empty($latestCartoons)) { ?>
                <div class="no-content">Chưa có hoạt hình mới nào.</div>
            <?php } else { ?>
                <?php foreach ($latestCartoons as $movie) { ?>
                    <div class="movie-item" style="position: relative;">
                        <?php if (!empty($movie['label'])): ?>
                            <div class="movie-badge <?php echo strtolower($movie['label']); ?>">
                                <?php echo htmlspecialchars($movie['label']); ?>
                            </div>
                        <?php endif; ?>
                        <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <!-- Nút xem ngay -->
                        <div class="movie-hover-action">
                            <a href="watch.php?id=<?php echo $movie['id']; ?>" class="watch-btn-circle">
                                <span class="play-icon"></span>
                            </a>
                        </div>
                        <div class="movie-item-content">
                            <div class="movie-item-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div class="movie-infos">
                                <div class="movie-info"><i class="bx bxs-time"></i><span><?php echo htmlspecialchars($movie['duration'] ?: 'N/A'); ?> phút</span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['year'] ?: 'N/A'); ?></span></div>
                                <div class="movie-info"><span><?php echo htmlspecialchars($movie['genre']); ?></span></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- END LATEST CARTOONS SECTION -->

<?php include 'footer.php' ?>

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>