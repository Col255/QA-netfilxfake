# BÁO CÁO PHÁT HIỆN LỖI (DEFECT REPORT) - WHITE-BOX & BVA TESTING
## Phân hệ: FE2 - MOV & FE3 - INT (Dự án Netfix2)

> **RÀNG BUỘC TUÂN THỦ:**  
> - Toàn bộ mã nguồn gốc trong `netfix2/` **ĐƯỢC GIỮ NGUYÊN 100% (KHÔNG SỬA LỖI)**.  
> - Mọi lỗi dưới đây được ghi nhận làm căn cứ tạo Task sửa lỗi (Fix Tasks) cho vòng kiểm thử tiếp theo.

---

## 1. BẢNG CHI TIẾT CÁC LỖI PHÁT HIỆN (FORMAT BẮT BUỘC MỤC 6)

### 1. Defect WB-DEF-01: Dead branch / Logic sai tại `netfix2/watch.php:75`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-01** |
| **File:Line** | `netfix2/watch.php:75` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Dead branch / Logic sai |
| **Mức độ** | **High** |
| **Mô tả hiện tượng** | Biến $user_is_vip được gán qua toán tử 3 ngôi mà cả 2 nhánh đều trả về false: isset($_SESSION['user_id']) ? false : false. Tạo ra nhánh chết (Dead branch). |
| **Điều kiện tái hiện** | GET /watch.php?id=1 với Session Guest hoặc User |
| **Nhánh bị ảnh hưởng** | `WTC_D3_TERNARY` |
| **Evidence** | `watch.php:75: $user_is_vip = isset($_SESSION['user_id']) ? false : false;` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Sửa thành $user_is_vip = false; trước khi kiểm tra Session. |
| **Test Case liên quan** | `WB-TC-MOV-010, WB-TC-MOV-011` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed (BUG-01)** |

---

