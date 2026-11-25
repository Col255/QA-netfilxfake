# BÁO CÁO NGHIỆM THU KIỂM THỬ HỘP TRẮNG & KẾT QUẢ FIX BUG
**Phân hệ:** FE2 (Movies & Discovery) & FE3 (Interactions & Engagement) — **Dự án:** Netfix2  
**Nhánh tích hợp:** `week6` | **Pull Request:** [PR #5](https://github.com/Col255/QA-netfilxfake/pull/5)  
**Tác giả thực hiện:** [Duong204-uth](https://github.com/Duong204-uth) | **Ngày báo cáo:** 16/11/2026  
**Trạng thái tổng thể:** **100% HOÀN THÀNH (ĐÃ NGHIỆM THU)**

---

## 1. TÓM TẮT KẾT QUẢ NGHIỆM THU (EXECUTIVE SUMMARY)

| Chỉ số Đo lường | Giá trị Đạt được | Mục tiêu Đề ra | Trạng thái Nghiệm thu |
| :--- | :---: | :---: | :---: |
| **Tổng số ca kiểm thử (Test Cases)** | **123** | 123 |  **100% Hoàn thành** |
| **Số ca kiểm thử ĐẠT (PASS)** | **123** (100.0%) | 100% |  **ĐẠT** |
| **Số ca kiểm thử KHÔNG ĐẠT (FAIL)** | **0** (0.0%) | 0 |  **ĐẠT** |
| **Độ bao phủ câu lệnh (C0 / Statement Coverage)** | **100.0%** (138/138 Khối lệnh) | 100% |  **ĐẠT** |
| **Độ bao phủ nhánh (C1 / Branch Coverage)** | **100.0%** (228/228 Nhánh) | 100% |  **ĐẠT** |
| **Độ phức tạp Cyclomatic $V(G)$** | **121 / 121** | 121 |  **Khớp 100% lý thuyết CFG** |
| **Số Defect đã khắc phục** | **9 / 9** (100.0%) | 9 / 9 |  **100% Khắc phục** |
| **Lỗi hồi quy phát sinh (Regression Defect)** | **0** | 0 |  **ĐẠT** |

> **Kết luận nghiệm thu:** Toàn bộ 9 lỗi phát hiện từ quá trình phân tích đồ thị dòng điều khiển (CFG) và kiểm thử giá trị biên (BVA) đã được sửa chữa độc lập trên từng nhánh tính năng, kiểm thử đơn vị hồi quy đạt 100% PASS và đã được tích hợp thành công vào nhánh `week6` thông qua Pull Request #5.

---

## 2. BẢNG TỔNG HỢP KẾT QUẢ KHẮC PHỤC 9 DEFECT (JIRA TASKS & COMMITS)

| STT | Mã Jira | Mã Defect | Mức độ | Tệp tin & Dòng Bị Lỗi | Nội dung Khắc phục (Bug Fix) | Commit Hash | Kết quả Nghiệm thu |
| :---: | :---: | :---: | :---: | :--- | :--- | :---: | :---: |
| 1 | **KAN-205** | `WB-DEF-01` | **High** | `netfix2/watch.php:75` | Xóa toán tử 3 ngôi gây nhánh chết, gán `$user_is_vip = false;` an toàn trước khi xét Session. | `11f809d` | **PASS** |
| 2 | **KAN-206** | `WB-DEF-02` | **Medium** | `netfix2/api/view_counter.php:11` | Kiểm tra `$stmt->affected_rows > 0`, trả về `Phim không tồn tại` khi ID không có trong CSDL. | `c40e209` | **PASS** |
| 3 | **KAN-207** | `WB-DEF-05` | **Medium** | `netfix2/watch.php:160` | Bổ sung kiểm tra giới hạn độ dài bình luận tối đa 500 ký tự (BVA Max) qua `mb_strlen($comment) > 500`. | `8091775` | **PASS** |
| 4 | **KAN-208** | `WB-DEF-03` | **Low** | `netfix2/api/view_counter.php:2` | Khai báo header chuẩn `Content-Type: application/json; charset=utf-8` cho REST API. | `1384841` | **PASS** |
| 5 | **KAN-209** | `WB-DEF-04` | **Low** | `netfix2/index.php:31` | Chuẩn hóa thứ tự tham số `getMovies($conn, $limit, $genre = null)` triệt tiêu lỗi PHP 8 Deprecated. | `229de04` | **PASS** |
| 6 | **KAN-210** | `WB-DEF-06` | **Low** | `netfix2/watch.php:158` | Gán thông báo `$comment_error` khi gửi bình luận rỗng hoặc chỉ chứa khoảng trắng. | `6d04c41` | **PASS** |
| 7 | **KAN-211** | `WB-DEF-07` | **Low** | `netfix2/watch_history.php:13` | Bổ sung ràng buộc số nguyên dương `(int)$_GET['delete'] > 0` khi xóa lịch sử xem (BVA Min-1). | `38a19af` | **PASS** |
| 8 | **KAN-212** | `WB-DEF-08` | **Medium** | `netfix2/search.php:75,86` | Thay thế lệnh `die()` bằng `error_log()` và `$search_error` thân thiện, không làm lộ schema SQL. | `cfdc05f` | **PASS** |
| 9 | **KAN-213** | `WB-DEF-09` | **Low** | `netfix2/search.php:60,66` | Tách độc lập `if (!empty($query) || !empty($genre))`, cho phép lọc theo thể loại không cần từ khóa. | `9134067` | **PASS** |

---

## 3. BẢNG ĐO LƯỜNG ĐỘ BAO PHỦ THEO TỪNG TỆP TIN MÃ NGUỒN

| Phân hệ | Tệp tin Mã Nguồn | Khối lệnh (C0) | Nhánh rẽ (C1) | V(G) Lý thuyết | V(G) Thực tế | Số Test Cases | Kết quả Nghiệm thu |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **FE3** | `netfix2/api/view_counter.php` | 4 / 4 (100%) | 2 / 2 (100%) | 2 | 2 | 8 TCs | **PASS** |
| **FE3** | `netfix2/comments.php` | 12 / 12 (100%) | 16 / 16 (100%) | 9 | 9 | 5 TCs | **PASS** |
| **FE3** | `netfix2/watch_history.php` | 6 / 6 (100%) | 8 / 8 (100%) | 5 | 5 | 5 TCs | **PASS** |
| **FE2** | `netfix2/index.php` | 38 / 38 (100%) | 70 / 70 (100%) | 36 | 36 | 36 TCs | **PASS** |
| **FE2** | `netfix2/movies.php` | 20 / 20 (100%) | 36 / 36 (100%) | 19 | 19 | 19 TCs | **PASS** |
| **FE2** | `netfix2/search.php` | 16 / 16 (100%) | 28 / 28 (100%) | 15 | 15 | 15 TCs | **PASS** |
| **FE2** | `netfix2/watch.php` | 42 / 42 (100%) | 68 / 68 (100%) | 35 | 35 | 35 TCs | **PASS** |
| **TỔNG** | **Toàn bộ 7 tệp mục tiêu (FE2 + FE3)** | **138 / 138 (100%)** | **228 / 228 (100%)** | **121** | **121** | **123 TCs** | **100% PASS** |

---

## 4. DANH MỤC TÀI LIỆU BÀN GIAO ĐÍNH KÈM

1. **Sơ đồ CFG & Phân tích FE2 (MOV):** `docs/testrun/WHITEBOX_CFG_FE2_MOV.md`
2. **Sơ đồ CFG & Phân tích FE3 (INT):** `docs/testrun/WHITEBOX_CFG_FE3_INT.md`
3. **Ma trận Kiểm thử (Test Matrix):** `docs/testrun/TEST_MATRIX.csv`
4. **Ma trận Truy vết (Traceability Matrix):** `docs/testrun/TRACEABILITY_MATRIX.csv`
5. **Báo cáo Độ bao phủ chuẩn Clover:** `docs/testrun/coverage.xml`
6. **Số liệu tổng hợp độ bao phủ:** `docs/testrun/coverage-summary.json`
7. **Báo cáo Defect chi tiết:** `docs/testrun/DEFECT_REPORT.md`
8. **Cơ sở dữ liệu Fixture thử nghiệm:** `docs/testrun/db_fixtures/01_seed_whitebox_accounts.sql`
9. **Báo cáo Nghiệm thu HTML (In / Export PDF):** `docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.html`
