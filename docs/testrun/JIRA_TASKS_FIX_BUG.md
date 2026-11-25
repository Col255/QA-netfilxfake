# DANH SÁCH CÁC TASK FIX BUG TRÊN JIRA (JIRA ISSUE TEMPLATES)
## Dự án: QA Testing 002 | Project Key: KAN
## Nguồn lỗi: Báo cáo Kiểm thử Hộp trắng (White-Box) & BVA cho Phân hệ FE2 (MOV) & FE3 (INT)

> **Mục đích:** Cung cấp đầy đủ thông tin chuẩn chỉnh (Tiêu đề, Loại Issue, Phân hệ, Mức độ ưu tiên, Mô tả, Các bước tái hiện, Kết quả thực tế, Kết quả mong đợi, Đề xuất sửa code, Test Case nghiệm thu) để thành viên chỉ việc sao chép (copy-paste) tạo task trực tiếp trên Jira (`https://lochnt0228.atlassian.net/jira/software/projects/KAN`).

---

## BẢNG TỔNG QUAN 9 TASK FIX BUG

| STT | Mã Task Đề Xuất | Tiêu đề Task Jira | Phân hệ | Mức độ Ưu tiên | File:Line Bị Lỗi | Mã Defect |
| :---: | :--- | :--- | :---: | :---: | :--- | :---: |
| 1 | **TASK-FIX-01** | `[FE2-MOV] Fix nhánh chết gán biến $user_is_vip tại watch.php (BUG-01)` | `FE2 - MOV` | **High** | `netfix2/watch.php:75` | `WB-DEF-01` |
| 2 | **TASK-FIX-02** | `[FE3-INT] Kiểm tra số dòng bị ảnh hưởng ($stmt->affected_rows) khi tăng view tại api/view_counter.php` | `FE3 - INT` | **Medium** | `netfix2/api/view_counter.php:10` | `WB-DEF-02` |
| 3 | **TASK-FIX-03** | `[FE3-INT] Bổ sung header Content-Type application/json cho api/view_counter.php` | `FE3 - INT` | **Low** | `netfix2/api/view_counter.php:1,10` | `WB-DEF-03` |
| 4 | **TASK-FIX-04** | `[FE2-MOV] Chuẩn hóa thứ tự tham số hàm getMovies() để triệt tiêu lỗi PHP 8 Deprecated trong index.php` | `FE2 - MOV` | **Low** | `netfix2/index.php:31` | `WB-DEF-04` |
| 5 | **TASK-FIX-05** | `[FE2-MOV] Bổ sung ràng buộc độ dài tối đa 500 ký tự (BVA Max) cho bình luận tại watch.php` | `FE2 - MOV` | **Medium** | `netfix2/watch.php:158` | `WB-DEF-05` |
| 6 | **TASK-FIX-06** | `[FE2-MOV] Bổ sung thông báo lỗi $comment_error khi gửi bình luận rỗng hoặc toàn khoảng trắng tại watch.php` | `FE2 - MOV` | **Low** | `netfix2/watch.php:159` | `WB-DEF-06` |
| 7 | **TASK-FIX-07** | `[FE3-INT] Bổ sung kiểm tra số nguyên dương > 0 khi xóa lịch sử xem tại watch_history.php` | `FE3 - INT` | **Low** | `netfix2/watch_history.php:13` | `WB-DEF-07` |
| 8 | **TASK-FIX-08** | `[FE2-MOV] Thay thế lệnh die() bằng xử lý lỗi an toàn không làm lộ schema SQL tại search.php` | `FE2 - MOV` | **Medium** | `netfix2/search.php:74,86` | `WB-DEF-08` |
| 9 | **TASK-FIX-09** | `[FE2-MOV] Tách độc lập điều kiện lọc thể loại (genre) không phụ thuộc từ khóa (query) tại search.php` | `FE2 - MOV` | **Low** | `netfix2/search.php:60,66` | `WB-DEF-09` |

---

## CHI TIẾT NỘI DUNG TỪNG TASK ĐỂ TẠO TRÊN JIRA

---

