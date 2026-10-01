<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'db.php'; // Kết nối database

// Hàm lấy danh sách thể loại phụ từ cột sub_genre
if (!function_exists('getSubGenres')) {
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
                $normalized = mb_strtolower(trim($genre));
                if (!empty($normalized) && !isset($subGenres[$normalized])) {
                    $subGenres[$normalized] = ucfirst($genre); // Giữ định dạng hiển thị
                }
            }
        }

        return array_values($subGenres);
    }

}

// Hàm lấy phim theo thể loại (genre)
if (!function_exists('getMoviesByGenre')) {
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
}

// Hàm lấy phim theo thể loại phụ (sub_genre)
if (!function_exists('getMoviesBySubGenre')) {
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
        $stmt->bind_param("si", $searchTerm, $limit); // Fixed syntax error
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
}

// Hàm lấy phim theo quốc gia (country)
if (!function_exists('getMoviesByCountry')) {
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
}

// Hàm lấy danh sách quốc gia từ cơ sở dữ liệu
if (!function_exists('getCountries')) {
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
}

// Hàm kiểm tra và lấy đường dẫn ảnh
if (!function_exists('getImagePath')) {
    function getImagePath($thumbnail) {
        $defaultImage = 'images/default-thumbnail.jpg';
        if ($thumbnail && file_exists(__DIR__ . '/' . $thumbnail)) {
            return htmlspecialchars($thumbnail);
        }
        return htmlspecialchars($defaultImage);
    }
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
<!-- NAV -->
<div class="nav-wrapper">
    <div class="container">
        <div class="nav">
            <a href="index.php" class="logo">
                <i class='bx bx-movie-play bx-tada main-color'></i>Netfl<span class="main-color">i</span>xvn
            </a>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li class="dropdown">
                    <a href="#">Thể loại <i class='bx bx-chevron-down'></i></a>
                    <ul class="dropdown-menu">
                        <?php if (empty($subGenres)) { ?>
                            <li><a href="#">Không có thể loại phụ</a></li>
                        <?php } else { ?>
                            <?php foreach ($subGenres as $subGenre) { ?>
                                <li><a href="movies.php?sub_genre=<?php echo urlencode($subGenre); ?>"><?php echo htmlspecialchars($subGenre); ?></a></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Quốc gia <i class='bx bx-chevron-down'></i></a>
                    <ul class="dropdown-menu">
                        <?php if (empty($countries)) { ?>
                            <li><a href="#">Không có quốc gia</a></li>
                        <?php } else { ?>
                            <?php foreach ($countries as $country) { ?>
                                <li><a href="movies.php?country=<?php echo urlencode($country); ?>"><?php echo htmlspecialchars($country); ?></a></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Phim <i class='bx bx-chevron-down'></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="movies.php?genre=Cinema">Phim Chiếu Rạp</a></li>
                        <li><a href="movies.php?genre=Series">Phim Bộ</a></li>
                        <li><a href="movies.php?genre=Cartoon">Phim Hoạt Hình</a></li>
                        <li><a href="movies.php?genre=Movie">Phim Lẻ</a></li>
                    </ul>
                </li>
                <li class="search-wrapper">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="query" placeholder="Tìm kiếm phim..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" required>
                        <button type="submit"><i class='bx bx-search'></i></button>
                    </form>
                </li>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <li><a href="profile.php" class="btn btn-hover">Hồ sơ</a></li>
                    <li><a href="logout.php" class="btn btn-hover">Đăng xuất</a></li>
                    <li><a href="pricing.php" class="btn btn-vip">Nâng cấp VIP</a></li>
                <?php } else { ?>
                    <li><a href="login.php" class="btn btn-hover">Đăng nhập</a></li>
                    <li><a href="register.php" class="btn btn-vip">Đăng ký</a></li>
                <?php } ?>
            </ul>
            <div class="hamburger-menu" id="hamburger-menu">
                <i class='bx bx-menu'></i>
            </div>
        </div>
    </div>
</div>
<!-- END NAV -->
 <script>
  window.addEventListener('scroll', function () {
    const header = document.querySelector('.nav-wrapper');
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
</script>
