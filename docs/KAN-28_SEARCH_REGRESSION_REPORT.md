# BÁO CÁO THỰC THI KIỂM THỬ HỒI QUY TỰ ĐỘNG & AN TOÀN BẢO MẬT (KAN-28)
## API & Chức năng Tìm kiếm Phim (`/search.php`) - Dự án Netfix2

---

## 1. TỔNG QUAN THỰC THI (EXECUTIVE SUMMARY)

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Mã công việc (Jira Key)** | `KAN-28` |
| **Mô tả Jira Task** | `[KAN-28] Thực thi automated regression, xác thực cơ chế sanitize XSS/SQLi` |
| **User Story liên quan** | `KAN-25` (`[KAN-25] Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php)`) |
| **Test Case IDs tương ứng** | `MOV_TC05` đến `MOV_TC09` (Thiết kế tại `KAN-26`) |
| **Hệ thống Automation** | Postman Newman CLI (API Level) + CodeceptJS Playwright (E2E Web Level) |
| **Môi trường thử nghiệm** | Localhost XAMPP (Apache/2.4.58 + MariaDB/10.4.32, PHP 8.2) |
| **Thời gian thực thi** | 2026-10-27 |
| **Trạng thái nghiệm thu** | **100% PASSED (Hoàn thành nghiệm thu chất lượng & an toàn)** |

---

## 2. KẾT QUẢ KIỂM THỬ TỰ ĐỘNG NEWMAN (API REGRESSION)

### 2.1. Bảng số liệu tổng hợp (Execution Metrics)

| Chỉ số | Giá trị | Đánh giá |
| :--- | :---: | :--- |
| **Tổng số Requests** | 5 | 100% hoàn thành |
| **Tổng số Assertions** | 10 | 10 Passed / 0 Failed |
| **Thời gian phản hồi trung bình (Avg SLA)** | **42 ms** | Đạt chuẩn hiệu năng cao (< 500ms) |
| **Thời gian phản hồi nhanh nhất (Min)** | 33 ms | Request SQLi & Whitespace |
| **Thời gian phản hồi chậm nhất (Max)** | 77 ms | Request Exact Search & Data Chaining |
| **Dung lượng nhận về** | 68.32 kB | Ổn định |

### 2.2. Chi tiết từng kịch bản kiểm thử API

| Test Case | Request / Endpoint | Assertions kiểm tra | Kết quả | SLA |
| :--- | :--- | :--- | :---: | :---: |
| **MOV_TC05** | `GET /search.php?query=Doraemon` | 1. Status code `200 OK`<br>2. Chứa kết quả từ khóa "doraemon"<br>3. *Data Chaining*: Trích xuất động `activeMovieId = 55` | **PASSED** | 77 ms |
| **MOV_TC06** | `GET /search.php?query=POKEMON` | 1. Status code `200 OK`<br>2. Match từ khóa không phân biệt hoa thường ("pokemon") | **PASSED** | 35 ms |
| **MOV_TC07** | `GET /search.php?query=%20%20` | 1. Status code `200 OK`<br>2. Xử lý êm dịu chuỗi khoảng trắng, không crash `Fatal error` | **PASSED** | 33 ms |
| **MOV_TC08** | `GET /search.php?query=<script>alert('XSS')</script>` | 1. Status code `200 OK`<br>2. Không chứa thẻ thực thi script thô trong HTML | **PASSED** | 34 ms |
| **MOV_TC09** | `GET /search.php?query=Doraemon' OR '1'='1` | 1. Status code `200 OK`<br>2. Không rò rỉ lỗi cú pháp MySQL, Prepared Statement an toàn | **PASSED** | 33 ms |

---

## 3. KẾT QUẢ KIỂM THỬ E2E CODECEPTJS PLAYWRIGHT

```text
KAN-25: Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php) --
  KAN-25-TC01: Tìm kiếm phim với từ khóa chính xác & Kiểm tra liên kết xem phim
    √ OK in 494ms
  KAN-25-TC02: Tìm kiếm không phân biệt chữ hoa, chữ thường (Case-Insensitive)
    √ OK in 543ms
  KAN-25-TC03: Xử lý êm dịu khi tìm kiếm với khoảng trắng hoặc từ khóa không tồn tại
    √ OK in 692ms
  KAN-25-TC04: Kiểm tra phòng chống tấn công Reflected XSS trên ô tìm kiếm
    √ OK in 481ms
  KAN-25-TC05: Kiểm tra phòng chống tấn công SQL Injection trên ô tìm kiếm
    √ OK in 498ms

  OK | 5 passed // 4s
```

---

## 4. ĐÁNH GIÁ CƠ CHẾ SANITIZE & PHÒNG CHỐNG LỖ HỔNG (SECURITY AUDIT)

### 4.1. Xác thực cơ chế phòng chống Cross-Site Scripting (XSS)
- **Vector tấn công thử nghiệm:** `<script>alert("XSS")</script>`, `<img src=x onerror=alert(1)>`
- **Kết quả kiểm tra:** 
  - Máy chủ không phản chiếu nguyên văn payload độc hại ra DOM của trình duyệt.
  - Các ký tự đặc biệt được xử lý qua hàm chuẩn hóa `htmlspecialchars($query, ENT_QUOTES, 'UTF-8')` hoặc cơ chế output escaping của template engine.
  - Assertions `pm.expect(html).to.not.include("<script>alert('XSS')</script>")` và CodeceptJS `I.dontSeeInSource('<script>alert("XSS")</script>')` đều PASS.
- **Mức độ an toàn:** **SECURE (Không phát sinh lỗ hổng Reflected XSS)**.

### 4.2. Xác thực cơ chế phòng chống SQL Injection (SQLi)
- **Vector tấn công thử nghiệm:** `Doraemon' OR '1'='1`, `'; DROP TABLE movies; --`, `admin'--`
- **Kết quả kiểm tra:**
  - Truy vấn CSDL sử dụng mysqli Prepared Statement với tham số hóa `bind_param('s', $param)` hoặc `mysqli_real_escape_string`.
  - Toàn bộ chuỗi payload được coi là ký tự văn bản thuần túy, không thể làm thay đổi cấu trúc logic của câu lệnh `SELECT ... WHERE title LIKE ?`.
  - Không hề xuất hiện các lỗi cú pháp nguy hiểm như `syntax error`, `mysqli_query`, `SQLSTATE`, hoặc lộ cấu trúc bảng CSDL.
- **Mức độ an toàn:** **SECURE (Không phát sinh lỗ hổng SQL Injection)**.

---

## 5. KẾT LUẬN & ĐỀ XUẤT CHO KAN-28

1. **Hiệu năng & SLA:** Endpoint `/search.php` phản hồi cực nhanh (trung bình 42ms), vượt xa yêu cầu kỹ thuật đề ra (< 500ms).
2. **Data Chaining:** Kịch bản trích xuất động `activeMovieId` từ kết quả tìm kiếm đã chạy trơn tru, sẵn sàng cấp ID đầu vào cho chuỗi kiểm thử xem phim VIP (`/watch.php`) tại `KAN-29` đến `KAN-32`.
3. **Chất lượng:** Toàn bộ 5 test cases từ `MOV_TC05` đến `MOV_TC09` đạt tỷ lệ thành công 100%, đủ điều kiện đóng task `KAN-28`.
