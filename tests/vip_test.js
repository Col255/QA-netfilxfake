Feature('Phân hệ 4: VIP Packages & Payment (VIP_TC01 -> VIP_TC11)');

/**
 * -----------------------------------------------------------------------------
 * BỘ KIỂM THỬ GIAO DIỆN & TÍCH HỢP E2E - PHÂN HỆ 4: VIP PACKAGES & PAYMENT
 * Tác giả: Lê Hoàng Nhật Bình (11 Test Cases: VIP_TC01 -> VIP_TC11)
 * -----------------------------------------------------------------------------
 * Hướng dẫn chạy:
 * 1. Chạy toàn bộ 11 kịch bản VIP:
 *    npx codeceptjs run tests/vip_test.js --steps
 * 2. Chạy từng kịch bản riêng lẻ theo mã Test Case:
 *    npx codeceptjs run tests/vip_test.js --grep "@VIP_TC01"
 *    npx codeceptjs run tests/vip_test.js --grep "@VIP_TC07"
 * -----------------------------------------------------------------------------
 */

async function loginAsUser(I) {
    I.amOnPage('/logout.php');
    I.amOnPage('/login.php');
    I.fillField('#identifier', 'tuan@gmail.com');
    I.fillField('#password', '123456');
    I.click('Đăng nhập');
    I.wait(1);
    I.seeInCurrentUrl('/index.php');
}

async function loginAsAdmin(I) {
    I.amOnPage('/logout.php');
    I.amOnPage('/admin_login.php');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('Đăng nhập Admin');
    I.wait(1);
    I.seeInCurrentUrl('/admin_dashboard.php');
}

Scenario('[VIP_TC01] Kiểm tra endpoint trang gói VIP phản hồi thành công @VIP_TC01 @smoke', async ({ I }) => {
    I.amOnPage('/pricing.php');
    I.wait(1);
    const currentUrl = await I.grabCurrentUrl();
    // Khi chưa đăng nhập sẽ redirect hợp lệ về login.php, hoặc hiển thị pricing.php nếu đã có session
    if (currentUrl.includes('login.php')) {
        I.seeInCurrentUrl('/login.php');
        I.see('Đăng nhập');
    } else {
        I.seeInCurrentUrl('/pricing.php');
        I.see('Gói VIP');
    }
});

Scenario('[VIP_TC02] Kiểm tra nội dung trang hiển thị gói VIP hoặc yêu cầu đăng nhập @VIP_TC02', async ({ I }) => {
    // Bước 1: Khi chưa đăng nhập -> yêu cầu đăng nhập
    I.amOnPage('/logout.php');
    I.amOnPage('/pricing.php');
    I.wait(1);
    I.seeInCurrentUrl('/login.php');

    // Bước 2: Sau khi đăng nhập -> hiển thị trang Gói VIP NetfixVN
    await loginAsUser(I);
    I.amOnPage('/pricing.php');
    I.wait(1);
    I.seeInCurrentUrl('/pricing.php');
    I.see('Gói VIP NetfixVN');
});

Scenario('[VIP_TC03] Kiểm tra các gói và mức giá VIP @VIP_TC03', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/pricing.php');
    I.wait(1);

    // Kiểm tra tên 3 gói VIP và mức giá hiển thị theo cấu hình pricing.php
    I.see('Gói VIP 1 Tháng');
    I.see('79,000 VNĐ');

    I.see('Gói VIP 3 Tháng');
    I.see('201,675 VNĐ');

    I.see('Gói VIP 6 Tháng');
    I.see('379,200 VNĐ');
});

Scenario('[VIP_TC04] Kiểm tra endpoint xử lý thanh toán phản hồi thành công @VIP_TC04', async ({ I }) => {
    await loginAsUser(I);
    // Truy cập GET /process_payment.php -> hệ thống phản hồi và điều hướng an toàn về pricing.php
    I.amOnPage('/process_payment.php');
    I.wait(1);
    I.seeInCurrentUrl('/pricing.php');
    I.see('Gói VIP NetfixVN');
});