### TASK 1: FIX NHÁNH CHẾT GÁN BIẾN `$user_is_vip` (TARGET BUG-01)
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Fix nhánh chết gán biến $user_is_vip tại watch.php (BUG-01)`
* **Component:** `FE2 - MOV`
* **Priority:** `High`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-01 (Xác nhận lại BUG-01 trong Cẩm nang White-Box)
* Tệp tin: netfix2/watch.php (Dòng 75)
* Phân hệ: FE2 - Movies & Streaming

h3. 2. Mô tả hiện tượng lỗi (Bug Description)
Tại dòng 75 của tệp netfix2/watch.php, biến $user_is_vip được gán qua toán tử 3 ngôi:
$user_is_vip = isset($_SESSION['user_id']) ? false : false;
Trong biểu thức này, cả 2 nhánh True và False đều trả về cùng một giá trị false, tạo ra một điều kiện nhánh chết (Dead Condition / Dead Branch). Mặc dù khối if phía dưới có thể gán lại giá trị khi đã đăng nhập, nhưng đối với khách vãng lai, đây là biểu thức dư thừa vi phạm tiêu chuẩn Clean Code và bị công cụ phân tích tĩnh (Qodana/SonarCloud) cảnh báo nghiêm trọng.

h3. 3. Các bước tái hiện (Steps to Reproduce)
1. Mở trình duyệt ẩn danh (Không có Session đăng nhập).
2. Truy cập URL: GET http://localhost/netfix2/watch.php?id=1
3. Quan sát luồng điều khiển thực thi dòng 75.

h3. 4. Kết quả thực tế (Actual Result)
Chạy qua nhánh 3 ngôi vô nghĩa: cả 2 ngả rẽ đều cho kết quả false.

h3. 5. Kết quả mong đợi (Expected Result)
Khởi tạo biến tường minh, an toàn: $user_is_vip = false; trước khi kiểm tra phiên làm việc.

h3. 6. Hướng dẫn sửa code (Proposed Fix)
Thay thế dòng 75 bằng:
$user_is_vip = false;

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-010, WB-TC-MOV-011, WB-TC-MOV-074
```

---

### TASK 2: KIỂM TRA SỐ DÒNG BỊ ẢNH HƯỞNG KHI TĂNG VIEW
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE3-INT] Kiểm tra số dòng bị ảnh hưởng ($stmt->affected_rows) khi tăng view tại api/view_counter.php`
* **Component:** `FE3 - INT`
* **Priority:** `Medium`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-02
* Tệp tin: netfix2/api/view_counter.php (Dòng 6-11)
* Phân hệ: FE3 - Interactions & Engagement

h3. 2. Mô tả hiện tượng lỗi
Khi gửi request API tăng lượt xem với một ID phim không tồn tại trong cơ sở dữ liệu (ví dụ: id=999999), câu lệnh UPDATE movies SET view_count = view_count + 1 WHERE id = 999999 vẫn chạy thành công (nhưng affected_rows = 0).
Tuy nhiên, API lập tức phản hồi: {"success": true, "movie_id": 999999}. Đây là lỗi logic trả về kết quả thành công ảo cho đối tượng không tồn tại.

h3. 3. Các bước tái hiện
1. Sử dụng Postman hoặc Trình duyệt gửi request:
   GET http://localhost/netfix2/api/view_counter.php?id=999999
2. Kiểm tra chuỗi JSON trả về.

h3. 4. Kết quả thực tế
Server trả về: {"success": true, "movie_id": 999999} dù phim không có trong CSDL.

h3. 5. Kết quả mong đợi
Nếu phim không tồn tại trong CSDL, API phải trả về:
{"success": false, "error": "Phim không tồn tại"}

h3. 6. Hướng dẫn sửa code
Sửa khối xử lý tại netfix2/api/view_counter.php:
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'movie_id' => $movie_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Phim không tồn tại']);
}

h3. 7. Test Case nghiệm thu
* WB-TC-FE3-005
```

---

