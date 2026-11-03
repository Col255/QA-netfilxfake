# ==============================================================================
# TÀI LIỆU KIỂM THỬ 5 TASK JIRA - MODULE ADMIN MANAGEMENT CMS (NETFIX2)
# Sinh viên thực hiện: Trung
# ==============================================================================

## 1. [KAN-47] Kiểm thử API Thêm Phim mới, Validation & An toàn Bảo mật (/add_movie.php)
- **Priority**: High | **Labels**: qa, api-testing, ADM, postman, security
- **User Story**: 
  As an Administrator / QA Tester, when validating the add movie function at `/add_movie.php`, I want to verify input field validation, boundary values, session check, and security protection (SQLi, XSS) so that movie records are stored accurately and securely.
- **Acceptance Criteria (AC)**:
  - [AC1]: Chỉ tài khoản phiên Admin mới được phép gửi POST thêm phim (trả về 200 hoặc 302 redirect).
  - [AC2]: Bắt buộc nhập đầy đủ các trường: `title`, `description`, `genre`, `year`, `duration`, `status`.
  - [AC3]: Kiểm tra giá trị biên:
    - Năm sản xuất `year`: Trong khoảng [1900, 2030].
    - Thời lượng `duration`: Trong khoảng [1, 600] phút.
  - [AC4]: Kháng lỗi tấn công SQL Injection và Reflected XSS trên các trường `title`, `description`.
  - [AC5]: Thời gian phản hồi API < 2000ms.

---

## 2. [KAN-48] Kiểm thử API Quản lý Phim, Phân trang & Lọc Danh mục (/manage_movies.php)
- **Priority**: Medium | **Labels**: qa, api-testing, ADM, postman
- **User Story**:
  As an Administrator, I want to retrieve movie lists with search keywords, category filtering, and pagination handling so that the CMS renders quickly and accurately.
- **Acceptance Criteria (AC)**:
  - [AC1]: Kiểm tra phân quyền truy cập trang danh sách phim (Session Auth Check).
  - [AC2]: Tham số phân trang `page`: số nguyên $\ge 1$. Khi `page` âm hoặc vượt tổng số trang, hệ thống xử lý mặc định hoặc trả về rỗng không crash.
  - [AC3]: Tham số `limit` (số lượng phim/trang): miền hợp lệ [5, 100].
  - [AC4]: Lọc theo `genre` (thể loại) trả về đúng tập hợp phim thuộc thể loại đó.
  - [AC5]: Thời gian phản hồi API < 1500ms.

---

## 3. [KAN-49] Kiểm thử API Quản lý Đơn hàng VIP (/orders.php)
- **Priority**: Medium | **Labels**: qa, api-testing, ADM, postman, payment
- **User Story**:
  As an Administrator, I want to review subscription orders, filter orders by payment status (Pending, Completed, Cancelled), and approve VIP upgrades.
- **Acceptance Criteria (AC)**:
  - [AC1]: Truy xuất danh sách đơn hàng đăng ký VIP chính xác theo trạng thái (`status=pending|approved|rejected`).
  - [AC2]: Không cho phép cập nhật đơn hàng với mã trạng thái không hợp lệ.
  - [AC3]: Phản hồi HTTP Code 200 hoặc 302, thời gian tải dữ liệu đơn hàng < 1500ms.

---

## 4. [KAN-50] Kiểm thử API Quản lý Khách hàng, Phân quyền & Khóa Tài khoản (/customers.php)
- **Priority**: High | **Labels**: qa, api-testing, ADM, postman
- **User Story**:
  As an Administrator, I want to manage customer accounts, view VIP status, toggle user activation (lock/unlock), and ensure non-admin users cannot alter role privileges.
- **Acceptance Criteria (AC)**:
  - [AC1]: Hiển thị đầy đủ thông tin định danh: `user_id`, `email`, `role`, `status` (active/locked).
  - [AC2]: Chỉ tài khoản Root Admin mới có quyền đổi `role` hoặc ban tài khoản.
  - [AC3]: Thời gian phản hồi danh sách người dùng < 1500ms.

---

## 5. [KAN-51] Kiểm thử API Báo cáo Thống kê Doanh thu & Người dùng (/statistics.php)
- **Priority**: Medium | **Labels**: qa, api-testing, ADM, postman, analytics
- **User Story**:
  As an Administrator, I want to view analytical figures on revenue, user growth, and view counts filtered by date range so that business performance can be tracked.
- **Acceptance Criteria (AC)**:
  - [AC1]: Các chỉ số doanh thu và số lượng phải là số không âm ($\ge 0$).
  - [AC2]: Bộ lọc khoảng ngày `from_date <= to_date`. Nếu `from_date > to_date`, hệ thống báo lỗi validation.
  - [AC3]: Thời gian xuất báo cáo thống kê tải về < 1500ms.