Scenario('[VIP_TC05] Kiểm tra xử lý khi thiếu package_id / package_id không hợp lệ @VIP_TC05 @negative', async ({ I }) => {
    await loginAsUser(I);

    // Trường hợp 1: Truy cập checkout.php với package_id=999 (ngoài khoảng 1-3)
    I.amOnPage('/checkout.php?package_id=999');
    I.wait(1);
    I.see('Không tìm thấy thông tin gói VIP');

    // Trường hợp 2: Gửi POST trực tiếp tới process_payment.php với package_id=999
    await I.executeScript(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process_payment.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'package_id';
        input.value = '999';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });
    I.wait(1);
    I.seeInCurrentUrl('/profile.php');
    I.see('Gói VIP không hợp lệ');
});

Scenario('[VIP_TC06] Kiểm tra chọn phương thức thanh toán MoMo @VIP_TC06', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/checkout.php?package_id=1');
    I.wait(1);
    I.see('Xác nhận Đơn hàng VIP');
    I.see('Gói VIP 1 Tháng');

    // Chọn phương thức thanh toán Momo và submit
    I.checkOption('input[name="payment_method"][value="Momo"]');
    I.click('Thanh toán');
    I.wait(2);

    // Hệ thống chuyển hướng tới cổng MoMo hoặc hiển thị phản hồi xử lý từ process_payment.php
    const currentUrl = await I.grabCurrentUrl();
    const pageSource = await I.grabSource();
    const isHandled =
        currentUrl.includes('momo.vn') ||
        currentUrl.includes('process_payment.php') ||
        pageSource.includes('MoMo');
    if (!isHandled) {
        throw new Error('Luồng thanh toán MoMo không phản hồi như mong đợi');
    }
});

Scenario('[VIP_TC07] Kiểm tra phương thức thanh toán không phải MoMo (Chuyển khoản ngân hàng) @VIP_TC07', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/checkout.php?package_id=1');
    I.wait(1);
    I.see('Xác nhận Đơn hàng VIP');

    // Chọn phương thức Chuyển khoản ngân hàng và bấm Thanh toán
    I.checkOption('input[name="payment_method"][value="Chuyển khoản ngân hàng"]');
    I.click('Thanh toán');
    I.wait(1);

    // Hệ thống tạo đơn Completed, kích hoạt VIP và chuyển về profile.php
    I.seeInCurrentUrl('/profile.php');
    I.see('VIP');
});

Scenario('[VIP_TC08] Kiểm tra trang trả kết quả thanh toán thành công @VIP_TC08', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/payment_return.php?resultCode=0&message=Success');
    I.wait(1);
    I.see('Thanh toán thành công!');
    I.see('Gói VIP của bạn đã được kích hoạt.');
});

Scenario('[VIP_TC09] Kiểm tra trang trả kết quả thanh toán thất bại @VIP_TC09 @negative', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/payment_return.php?resultCode=1006&message=Failed');
    I.wait(1);
    I.see('Thanh toán thất bại');
    I.see('Failed');
});

Scenario('[VIP_TC10] Kiểm tra endpoint trang quản lý đơn hàng VIP phản hồi thành công @VIP_TC10', async ({ I }) => {
    I.amOnPage('/orders.php');
    I.wait(1);
    const currentUrl = await I.grabCurrentUrl();
    // Phản hồi HTTP 200 (trang orders.php nếu là Admin hoặc trang admin_login.php nếu chưa đăng nhập Admin)
    if (currentUrl.includes('admin_login.php')) {
        I.seeInCurrentUrl('/admin_login.php');
        I.see('Đăng Nhập Admin');
    } else {
        I.seeInCurrentUrl('/orders.php');
        I.see('QUẢN LÍ ĐƠN HÀNG VIP');
    }
});

Scenario('[VIP_TC11] Kiểm tra phân quyền Admin và nội dung danh sách đơn hàng gói VIP @VIP_TC11 @security', async ({ I }) => {
    // Trường hợp 1: Người dùng thường hoặc khách truy cập /orders.php -> bị chặn và chuyển về admin_login.php
    await loginAsUser(I);
    I.amOnPage('/orders.php');
    I.wait(1);
    I.seeInCurrentUrl('/admin_login.php');
    I.see('Đăng Nhập Admin');

    // Trường hợp 2: Đăng nhập Admin hợp lệ -> xem được danh sách đơn hàng VIP
    await loginAsAdmin(I);
    I.amOnPage('/orders.php');
    I.wait(1);
    I.seeInCurrentUrl('/orders.php');
    I.see('QUẢN LÍ ĐƠN HÀNG VIP');
    I.seeElement('.order-table');
});
