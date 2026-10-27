# 📋 BẢNG TỔNG HỢP TEST CASE KIỂM THỬ CHỨC NĂNG & BẢO MẬT
## NHÓM CHỨC NĂNG: AUTHENTICATION & SECURITY (AUTH) — HỆ THỐNG NETFIX2

> **Tài liệu đặc tả kịch bản kiểm thử phần mềm (Software Test Case Specification)**  
> **Dự án:** Netfix2 — Website Xem Phim Trực Tuyến  
> **Module phụ trách:** `1. Authentication & Security (AUTH)`  
> **Người phụ trách (Author):** Huỳnh Nguyễn Tiến Lộc  
> **Môi trường thử nghiệm:** Local XAMPP (`http://localhost/netfix2`), Docker Container (`php:8.2-apache`), MySQL 8.0 (`netfixvn`)  
> **Công cụ kiểm thử:** Manual Testing, CodeceptJS + Playwright, Postman / Newman CLI, SonarQube & Qodana Static Security Scan  

---

## 1. THÔNG TIN QUẢN TRỊ TÀI LIỆU (Document Control)

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Mã tài liệu** | `TC-SPEC-NETFIX2-AUTH-v1.0` |
| **Phiên bản** | `1.0.0` |
| **Ngày tạo** | 25/10/2026 |
| **Trạng thái** | Approved / Ready for Execution |
| **Người thiết kế** | Huỳnh Nguyễn Tiến Lộc (QA Lead / Auth Module Owner) |
| **Hệ thống mục tiêu** | Phân hệ Xác thực, Phân quyền (RBAC), Quản lý Hồ sơ & Bảo mật (`netfix2/`) |

---

## 2. PHẠM VI & KỸ THUẬT ÁP DỤNG (Scope & Test Techniques)

### 2.1. Danh sách Endpoints / Trang PHP trong phạm vi kiểm thử
1. `GET / POST /register.php`: Đăng ký tài khoản người dùng mới (`username`, `email`, `password`, `confirm_password`), kiểm tra trùng lặp và mã hóa mật khẩu bằng `password_hash(..., PASSWORD_DEFAULT)`.
2. `GET / POST /login.php`: Đăng nhập dành cho người dùng thường (`is_admin = 0`) bằng `identifier` (Username hoặc Email) và `password`, xác thực qua `password_verify()` và khởi tạo `$_SESSION['user_id']`.
3. `GET / POST /admin_login.php`: Đăng nhập dành riêng cho Quản trị viên (`is_admin = 1`), kiểm tra phân quyền truy cập `admin_dashboard.php`.
4. `GET /logout.php`: Đăng xuất tài khoản, hủy toàn bộ dữ liệu phiên làm việc (`session_unset()`, `session_destroy()`) và điều hướng về `index.php`.
5. `GET /profile.php`: Xem thông tin hồ sơ cá nhân, kiểm tra hạn gói VIP (`vip_expires_at < time()`) tự động hạ cấp `is_vip = 0`, và lịch sử đơn hàng VIP.
6. `GET / POST /edit_profile.php`: Cập nhật thông tin cá nhân (`username`, `email` qua `update_profile`) và đổi mật khẩu trực tiếp trên trang chỉnh sửa (`change_password`).
7. `POST /change_password.php`: Xử lý đổi mật khẩu từ tab cài đặt của trang Hồ sơ (`current_password`, `new_password`, `confirm_new_password`) và redirect kèm thông báo trạng thái.

### 2.2. Kỹ thuật thiết kế kiểm thử áp dụng
* **Equivalence Partitioning (EP - Phân vùng tương đương):**
  - *Hợp lệ:* Tài khoản user thường (`tuan` / `tuan@gmail.com`), tài khoản Admin (`admin` / `admin@netfixvn.com`), email đúng chuẩn RFC (`user_new@gmail.com`).
  - *Không hợp lệ:* Email sai định dạng (`abc@`, `plainaddress`), tài khoản không tồn tại (`ghost_user`), mật khẩu sai, xác nhận mật khẩu lệch.
