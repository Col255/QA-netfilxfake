# TÀI LIỆU PHÂN TÍCH CFG, BẬC BAO PHỦ & BVA - PHÂN HỆ FE2: MOV (KHÁM PHÁ & TRÌNH PHÁT PHIM)
## Dự án: Netfix2 | Mục tiêu: 100% C0 (Câu lệnh), 100% C1 (Nhánh) & BVA Ma Trận Tổ Hợp

> **Phân hệ:** FE2 – MOV (Movies & Video Streaming)  
> **Các tệp tin phụ trách (4 files):**
> 1. `netfix2/index.php` ($V(G) = 36$, 35 nút nhánh)
> 2. `netfix2/movies.php` ($V(G) = 19$, 18 nút nhánh)
> 3. `netfix2/search.php` ($V(G) = 15$, 14 nút nhánh)
> 4. `netfix2/watch.php` ($V(G) = 35$, 34 nút nhánh)  
> **Tổng $V(G)$ lý thuyết phân hệ FE2:** $36 + 19 + 15 + 35 = 105$ (Tối thiểu 105 Basis Paths)

---

## 1. PHÂN TÍCH TỆP 1: `netfix2/index.php` (L1 - L127)

### 1.1. Bảng Ánh Xạ Khối Lệnh ($S_i$) và Nút Nhánh ($D_i$)
* `IDX_S1` (L1 - L9): `session_start(); require 'db.php';`
* `IDX_S2` (L10 - L28): Khai báo hàm `getPopularSubGenres($conn)`
  * `IDX_D1` (L14): `if (!$result) -> return []`
  * `IDX_D2` (L19): Vòng lặp `while ($row = $result->fetch_assoc())`
  * `IDX_D3` (L22): `if (!empty($genre))`
* `IDX_S3` (L30 - L59): Khai báo hàm `getMovies($conn, $genre = null, $limit)`
  * `IDX_D4` (L33): `if ($genre)` $\rightarrow$ Branch có lọc thể loại vs không lọc thể loại
  * `IDX_D5` (L36/L44): `if (!$stmt) -> return []`
  * `IDX_D6` (L50): `if (!$stmt->execute()) -> return []`
* `IDX_S4` (L61 - L78): Khai báo hàm `getTopMovies($conn, $limit)`
* `IDX_S5` (L80 - L90): Khai báo hàm `getCountries($conn)`
* `IDX_S6` (L92 - L99): Khai báo hàm `getImagePath($thumbnail)`
  * `IDX_D7` (L95): `if ($thumbnail && file_exists(__DIR__ . '/' . $thumbnail))`
* `IDX_S7` (L101 - L111): Gọi các hàm lấy dữ liệu:
  * `$subGenres`, `$countries`, `$heroMovies`, `$topMovies`, `$latestMovies`, `$latestSeries`, `$latestCartoons`, `$latestCinema`
  * `IDX_D8` (L110): `$recommendedMovie = !empty($topMovies) ? [$topMovies[0]] : [];`
* `IDX_S8` (L113 - L117): Khai báo hàm `assignMovieLabels(&$movies)`
  * `IDX_D9` (L114): Vòng lặp `foreach ($movies as &$movie)`
  * `IDX_D10` (L115): `(!empty($movie['is_vip']) && $movie['is_vip'] == 1) ? 'VIP' : 'Free'`
* `IDX_S9` (L120 - L124): Gán nhãn cho 5 danh sách phim:
  * `IDX_D11` (L120): `if (!empty($topMovies))`
  * `IDX_D12` (L121): `if (!empty($latestMovies))`
  * `IDX_D13` (L122): `if (!empty($latestCinema))`
  * `IDX_D14` (L123): `if (!empty($latestSeries))`
  * `IDX_D15` (L124): `if (!empty($latestCartoons))`
* `IDX_S10` (L128 - L391): Render giao diện HTML trang chủ

### 1.2. Độ Phức Tạp Chu Trình $V(G)$ & Basis Paths
* $P = 35$ nút nhánh $\implies V(G) = P + 1 = 36$.
* Cần tối thiểu **36 Basis Paths** để phủ 100% các nhánh rẽ và vòng lặp trong `index.php`.
* **Phát hiện DEFECT WB-DEF-04:** Tại dòng 31, khai báo tham số tùy chọn `$genre = null` trước tham số bắt buộc `$limit` gây lỗi `E_DEPRECATED` trên PHP 8.0+.

