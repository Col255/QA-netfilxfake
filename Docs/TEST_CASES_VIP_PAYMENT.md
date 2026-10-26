- **Mã Task Jira:** KAN-43
- **Thành viên thực hiện:** Lê Hoàng Nhật Bình

---

## 1. Test Cases cho Module Pricing (Bảng giá gói VIP)
- **TC_VIP_01** | `GET /vip/pricing.php` | Kiểm tra hiển thị bảng giá gói VIP | Gửi request `GET` tới API pricing | **Precondition:** Server hoạt động bình thường | **Expected Result:** Trả về mã `200 OK`, hiển thị đúng giao diện bảng giá các gói VIP (Tháng, Năm). | **Priority:** High | **Type:** Positive

---

## 2. Test Cases cho Module Payment (Xử lý thanh toán)
- **TC_PAY_01** | `POST /vip/payment/process_payment.php` | Thực hiện thanh toán gói VIP thành công | Gửi request `POST` chọn gói VIP hợp lệ | **Precondition:** Người dùng đã đăng nhập hệ thống | **Expected Result:** Xử lý thành công, chuyển hướng tới cổng thanh toán hoặc trang xác nhận (Mã phản hồi `302 Found` hoặc `200 OK`). | **Priority:** High | **Type:** Positive

- **TC_PAY_02** | `POST /vip/payment/process_payment.php` | Thanh toán thất bại khi chưa đăng nhập | Gửi request thanh toán mà không cung cấp phiên đăng nhập (Session/Token) | **Precondition:** Người dùng chưa đăng nhập | **Expected Result:** Hệ thống từ chối xử lý, chuyển hướng về trang đăng nhập hoặc báo lỗi xác thực. | **Priority:** Medium | **Type:** Negative

---

## 3. Test Cases cho Module Orders (Lịch sử đơn hàng)
- **TC_ORD_01** | `GET /vip/orders/order_history.php` | Kiểm tra hiển thị lịch sử đơn hàng VIP | Gửi request `GET` tới API order_history | **Precondition:** Người dùng đã từng thực hiện giao dịch mua gói VIP | **Expected Result:** Trả về mã `200 OK`, hiển thị danh sách các đơn hàng đã mua kèm trạng thái (Pending/Completed), tổng giá trị và phương thức thanh toán (Momo/VNPay). | **Priority:** High | **Type:** Positive
  KAN-43 Thiết kế Test Cases cho VIP Packages & Payment