### TASK 3: BỔ SUNG HEADER CONTENT-TYPE CHO VIEW_COUNTER API
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE3-INT] Bổ sung header Content-Type application/json cho api/view_counter.php`
* **Component:** `FE3 - INT`
* **Priority:** `Low`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-03
* Tệp tin: netfix2/api/view_counter.php (Dòng 1)
* Phân hệ: FE3 - Interactions & Engagement

h3. 2. Mô tả hiện tượng lỗi
Tệp api/view_counter.php xuất dữ liệu qua hàm json_encode() nhưng không khai báo header Content-Type. Do đó, Web Server (Apache/PHP) mặc định trả về Content-Type: text/html; charset=UTF-8. Trình duyệt hoặc ứng dụng di động sẽ hiểu nhầm đây là tài liệu HTML thay vì REST JSON.

h3. 3. Các bước tái hiện
1. Bật Network tab trên F12 hoặc gọi Postman tới:
   GET http://localhost/netfix2/api/view_counter.php?id=1
2. Kiểm tra Headers trong mục Response.

h3. 4. Kết quả thực tế
Content-Type là text/html.

h3. 5. Kết quả mong đợi
Content-Type phải là application/json; charset=utf-8.

h3. 6. Hướng dẫn sửa code
Thêm ngay sau thẻ <?php ở dòng 2:
header('Content-Type: application/json; charset=utf-8');

h3. 7. Test Case nghiệm thu
* WB-TC-FE3-001, WB-TC-FE3-002
```

---

### TASK 4: CHUẨN HÓA THỨ TỰ THAM SỐ HÀM `getMovies()` TRONG `index.php`
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Chuẩn hóa thứ tự tham số hàm getMovies() để triệt tiêu lỗi PHP 8 Deprecated trong index.php`
* **Component:** `FE2 - MOV`
* **Priority:** `Low`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-04
* Tệp tin: netfix2/index.php (Dòng 31)
* Phân hệ: FE2 - Movies & Discovery

h3. 2. Mô tả hiện tượng lỗi
Hàm getMovies được khai báo như sau:
function getMovies($conn, $genre = null, $limit)
Tham số $genre có giá trị mặc định (= null) nhưng lại đứng TRƯỚC tham số bắt buộc $limit. Từ phiên bản PHP 8.0 trở lên, cú pháp này bị coi là Deprecated và kích hoạt cảnh báo:
PHP Deprecated: Optional parameter $genre declared before required parameter $limit is implicitly treated as a required parameter.

h3. 3. Các bước tái hiện
1. Bật hiển thị lỗi PHP (ini_set('display_errors', 1); error_reporting(E_ALL);).
2. Chạy index.php trên môi trường PHP 8.1 hoặc PHP 8.2.

h3. 4. Kết quả thực tế
Phát sinh cảnh báo Deprecated trong Error Log.

h3. 5. Kết quả mong đợi
Chạy sạch 100%, không phát sinh bất kỳ cảnh báo Deprecated nào.

h3. 6. Hướng dẫn sửa code
Đổi thứ tự tham số để tham số bắt buộc $limit đứng trước:
function getMovies($conn, $limit, $genre = null)
Đồng thời cập nhật lại các lời gọi hàm getMovies tại dòng 104, 106, 107, 108, 109.

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-002
```

---

### TASK 5: BỔ SUNG RÀNG BUỘC ĐỘ DÀI TỐI ĐA CHO BÌNH LUẬN (BVA MAX)
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Bổ sung ràng buộc độ dài tối đa 500 ký tự (BVA Max) cho bình luận tại watch.php`
* **Component:** `FE2 - MOV`
* **Priority:** `Medium`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-05
* Tệp tin: netfix2/watch.php (Dòng 158-160)
* Phân hệ: FE2 - Movies & Streaming

h3. 2. Mô tả hiện tượng lỗi
Khi người dùng đã đăng nhập gửi bình luận phim qua POST /watch.php, hệ thống chỉ kiểm tra:
if ($comment !== '')
Không hề có bất kỳ câu lệnh kiểm tra độ dài tối đa nào. Khi người dùng cố tình gửi bình luận chứa 10,000 ký tự (BVA Max vượt ngưỡng), hệ thống vẫn execute lệnh INSERT vào CSDL, gây nguy cơ tràn bộ nhớ và phình cơ sở dữ liệu.

h3. 3. Các bước tái hiện
1. Đăng nhập tài khoản User thường.
2. Mở phim id=1: http://localhost/netfix2/watch.php?id=1
3. Nhập bình luận gồm 10,000 ký tự và nhấn Gửi bình luận.

h3. 4. Kết quả thực tế
Bình luận 10,000 ký tự được chèn thẳng vào bảng comments.

h3. 5. Kết quả mong đợi
Hệ thống phải chặn lại và hiển thị thông báo lỗi:
"Nội dung bình luận không được vượt quá 500 ký tự."

h3. 6. Hướng dẫn sửa code
Thêm kiểm tra độ dài bằng hàm mb_strlen:
if (mb_strlen($comment) > 500) {
    $comment_error = "Nội dung bình luận không được vượt quá 500 ký tự.";
} else {
    // Thực thi INSERT CSDL
}

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-025, WB-TC-MOV-083
```