---

## 2. PHÂN TÍCH TỆP 2: `netfix2/movies.php` (L1 - L144)

### 2.1. Bảng Ánh Xạ Khối Lệnh ($S_i$) và Nút Nhánh ($D_i$)
* `MOV_S1` (L1 - L9): `session_start(); require 'db.php';`
* `MOV_S2` (L30 - L51): `getMoviesByGenre($conn, $genre, $limit = 10)`
* `MOV_S3` (L53 - L75): `getMoviesBySubGenre($conn, $sub_genre, $limit = 10)`
* `MOV_S4` (L77 - L98): `getMoviesByCountry($conn, $country, $limit = 10)`
* `MOV_S5` (L100 - L124): `getCountries($conn)`, `getSubGenres($conn)`, `getImagePath()`
* `MOV_D1` (L128): `if (isset($_GET['genre']))`
  * `MOV_D1_T`: Lọc theo thể loại chính `getMoviesByGenre`
  * `MOV_D1_F`: Tiếp tục `MOV_D2`
* `MOV_D2` (L132): `elseif (isset($_GET['sub_genre']))`
  * `MOV_D2_T`: Lọc theo thể loại phụ `getMoviesBySubGenre`
  * `MOV_D2_F`: Tiếp tục `MOV_D3`
* `MOV_D3` (L136): `elseif (isset($_GET['country']))`
  * `MOV_D3_T`: Lọc theo quốc gia `getMoviesByCountry`
  * `MOV_D3_F`: Tiếp tục `MOV_S6` (Mặc định tải Phim lẻ `getMoviesByGenre($conn, 'Movie')`)

* **Độ phức tạp Cyclomatic $V(G)$:**
  * $P = 18 \implies V(G) = P + 1 = 19$.
  * **19 Basis Paths** bảo đảm kích hoạt đầy đủ các bộ lọc `genre`, `sub_genre`, `country`, và trạng thái mặc định không có tham số.

---

## 3. PHÂN TÍCH TỆP 3: `netfix2/search.php` (L1 - L93)

### 3.1. Bảng Ánh Xạ Khối Lệnh ($S_i$) và Nút Nhánh ($D_i$)
* `SRCH_S1` (L1 - L8): `session_start(); require 'db.php';`
* `SRCH_S2` (L56 - L58): `$query = isset($_GET['query']) ? trim($_GET['query']) : ''; $genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';`
* `SRCH_D1` (L60): `if (!empty($query))`
  * `SRCH_D1_F`: Bỏ qua khối truy vấn, `$searchResults = []`
  * `SRCH_D1_T`: Tiếp tục `SRCH_S3`
* `SRCH_S3` (L61 - L65): Tạo câu lệnh SQL tìm kiếm: `title LIKE ? OR description LIKE ? OR genre LIKE ?...`
* `SRCH_D2` (L66): `if (!empty($genre))`
  * `SRCH_D2_T`: Nối thêm `AND genre = ?` và `bind_param("ssssss", ...)`
  * `SRCH_D2_F`: Chỉ tìm theo từ khóa và `bind_param("sssss", ...)`
* `SRCH_D3` (L72): `if ($stmt === false) -> die(...)` (**DEFECT WB-DEF-08: die() in production**)
* `SRCH_D4` (L83): `if (!$stmt->execute()) -> die(...)` (**DEFECT WB-DEF-08: die() in production**)

* **Độ phức tạp Cyclomatic $V(G)$:**
  * $P = 14 \implies V(G) = P + 1 = 15$.
  * **15 Basis Paths** (Bao gồm các trường hợp: Từ khóa rỗng, Từ khóa hợp lệ không có thể loại, Từ khóa hợp lệ kèm thể loại, SQL injection fuzzing, và xử lý ký tự đặc biệt).
  * **Phát hiện DEFECT WB-DEF-09:** Nếu người dùng chỉ truyền `genre` mà không truyền `query` (`search.php?genre=Movie`), dòng 60 `if (!empty($query))` trả về false khiến bộ lọc thể loại bị chết hoàn toàn (Dead filter).

---