* **Boundary Value Analysis (BVA - Phân tích giá trị biên):**
  - Độ dài mật khẩu quy định trong `register.php`, `edit_profile.php`, `change_password.php` là **tối thiểu 6 ký tự** (`strlen($password) < 6`):
    - **Biên ngoài dưới (BVA = 5 ký tự):** `12345` $\rightarrow$ Từ chối, báo lỗi `"Mật khẩu phải có ít nhất 6 ký tự."`
    - **Biên trong tối thiểu (BVA = 6 ký tự):** `123456` $\rightarrow$ Chấp nhận hợp lệ.
    - **Biên trên (BVA = 7 ký tự / 64 ký tự):** `1234567` $\rightarrow$ Chấp nhận hợp lệ.
    - **Biên rỗng (BVA = 0 ký tự):** `""` $\rightarrow$ Từ chối, báo lỗi bỏ trống.
* **Decision Table Testing (Bảng quyết định):**
  - Ma trận phân quyền đăng nhập và điều hướng giữa **Loại cổng đăng nhập** (`login.php` vs `admin_login.php`), **Quyền tài khoản** (`is_admin = 0` vs `is_admin = 1`) và **Trạng thái phiên (`$_SESSION['user_id']`)**.
* **State Transition Testing (Kiểm thử chuyển đổi trạng thái):**
  - Vòng đời phiên làm việc: `Guest (Chưa đăng nhập)` $\rightarrow$ `Authenticated User` $\rightarrow$ `VIP Expired Auto-Downgrade (profile.php)` $\rightarrow$ `Logged Out (Session Destroyed)`.
* **Security Testing (Kiểm thử bảo mật OWASP):**
  - Kiểm thử chống **SQL Injection** (`' OR '1'='1 --`) trên `login.php`, `register.php`, `admin_login.php`.
  - Kiểm thử chống **Cross-Site Scripting (XSS)** (`<script>alert(1)</script>`) khi hiển thị tên người dùng hoặc thông báo lỗi.
  - Kiểm thử **Broken Access Control (Truy cập trái phép):** Khách chưa đăng nhập truy cập thẳng `/profile.php`, `/edit_profile.php`, `/change_password.php`.
  - Kiểm thử **Cryptographic Failures (Phát hiện từ SonarQube/Qodana):** Mật khẩu Admin trong `admin_login.php` đang so sánh chuỗi thuần (`$password === $user['password']`) thay vì `password_verify()`.

---

## 3. MA TRẬN TEST CASE CHI TIẾT (Detailed Test Case Matrix — 24 Test Cases)