---

### TASK 6: BỔ SUNG THÔNG BÁO LỖI KHI BÌNH LUẬN TOÀN KHOẢNG TRẮNG
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Bổ sung thông báo lỗi $comment_error khi gửi bình luận rỗng hoặc toàn khoảng trắng tại watch.php`
* **Component:** `FE2 - MOV`
* **Priority:** `Low`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-06
* Tệp tin: netfix2/watch.php (Dòng 159)
* Phân hệ: FE2 - Movies & Streaming

h3. 2. Mô tả hiện tượng lỗi
Tại dòng 159 của watch.php:
$comment = trim($_POST['comment_text']);
if ($comment !== '') {
    // INSERT vào DB
}
Nếu người dùng nhập toàn dấu cách ("   "), hàm trim() biến $comment thành chuỗi rỗng (''). Điều kiện if ($comment !== '') trả về false.
Tuy nhiên, khối code này KHÔNG CÓ NHÁNH ELSE, biến $comment_error vẫn giữ nguyên giá trị rỗng. Người dùng bấm gửi xong thấy trang reload nhưng không hề có thông báo nào giải thích vì sao bình luận không xuất hiện.

h3. 3. Các bước tái hiện
1. Đăng nhập User thường, vào trang xem phim watch.php?id=1.
2. Gõ 5 dấu cách vào ô bình luận và bấm nút Gửi.

h3. 4. Kết quả thực tế
Trang web reload trong im lặng, không có thông báo lỗi người dùng.

h3. 5. Kết quả mong đợi
Hiển thị thông báo màu đỏ: "Nội dung bình luận không được để trống."

h3. 6. Hướng dẫn sửa code
Bổ sung nhánh else tại dòng 165 của watch.php:
} else {
    $comment_error = "Nội dung bình luận không được để trống.";
}

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-024, WB-TC-MOV-084
```

---

### TASK 7: RÀNG BUỘC SỐ NGUYÊN DƯƠNG KHI XÓA LỊCH SỬ XEM PHIM
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE3-INT] Bổ sung kiểm tra số nguyên dương > 0 khi xóa lịch sử xem tại watch_history.php`
* **Component:** `FE3 - INT`
* **Priority:** `Low`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-07
* Tệp tin: netfix2/watch_history.php (Dòng 13)
* Phân hệ: FE3 - Interactions & Engagement

h3. 2. Mô tả hiện tượng lỗi
Đoạn code kiểm tra yêu cầu xóa lịch sử:
if (isset($_GET['delete']) && is_numeric($_GET['delete']))
Do hàm is_numeric('-5') trả về true, nên nếu kẻ xấu hoặc bot gọi link ?delete=-5, hệ thống vẫn vượt qua điều kiện và chuẩn bị câu lệnh DELETE FROM watch_history WHERE user_id = ? AND movie_id = -5. Việc truy vấn CSDL với khóa ngoại âm là lãng phí tài nguyên và vi phạm nguyên tắc kiểm tra biên (BVA Min-1).

h3. 3. Các bước tái hiện
1. Đăng nhập User thường.
2. Truy cập: GET http://localhost/netfix2/watch_history.php?delete=-5

h3. 4. Kết quả thực tế
Hệ thống kết nối CSDL và chạy lệnh DELETE với movie_id = -5 rồi redirect.

h3. 5. Kết quả mong đợi
Nếu tham số delete <= 0, hệ thống phải bỏ qua lệnh xóa và không kết nối CSDL thực hiện lệnh DELETE vô nghĩa.

h3. 6. Hướng dẫn sửa code
Sửa dòng 13 thành:
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && (int)$_GET['delete'] > 0)

h3. 7. Test Case nghiệm thu
* WB-TC-FE3-017
```

