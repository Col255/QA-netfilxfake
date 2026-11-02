# TEST CASE – VIP PACKAGE & PAYMENT

**Họ và tên:** Lê Hoàng Nhật Bình  
**Phân hệ phụ trách:** VIP Package và Payment  
**Dự án:** NetfixVN  
**Công cụ kiểm thử:** Laragon, Postman  
**Base URL:** `http://localhost/netfix2`

---

## 1. Mục tiêu kiểm thử

Kiểm tra trang hiển thị các gói VIP, chức năng khởi tạo thanh toán và trang lịch sử đơn hàng. Đồng thời kiểm tra mã trạng thái HTTP, thời gian phản hồi và một số điều kiện truy cập.

## 2. Môi trường kiểm thử

- Khởi động Apache và MySQL bằng Laragon.
- Website chạy tại `http://localhost/netfix2`.
- Gửi request và xem response bằng Postman.
- Các kết quả được ghi nhận trong tài liệu dựa trên những lần chạy thực tế; những trường hợp chưa chạy cần được kiểm thử bổ sung.

## 3. Danh sách test case

| Test Case ID | Chức năng | Phương thức / Endpoint | Các bước kiểm thử trên Postman | Kết quả mong đợi | Kết quả ghi nhận |
|---|---|---|---|---|---|
| VIP-01 | Hiển thị bảng giá VIP | GET `/pricing.php` | 1. Chọn GET. 2. Nhập `{{baseUrl}}/pricing.php`. 3. Nhấn Send. | Trang phản hồi thành công; hiển thị nội dung bảng giá hoặc yêu cầu đăng nhập theo trạng thái phiên. | HTTP 200; các assertion hiển thị PASS trong ảnh Postman. |
| VIP-02 | Thời gian phản hồi bảng giá | GET `/pricing.php` | Gửi request và xem thời gian phản hồi. | Thời gian phản hồi dưới 1500 ms theo assertion hiện tại. | Ảnh Postman ghi nhận khoảng 5 ms; assertion PASS. |
| PAY-01 | Khởi tạo thanh toán | POST `/process_payment.php` | 1. Chọn POST. 2. Nhập `{{baseUrl}}/process_payment.php`. 3. Thiết lập body theo tham số mà mã nguồn yêu cầu. 4. Nhấn Send. | Hệ thống xử lý yêu cầu thanh toán hợp lệ và phản hồi theo luồng thanh toán đã cấu hình. | HTTP 200; thời gian phản hồi khoảng 29 ms; assertion về status code và response time hiển thị PASS. Cần kiểm tra thêm body và đơn hàng trong database để xác nhận luồng thanh toán. |
| PAY-02 | Thời gian phản hồi xử lý thanh toán | POST `/process_payment.php` | Gửi request và xem thời gian phản hồi. | Thời gian phản hồi dưới 2000 ms theo assertion hiện tại. | Ảnh Postman ghi nhận khoảng 29 ms; assertion PASS. |
| ORD-01 | Xem lịch sử đơn hàng | GET `/order_history.php` | 1. Chọn GET. 2. Nhập `{{baseUrl}}/order_history.php`. 3. Nhấn Send. | Hệ thống trả về trang lịch sử đơn hàng hoặc chuyển hướng đăng nhập theo trạng thái phiên. | HTTP 200; ảnh Postman hiển thị Test Results 2/2 và các assertion PASS. |
| ORD-02 | Thời gian phản hồi lịch sử đơn hàng | GET `/order_history.php` | Gửi request và xem thời gian phản hồi. | Thời gian phản hồi dưới 1500 ms theo assertion hiện tại. | Ảnh Postman ghi nhận khoảng 5 ms ở phần kết quả tổng hợp; assertion PASS. |

## 4. Kiểm thử bổ sung cần thực hiện

Các trường hợp dưới đây chưa được xác nhận chỉ từ ảnh Postman hiện có. Cần thực hiện riêng và cập nhật kết quả sau khi chạy:

1. Truy cập `/pricing.php` khi chưa đăng nhập và khi đã đăng nhập.
2. Gửi POST thanh toán thiếu tham số hoặc dùng `package_id` không hợp lệ.
3. Thử phương thức thanh toán không được hỗ trợ.
4. Kiểm tra giá tiền được lấy từ phía server, không tin giá do client tự gửi.
5. Kiểm tra trạng thái đơn hàng và quyền VIP trong MySQL sau khi thanh toán.
6. Đăng nhập bằng hai tài khoản khác nhau để xác nhận mỗi tài khoản chỉ xem được lịch sử đơn hàng của chính mình.
7. Kiểm tra kết quả thanh toán MoMo trong môi trường thử nghiệm; không sử dụng giao dịch tiền thật.

## 5. Lưu ý

- HTTP 200 chỉ cho biết request nhận được phản hồi thành công ở tầng HTTP; chưa đủ để khẳng định giao dịch thanh toán thành công.
- Cần đối chiếu giá các gói VIP trong giao diện, mã nguồn và tài liệu yêu cầu trước khi kết luận test case Pass/Fail.
- Chỉ cập nhật kết quả thành **PASS** hoặc **FAIL** sau khi đã thực hiện và kiểm tra đầy đủ. Với trường hợp chưa chạy, ghi **NOT RUN**.
- Lưu ảnh chụp màn hình Postman và kết quả kiểm tra database làm bằng chứng kiểm thử.