| Test Case ID | Module / Endpoint | Tên Test Case (Title) | Điều kiện tiên quyết (Preconditions) | Các bước thực hiện (Steps) | Dữ liệu kiểm thử (Test Data) | Kết quả mong đợi (Expected Result) | Kỹ thuật kiểm thử | Mức ưu tiên (Priority) | Loại test (Type) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **AUTH_TC01** | `/register.php` | Hiển thị giao diện trang Đăng ký thành công cho khách vãng lai | Chưa đăng nhập (`$_SESSION['user_id']` trống). | 1. Gửi request `GET /register.php`<br>2. Kiểm tra mã phản hồi và cấu trúc form HTML. | Method: `GET`<br>Session: Guest | - HTTP Status: `200 OK`<br>- Hiển thị form `.register-form` có đủ 4 trường: `username`, `email`, `password`, `confirm_password` và nút `Đăng ký`. | Functional UI | **P1 (High)** | Positive |
| **AUTH_TC02** | `/register.php` | Đăng ký tài khoản mới thành công với mật khẩu đúng tại biên tối thiểu (6 ký tự) | `username` và `email` chưa tồn tại trong bảng `users`. | 1. Gửi `POST /register.php` với mật khẩu đúng 6 ký tự.<br>2. Kiểm tra thông báo và bản ghi trong bảng `users`. | `username`: `loc_qa01`<br>`email`: `loc_qa01@gmail.com`<br>`password`: `123456` (6 ký tự)<br>`confirm_password`: `123456` | - Hiển thị `.success`: `"Đăng ký thành công! Vui lòng đăng nhập."`<br>- Header `Refresh: 2; url=login.php`<br>- Bảng `users` lưu `is_admin = 0` và mật khẩu được băm Bcrypt (`$2y$10$...`). | **BVA (Min = 6)** | **P0 (Critical)** | Positive |
| **AUTH_TC03** | `/register.php` | Từ chối đăng ký khi mật khẩu có 5 ký tự (Dưới biên tối thiểu) | Chưa đăng nhập. | 1. Gửi `POST /register.php` với mật khẩu 5 ký tự.<br>2. Kiểm tra thông báo lỗi. | `username`: `loc_qa02`<br>`email`: `loc_qa02@gmail.com`<br>`password`: `12345` (5 ký tự)<br>`confirm_password`: `12345` | - Không tạo bản ghi mới trong CSDL<br>- Hiển thị thông báo `.error`: `"Mật khẩu phải có ít nhất 6 ký tự."` | **BVA (Min - 1 = 5)** | **P1 (High)** | Negative |
| **AUTH_TC04** | `/register.php` | Từ chối đăng ký khi bỏ trống một hoặc tất cả các trường bắt buộc | Chưa đăng nhập. | 1. Gửi `POST /register.php` với các trường rỗng.<br>2. Kiểm tra phản hồi. | `username`: `""`<br>`email`: `""`<br>`password`: `""`<br>`confirm_password`: `""` | - Hiển thị thông báo `.error`: `"Vui lòng nhập đầy đủ thông tin."`<br>- Không thực thi câu lệnh `INSERT`. | **BVA (Empty = 0)** | **P1 (High)** | Negative |
| **AUTH_TC05** | `/register.php` | Từ chối đăng ký khi mật khẩu xác nhận không khớp với mật khẩu | Chưa đăng nhập. | 1. Gửi `POST /register.php` với `password` khác `confirm_password`.<br>2. Kiểm tra thông báo lỗi. | `username`: `loc_qa03`<br>`email`: `loc_qa03@gmail.com`<br>`password`: `123456`<br>`confirm_password`: `654321` | - Hiển thị thông báo `.error`: `"Mật khẩu xác nhận không khớp."`<br>- Không lưu vào CSDL. | Equivalence (Negative EP) | **P1 (High)** | Negative |
| **AUTH_TC06** | `/register.php` | Từ chối đăng ký khi trùng Tên đăng nhập (`username`) đã tồn tại | Tài khoản `tuan` đã có sẵn trong bảng `users` (ID=20). | 1. Gửi `POST /register.php` với `username = tuan` và email mới.<br>2. Kiểm tra thông báo lỗi. | `username`: `tuan`<br>`email`: `new_tuan@gmail.com`<br>`password`: `123456`<br>`confirm_password`: `123456` | - Hiển thị thông báo `.error`: `"Tên đăng nhập hoặc email đã được sử dụng."`<br>- Đảm bảo tính duy nhất của `username`. | DB Constraint / Negative EP | **P0 (Critical)** | Negative |
| **AUTH_TC07** | `/register.php` | Từ chối đăng ký khi trùng Địa chỉ Email (`email`) đã tồn tại | Email `tuan@gmail.com` đã tồn tại trong bảng `users`. | 1. Gửi `POST /register.php` với `username` mới nhưng `email = tuan@gmail.com`.<br>2. Kiểm tra thông báo lỗi. | `username`: `tuan_moi`<br>`email`: `tuan@gmail.com`<br>`password`: `123456`<br>`confirm_password`: `123456` | - Hiển thị thông báo `.error`: `"Tên đăng nhập hoặc email đã được sử dụng."` | DB Constraint / Negative EP | **P0 (Critical)** | Negative |
| **AUTH_TC08** | `/register.php` | Kiểm tra lỗ hổng thiếu kiểm tra định dạng Email ở tầng Backend khi gửi POST trực tiếp | Gửi request qua Postman/cURL bỏ qua HTML5 `type="email"`. | 1. Gửi `POST /register.php` với `email` sai định dạng (`invalid_email_format`).<br>2. Quan sát hành vi xử lý của `register.php`. | `username`: `test_bad_mail`<br>`email`: `not_an_email`<br>`password`: `123456`<br>`confirm_password`: `123456` | - **Kỳ vọng chuẩn:** Từ chối và báo lỗi `"Địa chỉ email không hợp lệ."` (như `edit_profile.php` dòng 41 dùng `filter_var`)<br>- **Phát hiện Bug tiềm ẩn:** `register.php` hiện chưa gọi `filter_var($email, FILTER_VALIDATE_EMAIL)` ở server-side! | Error Guessing / API Validation | **P1 (High)** | Defect / Negative |
| **AUTH_TC09** | `/login.php` | Đăng nhập thành công bằng Email hợp lệ của người dùng thường | Tài khoản `tuan@gmail.com` (`is_admin = 0`, pass `123456`) tồn tại. | 1. Mở `/login.php`.<br>2. Nhập `identifier` và `password` hợp lệ.<br>3. Bấm nút `Đăng nhập`. | `identifier`: `tuan@gmail.com`<br>`password`: `123456` | - Khởi tạo `$_SESSION['user_id'] = 20`, `$_SESSION['is_admin'] = 0`<br>- Redirect `302` về `index.php`<br>- Thanh điều hướng hiển thị nút `Hồ sơ` và `Đăng xuất`. | Equivalence (Positive EP) | **P0 (Critical)** | Positive |
| **AUTH_TC10** | `/login.php` | Đăng nhập thành công bằng Username hợp lệ của người dùng thường | Tài khoản `username = tuan` tồn tại trong CSDL. | 1. Gửi `POST /login.php` với `identifier = tuan`.<br>2. Kiểm tra điều hướng và Session. | `identifier`: `tuan`<br>`password`: `123456` | - Xác thực `password_verify()` thành công<br>- Redirect về `index.php` và hiển thị menu người dùng đã đăng nhập. | Equivalence (Positive EP) | **P0 (Critical)** | Positive |
| **AUTH_TC11** | `/login.php` | Từ chối đăng nhập khi nhập sai mật khẩu | Tài khoản `tuan@gmail.com` tồn tại. | 1. Gửi `POST /login.php` với mật khẩu sai.<br>2. Kiểm tra thông báo lỗi và URL. | `identifier`: `tuan@gmail.com`<br>`password`: `sai_mat_khau_123` | - Không tạo `$_SESSION['user_id']`<br>- Giữ nguyên tại `/login.php`<br>- Hiển thị khối `.error`: `"Mật khẩu không đúng."` | Equivalence (Negative EP) | **P0 (Critical)** | Negative |
| **AUTH_TC12** | `/login.php` | Từ chối đăng nhập khi Username hoặc Email không tồn tại | Tài khoản `khong_ton_tai@gmail.com` không có trong DB. | 1. Gửi `POST /login.php` với tài khoản không tồn tại.<br>2. Kiểm tra thông báo lỗi. | `identifier`: `khong_ton_tai@gmail.com`<br>`password`: `123456` | - Không tạo Session<br>- Hiển thị khối `.error`: `"Tên đăng nhập hoặc email không tồn tại."` | Equivalence (Negative EP) | **P1 (High)** | Negative |
| **AUTH_TC13** | `/login.php` | Từ chối đăng nhập khi bỏ trống trường Tên đăng nhập hoặc Mật khẩu | Chưa đăng nhập. | 1. Gửi `POST /login.php` với `identifier` hoặc `password` rỗng.<br>2. Kiểm tra thông báo lỗi. | `identifier`: `""`<br>`password`: `""` | - Hiển thị thông báo `.error`: `"Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu."` | **BVA (Empty = 0)** | **P1 (High)** | Negative |
| **AUTH_TC14** | `/login.php` | Chặn tài khoản Admin đăng nhập qua cổng người dùng thường (`/login.php`) | Tài khoản `admin` có `is_admin = 1` trong bảng `users`. | 1. Gửi `POST /login.php` với tài khoản `admin` / `admin123`.<br>2. Kiểm tra điều kiện lọc `AND is_admin = 0`. | `identifier`: `admin`<br>`password`: `admin123` | - Câu truy vấn `WHERE ... AND is_admin = 0` trả về 0 dòng<br>- Từ chối đăng nhập và báo `"Tên đăng nhập hoặc email không tồn tại."` | **Decision Table / RBAC** | **P1 (High)** | Security / RBAC |
| **AUTH_TC15** | `/admin_login.php` | Đăng nhập Quản trị viên thành công tại cổng Admin (`/admin_login.php`) | Tài khoản `admin` (`admin@netfixvn.com`, `is_admin = 1`) tồn tại. | 1. Gửi `POST /admin_login.php` với thông tin Admin.<br>2. Kiểm tra Session và điều hướng. | `identifier`: `admin`<br>`password`: `admin123` | - Thiết lập `$_SESSION['user_id'] = 22`, `$_SESSION['is_admin'] = 1`<br>- Redirect `302` sang `admin_dashboard.php`<br>- *(Ghi chú Security Bug: Dòng 29 đang dùng `$password === $user['password']` dạng plain-text thay vì Bcrypt)*. | **Decision Table / RBAC** | **P0 (Critical)** | Positive / Security |
| **AUTH_TC16** | `/admin_login.php` | Chặn tài khoản người dùng thường (`is_admin = 0`) đăng nhập vào cổng Admin | Tài khoản `tuan@gmail.com` có `is_admin = 0`. | 1. Gửi `POST /admin_login.php` bằng tài khoản `tuan@gmail.com`.<br>2. Kiểm tra thông báo lỗi. | `identifier`: `tuan@gmail.com`<br>`password`: `123456` | - Không cấp quyền Admin<br>- Hiển thị `.error`: `"Tên đăng nhập hoặc email không tồn tại hoặc tài khoản này không phải admin."` | **Decision Table / RBAC** | **P0 (Critical)** | Security / RBAC |
| **AUTH_TC17** | `/profile.php`<br>`/edit_profile.php`<br>`/change_password.php` | Chặn khách vãng lai (chưa đăng nhập) truy cập trực tiếp các trang bảo vệ | Không có Cookie `PHPSESSID` hoặc chưa đăng nhập. | 1. Gửi `GET /profile.php` và `GET /edit_profile.php` khi chưa đăng nhập.<br>2. Kiểm tra điều hướng bảo vệ. | Session: Guest (Unauthenticated) | - Hệ thống lập tức chặn truy cập và trả về Header `Location: login.php` (`302 Found`)<br>- Không lộ bất kỳ dữ liệu cá nhân nào. | **State Transition / Access Guard** | **P0 (Critical)** | Security |
| **AUTH_TC18** | `/profile.php` | Tự động hạ cấp trạng thái VIP (`is_vip = 0`) khi thời hạn `vip_expires_at` đã quá hạn | User có `is_vip = 1` nhưng `vip_expires_at < time()` trong DB. | 1. Đăng nhập tài khoản có gói VIP vừa hết hạn.<br>2. Truy cập `GET /profile.php`.<br>3. Kiểm tra trạng thái VIP trên giao diện và trong DB. | User ID: `20` với `is_vip = 1`, `vip_expires_at = '2025-01-01 00:00:00'` | - Dòng 33–41 của `profile.php` tự động chạy `UPDATE users SET is_vip = 0`<br>- Hiển thị trạng thái tài khoản là `Tài khoản Thường (Miễn phí)`. | **State Transition / BVA Time** | **P1 (High)** | Business Logic |
| **AUTH_TC19** | `/edit_profile.php` | Cập nhật thông tin hồ sơ (`username`, `email`) thành công | User đã đăng nhập hợp lệ (`$_SESSION['user_id'] = 20`). | 1. Gửi `POST /edit_profile.php` kèm nút `update_profile=1`.<br>2. Kiểm tra thông báo và dữ liệu mới. | `update_profile`: `1`<br>`username`: `tuan_updated`<br>`email`: `tuan_updated@gmail.com` | - Cập nhật CSDL và biến `$_SESSION['username']`, `$_SESSION['email']` thành công<br>- Hiển thị thông báo: `"Thông tin cá nhân đã được cập nhật thành công!"` | Equivalence (Positive EP) | **P1 (High)** | Positive |
| **AUTH_TC20** | `/edit_profile.php` | Từ chối cập nhật hồ sơ khi nhập Email sai định dạng hoặc trùng với người dùng khác | User đã đăng nhập; email `admin@netfixvn.com` thuộc về user khác (`id != 20`). | 1. Gửi `POST /edit_profile.php` với `email = admin@netfixvn.com` hoặc `email = sai_dinh_dang`.<br>2. Kiểm tra thông báo lỗi. | Trường hợp 1: `email = sai_dinh_dang`<br>Trường hợp 2: `email = admin@netfixvn.com` | - TH1: Báo lỗi `"Địa chỉ email không hợp lệ."`<br>- TH2: Báo lỗi `"Tên người dùng hoặc email này đã được sử dụng bởi người khác."` | Equivalence (Negative EP) | **P1 (High)** | Negative |
| **AUTH_TC21** | `/change_password.php` | Đổi mật khẩu thành công khi nhập đúng mật khẩu hiện tại và mật khẩu mới $\ge$ 6 ký tự | User đã đăng nhập hợp lệ. | 1. Gửi `POST /change_password.php` với mật khẩu hiện tại đúng và mật khẩu mới 6 ký tự.<br>2. Kiểm tra URL redirect. | `current_password`: `123456`<br>`new_password`: `654321`<br>`confirm_new_password`: `654321` | - Mật khẩu mới được hash Bcrypt và cập nhật vào bảng `users`<br>- Redirect về `profile.php?tab=settings&status=success&message=Đổi+mật+khẩu+thành+công!` | **BVA (Min = 6)** / Positive | **P0 (Critical)** | Positive |
| **AUTH_TC22** | `/change_password.php` | Từ chối đổi mật khẩu khi nhập sai mật khẩu hiện tại hoặc mật khẩu mới < 6 ký tự | User đã đăng nhập hợp lệ. | 1. Gửi `POST /change_password.php` với `current_password` sai (hoặc `new_password` 5 ký tự).<br>2. Kiểm tra thông báo lỗi trên URL redirect. | TH1: `current_password = wrong_pass`<br>TH2: `new_password = 12345` (5 ký tự) | - TH1: Redirect kèm `status=error&message=Mật+khẩu+hiện+tại+không+đúng.`<br>- TH2: Redirect kèm `status=error&message=Mật+khẩu+mới+phải+có+ít+nhất+6+ký+tự.` | **BVA (Min - 1 = 5)** / Negative | **P1 (High)** | Negative |
| **AUTH_TC23** | `/logout.php` | Đăng xuất tài khoản thành công và hủy hoàn toàn phiên làm việc (Session Destroy) | User đang trong phiên đăng nhập. | 1. Gửi request `GET /logout.php`.<br>2. Thử truy cập lại `/profile.php` sau khi đăng xuất. | Method: `GET /logout.php` | - `session_unset()` và `session_destroy()` xóa sạch biến phiên<br>- Redirect về `index.php` (hiển thị lại nút `Đăng nhập` / `Đăng ký`)<br>- Truy cập lại `/profile.php` bị đẩy về `login.php`. | **State Transition** | **P0 (Critical)** | Positive |
| **AUTH_TC24** | `/login.php`<br>`/register.php` | Phòng chống tấn công SQL Injection & XSS trên biểu mẫu Xác thực | Chưa đăng nhập. | 1. Nhập payload SQLi `' OR '1'='1 --` vào `identifier`.<br>2. Nhập payload XSS `<script>alert(1)</script>` vào `username`.<br>3. Kiểm tra phản hồi. | `identifier`: `' OR '1'='1 --`<br>`password`: `' OR '1'='1 --` | - Câu lệnh `Prepared Statement` (`$conn->prepare` + `bind_param`) vô hiệu hóa hoàn toàn SQL Injection<br>- Không thể đăng nhập lậu; dữ liệu xuất ra được bọc `htmlspecialchars()` an toàn. | **Security Testing (OWASP)** | **P0 (Critical)** | Security |

