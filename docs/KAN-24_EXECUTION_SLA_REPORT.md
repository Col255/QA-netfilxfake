# 📊 BÁO CÁO THỰC THI KIỂM THỬ, ĐO LƯỜNG SLA & LOG BUG
## TASK JIRA: [KAN-24] — PHÂN HỆ DISCOVERY & CATALOG FILTER

> **Tài liệu nghiệm thu kỹ thuật (Technical Test Execution & SLA Report)**  
> **Jira Task Key:** `KAN-24`  
> **Parent Story:** `KAN-21` (`[KAN-21] Kiểm thử API Khám phá Trang chủ & Lọc Danh mục Phim`)  
> **Người thực hiện:** QA Engineer (`syduong009`)  
> **Ngày thực hiện:** 25/10/2026  
> **Môi trường:** Localhost Apache 2.4.65, PHP 8.5.12, MySQL 8.0 (XAMPP)  
> **Công cụ đo lường:** Newman CLI Runner v6.x & CodeceptJS Playwright  

---

## 1. TỔNG QUAN KẾT QUẢ THỰC THI (Execution Summary)

| Chỉ số (Metric) | Giá trị ghi nhận | Đánh giá chuẩn QA |
| :--- | :--- | :--- |
| **Tổng số Requests thực thi** | 12 requests (3 Iterations benchmark) | Hoàn thành 100% |
| **Tổng số Assertions** | 33 assertions | Đạt 33 / 33 (100% Passed) |
| **Tỷ lệ Pass (Pass Rate)** | **100%** | ✅ Đạt ngưỡng chất lượng |
| **Tổng số Defect phát hiện** | 0 Critical / 0 High / 0 Medium | ✅ Đạt điều kiện nghiệm thu |
| **Thời gian phản hồi trung bình (Avg SLA)** | **17 ms** | 🚀 Vượt xa cam kết SLA (< 1500ms) |
| **Thời gian phản hồi nhanh nhất (Min)** | **9 ms** | Cực kỳ mượt mà |
| **Thời gian phản hồi chậm nhất (Max)** | **49 ms** | Ổn định dưới tải lặp |
| **Tổng thời lượng test suite** | **1.19 giây** | Tối ưu cho CI/CD Regression |

---

## 2. BẢNG CHI TIẾT ĐO LƯỜNG SLA PHẢN HỒI (SLA Benchmark Details)

| Test Case ID | Endpoint & Query | Kích thước Payload | Thời gian phản hồi (Min / Avg / Max) | SLA Cam kết | Trạng thái SLA |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **KAN-23-TC01** | `GET /index.php` | 54.0 KB | 16ms / **27ms** / 49ms | < 1500ms | 🟢 Đạt chuẩn (Vượt 98.2%) |
| **KAN-23-TC02** | `GET /movies.php` | 18.0 KB | 10ms / **18ms** / 34ms | < 1500ms | 🟢 Đạt chuẩn (Vượt 98.8%) |
| **KAN-23-TC03** | `GET /movies.php?genre=Cartoon&country=Japan` | 17.8 KB | 10ms / **10ms** / 11ms | < 1500ms | 🟢 Đạt chuẩn (Vượt 99.3%) |
| **KAN-23-TC04** | `GET /movies.php?genre=UnknownGenre123` | 12.9 KB | 9ms / **13ms** / 20ms | < 1500ms | 🟢 Đạt chuẩn (Vượt 99.1%) |

---

## 3. KẾT QUẢ XÁC MINH NỘI DUNG (Content Verification)

* **Khám phá trang chủ (`/index.php`):**
  - Khối Slider phim nổi bật `#top-movies-slide` và các item `.top-movie-item` kết xuất đầy đủ.
  - Các phân loại danh mục hiển thị chính xác theo CSDL: *Phim mới nhất 🆕*, *Phim chiếu rạp mới 🎬*, *Series mới nhất 📺*, *Hoạt hình mới nhất 🎞️*.
  - Không tồn tại cảnh báo rò rỉ mã lỗi: Không có `Notice`, `Warning: mysqli`, hay `Fatal error`.
* **Bộ lọc thể loại & quốc gia (`/movies.php`):**
  - Lọc thể loại `Cartoon` kết hợp quốc gia `Japan` trả về chính xác danh sách phim hoạt hình Nhật Bản (Pokemon, Doraemon).
  - Lọc thể loại `Series` trả về phim bộ (Vương Chiêu Quân).
  - Lọc thể loại không tồn tại `UnknownGenre123`: Hiển thị thông báo rỗng thân thiện (`Không có phim trong danh mục này.`), không gây sập trang.

---

## 4. BẢNG THEO DÕI KHUYẾT TẬT & ĐỀ XUẤT CẢI TIẾN (Defect Log & Observations)

| Mã ID | Phân loại | Mô tả | Mức độ nghiêm trọng | Trạng thái | Đề xuất giải pháp |
| :--- | :--- | :--- | :---: | :---: | :--- |
| **OBS-01** | *Observation* | Trang `/index.php` chưa cấu hình header `Cache-Control` cho tài nguyên tĩnh HTML. | Low / Nhẹ | Đang theo dõi | Có thể bổ sung HTTP Caching để giảm tải truy vấn MySQL khi lượng người dùng đồng thời tăng cao. |
| **OBS-02** | *Improvement* | Kích thước view trang chủ ~54KB do nhúng nhiều thumbnail Base64 hoặc ảnh cục bộ. | Low / Nhẹ | Đang theo dõi | Đã áp dụng `loading="lazy"` trong mã nguồn, tải mượt mà trên môi trường thử nghiệm. |

*Không phát sinh lỗi P0/P1 gây cản trở nghiệm thu.*

---

## 5. KẾT LUẬN & ĐỀ NGHỊ (Sign-off & Recommendation)

* **Đánh giá chung:** Task **KAN-24** hoàn thành xuất sắc toàn bộ mục tiêu kiểm thử, đo lường SLA và ghi nhận kết quả.
* **Quyết định:** **PASSED / READY FOR SIGN-OFF**  
* **Đề nghị:** Chuyển trạng thái task **KAN-24** sang **Done** trên bảng Jira.
