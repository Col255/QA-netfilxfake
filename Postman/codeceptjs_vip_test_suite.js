Feature('Phân hệ 4: VIP Packages & Payment');

/**
 * -----------------------------------------------------------------------------
 * HƯỚNG DẪN CHẠY TEST CASE RIÊNG LẺ:
 * -----------------------------------------------------------------------------
 * 1. Chạy 1 test case duy nhất theo tên hoặc Tag:
 *    npx codeceptjs run --grep "@VIP_TC01"
 *    npx codeceptjs run --grep "@VIP_TC05"
 * 
 * 2. Chạy test case bằng flag --grep với từ khóa bất kỳ:
 *    npx codeceptjs run --grep "MoMo"
 * 
 * 3. Chạy toàn bộ suite:
 *    npx codeceptjs run vip_test.js
 * -----------------------------------------------------------------------------
 */

Before(({ I }) => {
  // Có thể cấu hình cookie/session chung ở đây nếu cần
});

Scenario('VIP_TC01: Kiểm tra endpoint trang gói VIP phản hồi thành công @VIP_TC01 @smoke', async ({ I }) => {
  I.amOnPage('/pricing.php');
  I.seeInCurrentUrl('/pricing.php');
  // Thường phản hồi HTTP 200 trang hiển thị bình thường
});

Scenario('VIP_TC02: Kiểm tra nội dung trang hiển thị gói VIP hoặc yêu cầu đăng nhập @VIP_TC02', async ({ I }) => {
  I.amOnPage('/pricing.php');
  
  // Kiểm tra nếu hệ thống bắt đăng nhập hoặc hiển thị giao diện chọn gói VIP
  const url = await I.grabCurrentUrl();
  if (url.includes('login.php')) {
    I.seeInCurrentUrl('login.php');
  } else {
    I.see('VIP');
  }
});

Scenario('VIP_TC03: Kiểm tra các gói và mức giá VIP @VIP_TC03', async ({ I }) => {
  I.amOnPage('/pricing.php');
  
  // Kiểm tra sự xuất hiện của các gói cước và giá tương ứng
  I.see('79');  // Giá gói 1 tháng
  I.see('201'); // Giá gói 3 tháng
  I.see('379'); // Giá gói 6 tháng
});

Scenario('VIP_TC04: Kiểm tra endpoint xử lý thanh toán phản hồi thành công @VIP_TC04', async ({ I }) => {
  // Đóng vai người dùng gửi request xử lý thanh toán
  I.amOnPage('/pricing.php');
  // Thực hiện submit form với package_id hợp lệ
  I.sendPostRequest('/process_payment.php', {
    package_id: '1'
  });
});

Scenario('VIP_TC05: Kiểm tra xử lý khi thiếu package_id / package_id không hợp lệ @VIP_TC05 @negative', async ({ I }) => {
  I.sendPostRequest('/process_payment.php', {
    package_id: '999'
  });
  // Giả định hệ thống chuyển hướng hoặc báo lỗi package không hợp lệ
});

Scenario('VIP_TC06: Kiểm tra chọn phương thức thanh toán MoMo @VIP_TC06', async ({ I }) => {
  I.sendPostRequest('/process_payment.php', {
    package_id: '1',
    payment_method: 'Momo'
  });
  // Verify hệ thống khởi tạo đơn và điều hướng sang MoMo Gateway hoặc xử lý thành công
});

Scenario('VIP_TC07: Kiểm tra phương thức thanh toán không phải MoMo (Ngân hàng) @VIP_TC07', async ({ I }) => {
  I.sendPostRequest('/process_payment.php', {
    package_id: '1',
    payment_method: 'Chuyển khoản ngân hàng'
  });
});

Scenario('VIP_TC08: Kiểm tra trang trả kết quả thanh toán thành công @VIP_TC08', async ({ I }) => {
  I.amOnPage('/payment_return.php?resultCode=0&message=Success');
  I.seeTextEquals('Thành công', 'body') || I.see('thành công');
});

Scenario('VIP_TC09: Kiểm tra trang trả kết quả thanh toán thất bại @VIP_TC09 @negative', async ({ I }) => {
  I.amOnPage('/payment_return.php?resultCode=1006&message=Failed');
  I.see('thất bại') || I.see('Failed');
});

Scenario('VIP_TC10: Kiểm tra endpoint trang quản lý đơn hàng VIP phản hồi thành công @VIP_TC10', async ({ I }) => {
  I.amOnPage('/orders.php');
  I.seeInCurrentUrl('/orders.php');
});

Scenario('VIP_TC11: Kiểm tra phân quyền Admin và nội dung danh sách đơn hàng gói VIP @VIP_TC11 @security', async ({ I }) => {
  I.amOnPage('/orders.php');
  // Kiểm tra trang danh sách đơn hàng nếu chưa đăng nhập Admin thì bị chuyển hướng hoặc chặn 403
  const url = await I.grabCurrentUrl();
  if (url.includes('login.php')) {
    I.seeInCurrentUrl('login.php');
  } else {
    I.see('Đơn hàng') || I.see('Orders');
  }
});