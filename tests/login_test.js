Feature('Kiểm thử giao diện Đăng nhập Netfix2');

Scenario('Test 1: Đăng nhập thành công với tài khoản hợp lệ', ({ I }) => {
    I.amOnPage('/login.php');

    // Điền chính xác theo id="identifier" của form
    I.fillField('#identifier', 'tuan@gmail.com');

    // Điền chính xác theo id="password" của form
    I.fillField('#password', '123456');

    // Bấm đúng vào nút có chữ "Đăng nhập"
    I.click('Đăng nhập');
    I.wait(2);

    // Kiểm tra chuyển hướng thành công về trang chủ
    I.seeInCurrentUrl('/index.php');
});

Scenario('Test 2: Đăng nhập thất bại khi sai mật khẩu', ({ I }) => {
    I.amOnPage('/login.php');

    I.fillField('#identifier', 'tuan@gmail.com');
    I.fillField('#password', 'sai_mat_khau_123');
    I.click('Đăng nhập');
    I.wait(1);

    // Kiểm tra có hiện hộp thông báo lỗi class="error" của PHP
    I.seeElement('.error');

    // Vẫn ở lại trang login.php
    I.seeInCurrentUrl('/login.php');
});