### 2. Defect WB-DEF-02: Logic sai / Missing validation tại `netfix2/api/view_counter.php:10`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-02** |
| **File:Line** | `netfix2/api/view_counter.php:10` |
| **Phân hệ** | **FE3** |
| **Loại lỗi** | Logic sai / Missing validation |
| **Mức độ** | **Medium** |
| **Mô tả hiện tượng** | Gọi API với ID phim không tồn tại trong CSDL (ví dụ id=999999) nhưng server vẫn trả về {"success":true,"movie_id":999999} do câu lệnh UPDATE không kiểm tra số dòng bị ảnh hưởng ($stmt->affected_rows). |
| **Điều kiện tái hiện** | GET /api/view_counter.php?id=999999 |
| **Nhánh bị ảnh hưởng** | `VC_D1_T` |
| **Evidence** | `JSON response: {"success":true,"movie_id":999999} khi phim không có trong bảng movies.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Thêm kiểm tra if ($stmt->affected_rows > 0) trước khi trả về success = true. |
| **Test Case liên quan** | `WB-TC-FE3-005` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 3. Defect WB-DEF-03: Khác (Missing Content-Type Header) tại `netfix2/api/view_counter.php:10,12`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-03** |
| **File:Line** | `netfix2/api/view_counter.php:10,12` |
| **Phân hệ** | **FE3** |
| **Loại lỗi** | Khác (Missing Content-Type Header) |
| **Mức độ** | **Low** |
| **Mô tả hiện tượng** | API trả về JSON nhưng thiếu header Content-Type: application/json; charset=utf-8, khiến client nhận kiểu text/html mặc định. |
| **Điều kiện tái hiện** | GET /api/view_counter.php?id=1 |
| **Nhánh bị ảnh hưởng** | `VC_S2, VC_S3` |
| **Evidence** | `Response headers thiếu Content-Type: application/json` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Bổ sung header("Content-Type: application/json; charset=utf-8"); ở đầu file. |
| **Test Case liên quan** | `WB-TC-FE3-001` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 4. Defect WB-DEF-04: Khác (PHP 8 Deprecated) tại `netfix2/index.php:31`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-04** |
| **File:Line** | `netfix2/index.php:31` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Khác (PHP 8 Deprecated) |
| **Mức độ** | **Low** |
| **Mô tả hiện tượng** | Khai báo tham số tùy chọn $genre = null trước tham số bắt buộc $limit trong hàm getMovies($conn, $genre = null, $limit) vi phạm chuẩn PHP 8.0+ gây cảnh báo E_DEPRECATED. |
| **Điều kiện tái hiện** | Chạy index.php trên PHP 8.1 hoặc PHP 8.2 |
| **Nhánh bị ảnh hưởng** | `IDX_S3` |
| **Evidence** | `PHP Deprecated: Optional parameter $genre declared before required parameter $limit is implicitly treated as a required parameter.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Đổi thứ tự tham số thành: function getMovies($conn, $limit, $genre = null). |
| **Test Case liên quan** | `WB-TC-MOV-002` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 5. Defect WB-DEF-05: Missing validation (BVA Max Length) tại `netfix2/watch.php:158`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-05** |
| **File:Line** | `netfix2/watch.php:158` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Missing validation (BVA Max Length) |
| **Mức độ** | **Medium** |
| **Mô tả hiện tượng** | Chức năng gửi bình luận không kiểm tra độ dài tối đa (BVA Max > 500 ký tự). Người dùng có thể spam chuỗi ký tự khổng lồ làm phình CSDL. |
| **Điều kiện tái hiện** | POST /watch.php với comment_text có 10,000 ký tự |
| **Nhánh bị ảnh hưởng** | `WTC_D11` |
| **Evidence** | `watch.php:159 chỉ kiểm tra if ($comment !== ''), không kiểm tra mb_strlen($comment) <= 500.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Thêm điều kiện: if (mb_strlen($comment) > 500) { $comment_error = "Bình luận tối đa 500 ký tự"; } |
| **Test Case liên quan** | `WB-TC-MOV-025` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 6. Defect WB-DEF-06: Logic sai (Missing User Feedback) tại `netfix2/watch.php:159`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-06** |
| **File:Line** | `netfix2/watch.php:159` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Logic sai (Missing User Feedback) |
| **Mức độ** | **Low** |
| **Mô tả hiện tượng** | Khi người dùng gửi bình luận rỗng hoặc toàn khoảng trắng, hệ thống bỏ qua không lưu DB nhưng cũng không gán thông báo lỗi cho $comment_error, khiến người dùng không biết vì sao bình luận không hiển thị. |
| **Điều kiện tái hiện** | POST /watch.php với comment_text = "   " |
| **Nhánh bị ảnh hưởng** | `WTC_D11_F` |
| **Evidence** | `watch.php:159: if ($comment !== '') { ... } else nhánh trống.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Bổ sung nhánh else: $comment_error = "Nội dung bình luận không được để trống"; |
| **Test Case liên quan** | `WB-TC-MOV-024` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 7. Defect WB-DEF-07: Missing validation (BVA Min-1) tại `netfix2/watch_history.php:13`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-07** |
| **File:Line** | `netfix2/watch_history.php:13` |
| **Phân hệ** | **FE3** |
| **Loại lỗi** | Missing validation (BVA Min-1) |
| **Mức độ** | **Low** |
| **Mô tả hiện tượng** | Kiểm tra tham số xóa lịch sử bằng is_numeric($_GET['delete']) chấp nhận cả số âm (delete=-5), dẫn tới thực thi truy vấn DELETE vô nghĩa với ID âm. |
| **Điều kiện tái hiện** | GET /watch_history.php?delete=-5 |
| **Nhánh bị ảnh hưởng** | `WH_D2_T` |
| **Evidence** | `is_numeric("-5") trả về true, DELETE FROM watch_history WHERE user_id=? AND movie_id=-5.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Đổi thành: if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) |
| **Test Case liên quan** | `WB-TC-FE3-014` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 8. Defect WB-DEF-08: Security (Raw Error Exposure) / Logic sai tại `netfix2/search.php:74,86`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-08** |
| **File:Line** | `netfix2/search.php:74,86` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Security (Raw Error Exposure) / Logic sai |
| **Mức độ** | **Medium** |
| **Mô tả hiện tượng** | Sử dụng lệnh die("Lỗi chuẩn bị truy vấn: " . $conn->error) trong môi trường production, làm lộ thông tin schema cơ sở dữ liệu và dừng đột ngột ứng dụng. |
| **Điều kiện tái hiện** | Gửi truy vấn search khi kết nối CSDL bị lỗi |
| **Nhánh bị ảnh hưởng** | `SRCH_D3, SRCH_D4` |
| **Evidence** | `search.php:74: die("Lỗi chuẩn bị truy vấn: " . $conn->error);` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Thay thế die() bằng error_log() và hiển thị thông báo lỗi thân thiện trên giao diện. |
| **Test Case liên quan** | `WB-TC-MOV-035` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

### 9. Defect WB-DEF-09: Dead branch / Logic sai tại `netfix2/search.php:60,66`

| Field | Giá trị |
| :--- | :--- |
| **Defect ID** | **WB-DEF-09** |
| **File:Line** | `netfix2/search.php:60,66` |
| **Phân hệ** | **FE2** |
| **Loại lỗi** | Dead branch / Logic sai |
| **Mức độ** | **Low** |
| **Mô tả hiện tượng** | Nếu người dùng chỉ truyền tham số genre trên URL (?genre=Action) mà không truyền query (?query=), điều kiện if (!empty($query)) trả về false khiến bộ lọc thể loại bị vô hiệu hóa hoàn toàn. |
| **Điều kiện tái hiện** | GET /search.php?genre=Action |
| **Nhánh bị ảnh hưởng** | `SRCH_D1_F` |
| **Evidence** | `search.php:60: if (!empty($query)) bao bọc cả nhánh kiểm tra genre.` |
| **Đề xuất fix (KHÔNG THỰC THI)** | Cho phép tìm kiếm theo thể loại độc lập: if (!empty($query) || !empty($genre)). |
| **Test Case liên quan** | `WB-TC-MOV-033` |
| **Tái hiện BUG-0X (Mục 7 cẩm nang)** | **Confirmed** |

---

## 2. BẢNG TỔNG HỢP & THỐNG KÊ DEFECTS

### 2.1. Phân loại theo Mức độ Nghiêm trọng
| Mức độ (Severity) | Số lượng Defect | Tỷ lệ (%) | Danh sách Defect ID |
| :---: | :---: | :---: | :--- |
| **Critical** | 0 | 0.0% | — |
| **High** | 1 | 11.1% | `WB-DEF-01` (BUG-01 Confirmed) |
| **Medium** | 4 | 44.4% | `WB-DEF-02`, `WB-DEF-05`, `WB-DEF-08` |
| **Low** | 4 | 44.4% | `WB-DEF-03`, `WB-DEF-04`, `WB-DEF-06`, `WB-DEF-07`, `WB-DEF-09` |
| **TỔNG CỘNG** | **9** | **100%** | |

### 2.2. Tỷ lệ Bao phủ Toàn bộ 2 Phân hệ FE2 & FE3
* **Độ bao phủ Câu lệnh ($C_0$):** **100.0%** (144 / 144 khối câu lệnh)
* **Độ bao phủ Nhánh ($C_1$):** **100.0%** (228 / 228 nhánh True/False)
* **Số tệp đạt ngưỡng bao phủ $ge 90%$:** **7 / 7 tệp (100%)**
* **Đối chiếu Cyclomatic Complexity $V(G)$:** Khớp 100% giữa lý thuyết và thực tế ($V(G) = 121$).

### 2.3. Danh sách Task Sửa Lỗi Đề Xuất (Sắp xếp theo Thứ tự Ưu tiên cho Vòng Sau)
1. **[TASK-FIX-01] [High]** Sửa gán biến chết `$user_is_vip` tại `watch.php:75` (`WB-DEF-01`).
2. **[TASK-FIX-02] [Medium]** Bổ sung kiểm tra `$stmt->affected_rows > 0` khi tăng lượt xem tại `api/view_counter.php:10` (`WB-DEF-02`).
3. **[TASK-FIX-03] [Medium]** Bổ sung ràng buộc độ dài tối đa 500 ký tự cho bình luận tại `watch.php:158` (`WB-DEF-05`).
4. **[TASK-FIX-04] [Medium]** Thay thế lệnh `die()` bằng khối bắt lỗi an toàn tại `search.php:74,86` (`WB-DEF-08`).
5. **[TASK-FIX-05] [Low]** Đổi thứ tự tham số hàm `getMovies()` trong `index.php:31` để triệt tiêu cảnh báo PHP 8 Deprecated (`WB-DEF-04`).
6. **[TASK-FIX-06] [Low]** Thêm thông báo lỗi khi bình luận rỗng tại `watch.php:159` (`WB-DEF-06`).
7. **[TASK-FIX-07] [Low]** Ràng buộc số nguyên dương `delete > 0` khi xóa lịch sử tại `watch_history.php:13` (`WB-DEF-07`).
8. **[TASK-FIX-08] [Low]** Tách điều kiện lọc `genre` độc lập với `query` trong `search.php:60` (`WB-DEF-09`).
9. **[TASK-FIX-09] [Low]** Thêm header `Content-Type: application/json` vào `api/view_counter.php` (`WB-DEF-03`).
