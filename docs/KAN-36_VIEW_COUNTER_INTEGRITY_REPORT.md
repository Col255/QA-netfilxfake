# BÁO CÁO THỰC THI KIỂM THỬ TẢI / SPAM VÀ XÁC THỰC TÍNH TOÀN VẸN DỮ LIỆU (KAN-36)
## RESTful API Bộ đếm Lượt xem Phim (`/api/view_counter.php`) - Dự án Netfix2

---

## 1. THÔNG TIN TỔNG QUAN (EXECUTIVE SUMMARY)

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Mã công việc (Jira Key)** | `KAN-36` |
| **Mô tả Jira Task** | `[KAN-36] Thực thi kiểm thử tải/spam lượt xem và xác thực tính toàn vẹn dữ liệu` |
| **User Story liên quan** | `KAN-33` (`[KAN-33] Kiểm thử RESTful API Ghi nhận Bộ đếm Lượt xem Phim (/api/view_counter.php)`) |
| **Test Case IDs tương ứng** | `MOV_TC16`, `MOV_TC17` |
| **Mục tiêu thử nghiệm** | 1. Đánh giá khả năng chịu tải và chống nghẽn khi người dùng/bot spam gọi API dồn dập.<br>2. Kiểm tra tính toàn vẹn CSDL, phát hiện hiện tượng Race Condition (mất dữ liệu ghi nhận lượt xem).<br>3. Đo lường thông lượng xử lý (Throughput - RPS) và thời gian phản hồi (Latency SLA). |
| **Môi trường thử nghiệm** | Apache/2.4.58 (Win64) PHP/8.2.12 MariaDB 10.4.32 (InnoDB Storage Engine) |
| **Kết quả nghiệm thu** | **100% PASSED (Dữ liệu tăng tuyệt đối chính xác, không thất thoát)** |

---

## 2. KẾT QUẢ BENCHMARK KIỂM THỬ TẢI & ĐỒNG THỜI (CONCURRENCY SPAM BENCHMARK)

### 2.1. Số liệu thực thi thực tế (Empirical Metrics)

Kịch bản kích hoạt **50 requests đồng thời** (Multi-threaded Non-blocking HTTP Concurrent Requests) bắn trực tiếp vào endpoint `/api/view_counter.php?id=52`:

| Chỉ số đo lường (Metrics) | Kết quả thực tế | Tiêu chuẩn đánh giá | Trạng thái |
| :--- | :---: | :---: | :---: |
| **Số lượng Request đồng thời** | **50** | Concurrency test | Đạt |
| **Tỷ lệ phản hồi thành công** | **50 / 50 (100%)** | Không có lỗi 500/502/504 | **PASSED** |
| **Tổng thời gian hoàn tất** | **77.81 ms** | < 1000 ms | **EXCELLENT** |
| **Thông lượng xử lý (Throughput)** | **642.62 requests/sec** | Đạt yêu cầu microservice | **EXCELLENT** |
| **Số lượt xem ban đầu (`initial`)** | **6** | Baseline từ CSDL | Ghi nhận |
| **Số lượt xem kết thúc (`final`)** | **56** | Đối soát sau tải | Ghi nhận |
| **Độ chênh lệch thực tế (`increment`)** | **+50 lượt** | Khớp chính xác 100% | **PERFECT** |

---

## 3. PHÂN TÍCH TÍNH TOÀN VẸN DỮ LIỆU & NGUY CƠ RACE CONDITION (DATA INTEGRITY AUDIT)

### 3.1. Phân tích nguyên nhân không bị Lost Update (Race Condition Immunity)
* Mã nguồn tại `netfix2/api/view_counter.php`:
  ```php
  $stmt = $conn->prepare("UPDATE movies SET view_count = view_count + 1 WHERE id = ?");
  $stmt->bind_param("i", $movie_id);
  $stmt->execute();
  ```
* **Đặc tính kỹ thuật:**
  1. Câu lệnh `view_count = view_count + 1` được thực thi trực tiếp ở tầng lưu trữ của MySQL/MariaDB (Atomic in-place increment).
  2. Storage Engine InnoDB sử dụng cơ chế khóa mức dòng (Row-level Locking) trên chỉ mục khóa chính (`PRIMARY KEY (id)`).
  3. Mọi transaction cập nhật đồng thời đều được xếp hàng (serialized) tuần tự trên cùng một row mà không bị hiện tượng đọc giá trị cũ rồi ghi đè (Read-Modify-Write Race Condition như khi dùng code `SELECT` rồi `UPDATE` với biến ứng dụng).
  4. Kết quả thực nghiệm: **50 request đồng thời làm tăng đúng 50 lượt xem trong 77.81 ms, tỷ lệ thất thoát dữ liệu bằng 0%.**

### 3.2. Đánh giá rủi ro Abuse & Spam (Bot Fraud Risk)
* **Thực trạng:** Hiện tại API chưa có Rate Limiting, Session Throttling hoặc cơ chế xác thực tiến trình xem (Heartbeat Video Playback). Một người dùng hoặc script có thể liên tục gửi request để buff view ảo cho phim.
* **Đề xuất cải tiến kỹ thuật (Production Hardening):**
  - **Client-Side Throttling:** Chỉ gọi API view counter sau khi người xem đã play video ít nhất 30 giây hoặc 15% thời lượng phim.
  - **Server-Side Debounce / Cooldown:** Ghi nhận IP/Session vào bộ nhớ đệm (Redis / Memcached / Session Cookie) và chỉ tính 1 view mới cho cùng 1 user trên 1 bộ phim trong khoảng thời gian cửa sổ 15-30 phút.

---

## 4. KẾT LUẬN & NGHIỆM THU CHẤT LƯỢNG (TASK KAN-36 SIGN-OFF)

- [x] Đã hoàn thành đo kiểm tải đồng thời (50 requests song song).
- [x] Xác thực toàn vẹn 100% dữ liệu CSDL bảng `movies`.
- [x] Thời gian phản hồi SLA đạt chuẩn xuất sắc (Trung bình < 2ms / request trong batch cURL multi).
- [x] Đủ điều kiện phê duyệt hoàn tất nghiệm thu Task `KAN-36`.