---

## 4. MA TRẬN PHÂN QUYỀN ĐĂNG NHẬP & TRUY CẬP (Decision Table — RBAC)

| Điều kiện / Quy tắc (Rules) | Rule 1 (Khách - Guest) | Rule 2 (User Thường vào `/login.php`) | Rule 3 (User Thường vào `/admin_login.php`) | Rule 4 (Admin vào `/login.php`) | Rule 5 (Admin vào `/admin_login.php`) |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Trạng thái đăng nhập** | Chưa đăng nhập | Nhập đúng User/Pass | Nhập đúng User/Pass | Nhập đúng Admin/Pass | Nhập đúng Admin/Pass |
| **Cờ phân quyền (`users.is_admin`)** | N/A | `0` (User thường) | `0` (User thường) | `1` (Admin) | `1` (Admin) |
| **Cổng đăng nhập sử dụng** | Truy cập `/profile.php` | `/login.php` | `/admin_login.php` | `/login.php` | `/admin_login.php` |
| **KẾT QUẢ MONG ĐỢI** | **Chặn $\rightarrow$ Redirect về `/login.php`** | **Cho phép $\rightarrow$ Redirect `/index.php`** | **Chặn $\rightarrow$ Báo lỗi không phải Admin** | **Chặn $\rightarrow$ Không khớp `is_admin = 0`** | **Cho phép $\rightarrow$ Redirect `/admin_dashboard.php`** |