---

### TASK 8: THAY THẾ LỆNH `die()` BẰNG XỬ LÝ LỖI AN TOÀN TRONG `search.php`
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Thay thế lệnh die() bằng xử lý lỗi an toàn không làm lộ schema SQL tại search.php`
* **Component:** `FE2 - MOV`
* **Priority:** `Medium`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-08
* Tệp tin: netfix2/search.php (Dòng 74, 86)
* Phân hệ: FE2 - Movies & Discovery

h3. 2. Mô tả hiện tượng lỗi
Tại dòng 74 và 86:
die("Lỗi chuẩn bị truy vấn: " . $conn->error);
die("Lỗi thực thi truy vấn: " . $stmt->error);
Lệnh die() làm ngắt trang web đột ngột (White Screen of Death) và làm lộ trực tiếp thông báo lỗi SQL của MySQL ra màn hình người dùng, vi phạm tiêu chuẩn bảo mật OWASP về Information Exposure.

h3. 3. Các bước tái hiện
1. Cố ý truyền dữ liệu gây lỗi prepare hoặc ngắt kết nối DB trong quá trình search.

h3. 4. Kết quả thực tế
Màn hình trắng xóa hiển thị dòng chữ lỗi chi tiết của MySQL.

h3. 5. Kết quả mong đợi
Ghi lỗi vào error_log() của server và hiển thị giao diện thông báo lỗi thân thiện: "Hệ thống tìm kiếm tạm thời gián đoạn. Vui lòng thử lại sau."

h3. 6. Hướng dẫn sửa code
Thay thế lệnh die() bằng:
error_log("Search SQL Error: " . $conn->error);
$searchResults = [];
$error_message = "Không thể thực hiện tìm kiếm vào lúc này.";

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-035
```

---

### TASK 9: TÁCH ĐỘC LẬP BỘ LỌC THỂ LOẠI (GENRE) TRONG `search.php`
* **Issue Type:** `Bug`
* **Summary (Tiêu đề):** `[FE2-MOV] Tách độc lập điều kiện lọc thể loại (genre) không phụ thuộc từ khóa (query) tại search.php`
* **Component:** `FE2 - MOV`
* **Priority:** `Low`
* **Description (Mô tả):**
```text
h3. 1. Thông tin chung
* Mã Defect: WB-DEF-09
* Tệp tin: netfix2/search.php (Dòng 60, 66)
* Phân hệ: FE2 - Movies & Discovery

h3. 2. Mô tả hiện tượng lỗi
Trong search.php, khối xử lý tìm kiếm bắt đầu bằng:
if (!empty($query)) {
    // ...
    if (!empty($genre)) {
        $sql .= " AND genre = ?";
    }
}
Điều này dẫn tới việc: Nếu người dùng truy cập trang tìm kiếm và chọn thể loại (ví dụ: search.php?genre=Action) mà để trống ô tìm kiếm (query=''), thì điều kiện if (!empty($query)) trả về false.
Hệ thống bỏ qua toàn bộ khối SQL, $searchResults trả về mảng rỗng. Bộ lọc thể loại bị chết (Dead Filter) khi không có từ khóa đi kèm.

h3. 3. Các bước tái hiện
1. Truy cập link: GET http://localhost/netfix2/search.php?genre=Movie

h3. 4. Kết quả thực tế
Không có phim nào được hiển thị ($searchResults = []).

h3. 5. Kết quả mong đợi
Hiển thị danh sách tất cả các phim thuộc thể loại Movie.

h3. 6. Hướng dẫn sửa code
Cho phép tìm kiếm khi có query HOẶC có genre:
if (!empty($query) || !empty($genre)) {
    // Xây dựng câu lệnh WHERE động theo từng trường có dữ liệu
}

h3. 7. Test Case nghiệm thu
* WB-TC-MOV-033, WB-TC-MOV-068
```
