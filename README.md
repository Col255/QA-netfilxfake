# 🎬 Netfix2 — Website xem phim trực tuyến

Netfix2 là website xem phim trực tuyến viết bằng PHP, kết nối MySQL/MariaDB. Dự án có các trang dành cho khách xem phim, người dùng và quản trị viên. Mã nguồn website nằm trong `netfix2/`; repository cũng có file SQL khởi tạo dữ liệu và Postman Collection để gửi thử các request.

> ⚠️ Các trang `.php` cần chạy qua web server có PHP. Không mở trực tiếp file PHP bằng trình duyệt hoặc VS Code.

## 🛠️ Công nghệ sử dụng

- 🐘 PHP và MySQLi
- 🗄️ MySQL / MariaDB
- 🌐 HTML, CSS và JavaScript
- <img src="https://cdn.simpleicons.org/xampp/F37623" alt="XAMPP" width="22"> XAMPP để chạy Apache và MySQL/MariaDB trên máy local
- <img src="https://cdn.simpleicons.org/postman/FF6C37" alt="Postman" width="22"> Postman để gửi request kiểm thử

## ✨ Chức năng chính

- 👤 **Tài khoản:** đăng ký, đăng nhập, đăng xuất, chỉnh sửa hồ sơ và đổi mật khẩu.
- 🍿 **Phim:** duyệt danh sách, lọc theo thể loại/quốc gia, tìm kiếm và xem phim hoặc tập phim.
- 💬 **Tương tác:** bình luận, lưu lịch sử xem và cập nhật lượt xem.
- 💳 **VIP và đơn hàng:** xem gói VIP, tạo và theo dõi đơn hàng, xử lý luồng thanh toán.
- 🛡️ **Quản trị:** quản lý phim, khách hàng, đơn hàng VIP và xem thống kê.

Một số trang và thao tác chỉ dùng được sau khi đăng nhập đúng loại tài khoản. Các trang PHP trong collection Postman có thể trả HTML hoặc chuyển hướng; mã HTTP `200 OK` không tự khẳng định thao tác nghiệp vụ đã thành công. Hãy kiểm tra nội dung phản hồi và dữ liệu trong database.

## 📋 Yêu cầu hệ thống

- Windows (các bước dưới đây dùng XAMPP).
- <img src="https://cdn.simpleicons.org/xampp/F37623" alt="XAMPP" width="22"> XAMPP có Apache và MySQL/MariaDB.
- Trình duyệt web.
- <img src="https://cdn.simpleicons.org/postman/FF6C37" alt="Postman" width="22"> Postman nếu cần gửi các request trong collection.

## 🟧 Cài đặt và chạy với XAMPP

1. Cài XAMPP, mở **XAMPP Control Panel**, nhấn **Start** ở `Apache` và `MySQL`.
2. Chép thư mục `netfix2` vào thư mục web của XAMPP. Đường dẫn thường dùng là `C:\xampp\htdocs\netfix2`.
3. Mở [phpMyAdmin](http://localhost/phpmyadmin), tạo database tên `netfixvn` (nên dùng collation `utf8mb4_unicode_ci`).
4. Chọn database `netfixvn`, mở tab **Import**, chọn file `netfixvn.sql` ở thư mục gốc của repository và nhấn **Import** hoặc **Go**.
5. Kiểm tra cấu hình kết nối trong `netfix2/db.php`. Mặc định:

   ```php
   $host = 'localhost';
   $dbname = 'netfixvn';
   $username = 'root';
   $password = '';
   ```

   Nếu tài khoản MySQL của bạn có mật khẩu hoặc dùng host/cổng khác, cập nhật các giá trị này cho phù hợp.
6. Mở [http://localhost/netfix2/](http://localhost/netfix2/) để truy cập website.

Nếu đổi tên thư mục dự án trong `htdocs`, hãy dùng tên đó trong URL và cập nhật biến `base_url` của Postman Collection.

## 📬 Import Postman Collection

File collection có sẵn ở thư mục gốc: `netfix2_api.json`.

1. Mở Postman và nhấn **Import**.
2. Chọn **Files** hoặc **Upload Files**, rồi chọn `netfix2_api.json`.
3. Nhấn **Import**. Collection **Netfix2 - Backend API Testing** sẽ xuất hiện trong danh sách Collections.
4. Mở collection và kiểm tra biến `base_url`. Giá trị mặc định là `http://localhost/netfix2`.
5. Đảm bảo Apache, MySQL đã chạy và database đã được import; mở request cần thử rồi nhấn **Send**.