---

## 5. DANH SÁCH LỖI TIỀM ẨN PHÁT HIỆN QUA PHÂN TÍCH TĨNH & THIẾT KẾ TEST CASE (Defects Found in AUTH)

Trong quá trình thiết kế Test Case và quét tĩnh (SonarQube / Qodana) cho phân hệ **`1. Authentication & Security (AUTH)`**, đã phát hiện **3 lỗi bảo mật & logic thực tế** cần ghi nhận lên Jira:

1. 🔴 **[Defect AUTH-BUG-01 — Mức Blocker / Security]: Mật khẩu Admin lưu dạng văn bản thuần (Plain-text) chưa mã hóa Bcrypt**
   * **Vị trí:** `database/netfixvn.sql` (Dòng 202) và `netfix2/admin_login.php` (Dòng 29).
   * **Chi tiết:** Tài khoản `admin@netfixvn.com` lưu mật khẩu trực tiếp là `'admin123'` và so sánh bằng toán tử `$password === $user['password']`, trong khi `login.php` dùng `password_verify()`. Nếu lộ CSDL, mật khẩu Admin bị lộ ngay lập tức.
2. 🟠 **[Defect AUTH-BUG-02 — Mức High / Validation]: Trang `register.php` thiếu kiểm tra `filter_var(..., FILTER_VALIDATE_EMAIL)` ở phía Server**
   * **Vị trí:** `netfix2/register.php` (Dòng 20–27).
   * **Chi tiết:** Ở `edit_profile.php` (dòng 41) có kiểm tra `filter_var($new_email, FILTER_VALIDATE_EMAIL)`, nhưng ở `register.php` lại chỉ dựa vào thuộc tính HTML5 `type="email"` ở trình duyệt. Nếu gửi request POST trực tiếp qua Postman với email sai định dạng, hệ thống vẫn cho phép tạo tài khoản.
3. 🟡 **[Defect AUTH-BUG-03 — Mức Medium / Session]: Trang `login.php` chưa lưu `$_SESSION['username']` khi đăng nhập nhưng `edit_profile.php` lại cập nhật biến này**
   * **Vị trí:** `netfix2/login.php` (Dòng 30–31) so với `netfix2/edit_profile.php` (Dòng 60–61).
   * **Chi tiết:** Lúc đăng nhập chỉ gán `$_SESSION['user_id']` và `$_SESSION['is_admin']`, gây thiếu đồng nhất biến Session giữa các trang.

---

## 6. HƯỚNG DẪN CHẠY KIỂM THỬ TỰ ĐỘNG PHÂN HỆ AUTH (CodeceptJS)

Phân hệ `AUTH` đã được tích hợp kiểm thử giao diện tự động E2E trong [`tests/login_test.js`](../tests/login_test.js):
```powershell
# Chạy kiểm thử tự động phân hệ AUTH trên máy local (XAMPP)
npm test

# Hoặc chạy trực tiếp bằng CodeceptJS kèm hiển thị từng bước
npx codeceptjs run tests/login_test.js --steps
```
