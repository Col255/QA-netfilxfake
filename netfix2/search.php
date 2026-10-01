<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

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

// Hàm kiểm tra và lấy đường dẫn ảnh
function getImagePath($thumbnail) {
    $defaultImage = 'images/default-thumbnail.jpg';
    if ($thumbnail && file_exists(__DIR__ . '/' . $thumbnail)) {
        return htmlspecialchars($thumbnail);
    }
    return htmlspecialchars($defaultImage);
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

// Lấy danh sách quốc gia và thể loại phụ
$countries = getCountries($conn);
$subGenres = getSubGenres($conn);

// Xử lý tìm kiếm
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$searchResults = [];

if (!empty($query)) {
    $sql = "SELECT id, title, thumbnail, description, duration, genre, year, episode_count, view_count 
            FROM movies 
            WHERE (title LIKE ? OR description LIKE ? OR genre LIKE ? OR actors LIKE ? OR director LIKE ?)";
    $searchTerm = "%{$query}%";
    
    if (!empty($genre)) {
        $sql .= " AND genre = ?";
    }
    $sql .= " ORDER BY view_count DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed (search): " . $conn->error);
        die("Lỗi chuẩn bị truy vấn: " . $conn->error);
    }

    if (!empty($genre)) {
        $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $genre);
    } else {
        $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }

    if (!$stmt->execute()) {
        error_log("Execute failed (search): " . $stmt->error);
        $stmt->close();
        die("Lỗi thực thi truy vấn: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Giữ nguyên phần head -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NetfixVN - Kết quả tìm kiếm phim, series và hoạt hình với chất lượng HD.">
    <meta name="keywords" content="phim, series, hoạt hình, tìm kiếm, NetfixVN">
    <title>Kết quả tìm kiếm - NetfixVN</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="iqiyi-style.css">
  
</head>
<body>
   <?php include'header.php' ?>


    <!-- Phần còn lại của search.php giữ nguyên -->
    <div class="section">
        <div class="container">
            <div class="section-header">
                Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($query); ?>"
            </div>
            <div class="movies-slide carousel-nav-center owl-carousel">
                <?php if (empty($searchResults)) { ?>
                    <div class="no-content">Không tìm thấy phim phù hợp với "<?php echo htmlspecialchars($query); ?>"</div>
                <?php } else { ?>
                    <?php foreach ($searchResults as $movie) { ?>
                        <a href="watch.php?id=<?php echo $movie['id']; ?>" class="movie-item">
                            <img loading="lazy" src="<?php echo getImagePath($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> Poster">
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

<?php include'footer.php' ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>
    <script src="app.js"></script>
    <script>
        $(document).ready(function() {
            $('#hamburger-menu').click(function() {
                $(this).toggleClass('active');
                $('#nav-menu').toggleClass('active');
            });

            $('.owl-carousel').owlCarousel({
                loop: true,
                margin: 10,
                nav: true,
                responsive: {
                    0: { items: 1 },
                    600: { items: 3 },
                    1000: { items: 5 }
                }
            });
        });
    </script>
</body>
</html>