## 4. PHÂN TÍCH TỆP 4: `netfix2/watch.php` (L1 - L201)

### 4.1. Bảng Ánh Xạ Khối Lệnh ($S_i$) và Nút Nhánh ($D_i$)
* `WTC_S1` (L1 - L9): `session_start(); require 'db.php';`
* `WTC_S3` (L56 - L57): `movie_id = (int)$_GET['id']; episode_id = (int)$_GET['episode'];`
* `WTC_D1` (L59): `if ($movie_id <= 0)`
  * `WTC_D1_T`: Báo lỗi ID phim không hợp lệ
  * `WTC_D1_F`: Tiếp tục truy vấn phim
* `WTC_D2` (L71): `if (!$movie)`
  * `WTC_D2_T`: Báo lỗi Phim không tồn tại
  * `WTC_D2_F`: Tiếp tục kiểm tra quyền VIP
* `WTC_D3_TERNARY` (L75): `$user_is_vip = isset($_SESSION['user_id']) ? false : false;`
  * **TARGET BUG-01 (CONFIRMED - DEFECT WB-DEF-01):** Biểu thức 3 ngôi có cả 2 nhánh đều trả về `false`, tạo ra nhánh chết logic.
* `WTC_D3` (L76): `if (isset($_SESSION['user_id']))`
  * `WTC_D3_T`: Truy vấn `SELECT is_vip FROM users WHERE id = ?`
  * `WTC_D3_F`: Khách vãng lai
* `WTC_D4` (L87): `if ($movie['is_vip'] == 1 && !$user_is_vip)`
  * `WTC_D4_T`: Chặn truy cập và hiển thị thông báo yêu cầu nâng cấp gói VIP
  * `WTC_D4_F`: Cho phép xem phim
* `WTC_D5` (L98): `if (isset($_SESSION['user_id']) && $movie_id > 0)`
  * `WTC_D6` (L108): `if ($result_check_history->num_rows > 0)`
    * `WTC_D6_T`: UPDATE `last_watched_at`
    * `WTC_D6_F`: INSERT bản ghi lịch sử xem mới
* `WTC_D7` (L126): `if ($movie['genre'] === 'Series')`
  * `WTC_D8` (L140): `if ($ep['is_vip'] == 1 && !$user_is_vip) -> $ep['video_path'] = null;`
* `WTC_D9` (L154): `if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text']))`
  * `WTC_D10` (L155): `if (!isset($_SESSION['user_id'])) -> Báo lỗi cần đăng nhập`
  * `WTC_D11` (L159): `if ($comment !== '') -> INSERT comments`
  * **DEFECT WB-DEF-05:** Không kiểm tra độ dài tối đa `strlen($comment) > 500`.
  * **DEFECT WB-DEF-06:** Không set thông báo lỗi khi bình luận toàn khoảng trắng (`$comment === ''`).

### 4.2. Độ Phức Tạp Cyclomatic $V(G)$ & 35 Basis Paths
* $P = 34 \implies V(G) = P + 1 = 35$.

### 4.3. Ma Trận Tổ Hợp Phân Quyền VIP (4 Tổ hợp Bắt buộc)
| Tổ Hợp | `movies.is_vip` | Trạng thái Người Dùng (`user_is_vip`) | Nhánh Kích Hoạt | Kết Quả Mong Đợi | Kết Quả Thực Tế | Ghi Chú |
| :---: | :---: | :---: | :---: | :--- | :--- | :--- |
| **Combo 1** | 0 (Phim Thường) | 0 (Khách / User Thường) | `WTC_D4_F` | Hiển thị player phát video bình thường | PASS (200 OK) | Hợp lệ |
| **Combo 2** | 0 (Phim Thường) | 1 (User VIP) | `WTC_D4_F` | Hiển thị player phát video bình thường | PASS (200 OK) | Hợp lệ |
| **Combo 3** | 1 (Phim VIP) | 0 (Khách / User Thường) | `WTC_D4_T` | Chặn video, hiện thông báo nâng cấp VIP | PASS (Chặn đúng) | Hợp lệ |
| **Combo 4** | 1 (Phim VIP) | 1 (User VIP) | `WTC_D4_F` | Hiển thị player xem phim VIP chất lượng cao | PASS (200 OK) | Hợp lệ |

