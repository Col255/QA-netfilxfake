Feature('Phân Hệ 1: AUTH & SECURITY — Kiểm Thử Giao Diện E2E (AUTH_TC01 -> AUTH_TC24)');

// Biến động dùng chung xuyên suốt 24 kịch bản để chạy bao nhiêu lần cũng PASS 100%
const ts = Date.now();
const testUser = `loc_e2e_${ts}`;
const testEmail = `loc_e2e_${ts}@gmail.com`;
const updatedUser = `loc_e2e_up_${ts}`;
const updatedEmail = `up_loc_e2e_${ts}@gmail.com`;

// ============================================================================
// NHÓM 1: ĐĂNG KÝ TÀI KHOẢN (/register.php) — AUTH_TC01 -> AUTH_TC08
// ============================================================================

Scenario('[AUTH_TC01] Hiển thị giao diện trang Đăng ký thành công cho khách vãng lai (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.seeElement('.register-form');
    I.seeElement('#username');
    I.seeElement('#email');
    I.seeElement('#password');
    I.seeElement('#confirm_password');
    I.see('Đăng ký', 'button[type="submit"]');
});

Scenario('[AUTH_TC02] Đăng ký tài khoản mới thành công với mật khẩu tại biên tối thiểu 6 ký tự (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.fillField('#username', testUser);
    I.fillField('#email', testEmail);
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.success');
    I.see('Đăng ký thành công! Vui lòng đăng nhập.');
});

Scenario('[AUTH_TC03] Từ chối đăng ký khi mật khẩu có 5 ký tự - Dưới biên tối thiểu (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.fillField('#username', `bva5_${ts}`);
    I.fillField('#email', `bva5_${ts}@gmail.com`);
    I.fillField('#password', '12345');
    I.fillField('#confirm_password', '12345');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Mật khẩu phải có ít nhất 6 ký tự.');
});

Scenario('[AUTH_TC04] Từ chối đăng ký khi bỏ trống các trường bắt buộc (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    // Tắt HTML5 required tạm thời để kiểm tra thông báo lỗi của tầng Backend PHP
    I.executeScript(() => document.querySelectorAll('form').forEach(f => f.noValidate = true));
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Vui lòng nhập đầy đủ thông tin.');
});

Scenario('[AUTH_TC05] Từ chối đăng ký khi mật khẩu xác nhận không khớp (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.fillField('#username', `mismatch_${ts}`);
    I.fillField('#email', `mismatch_${ts}@gmail.com`);
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '654321');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Mật khẩu xác nhận không khớp.');
});

Scenario('[AUTH_TC06] Từ chối đăng ký khi trùng Tên đăng nhập (username) đã tồn tại (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.fillField('#username', testUser);
    I.fillField('#email', `other_mail_${ts}@gmail.com`);
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email đã được sử dụng.');
});

Scenario('[AUTH_TC07] Từ chối đăng ký khi trùng Địa chỉ Email đã tồn tại (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.fillField('#username', `other_user_${ts}`);
    I.fillField('#email', testEmail);
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email đã được sử dụng.');
});

Scenario('[AUTH_TC08] Kiểm tra xác thực định dạng Email trên biểu mẫu Đăng ký (/register.php)', ({ I }) => {
    I.amOnPage('/register.php');
    I.seeElement('input#email[type="email"]');
    I.fillField('#username', `badmail_${ts}`);
    I.fillField('#email', 'not_an_email');
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '123456');
    I.click('button[type="submit"]');
    // Trình duyệt giữ lại tại trang register.php do HTML5 type="email" chặn
    I.seeInCurrentUrl('/register.php');
});

// ============================================================================
// NHÓM 2: ĐĂNG NHẬP NGƯỜI DÙNG (/login.php) — AUTH_TC09 -> AUTH_TC14
// ============================================================================

Scenario('[AUTH_TC09] Đăng nhập thành công bằng Email hợp lệ của người dùng thường (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeInCurrentUrl('/index.php');
    I.dontSeeElement('.error');
    I.see('Đăng xuất');
});

Scenario('[AUTH_TC10] Đăng nhập thành công bằng Username hợp lệ của người dùng thường (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testUser);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeInCurrentUrl('/index.php');
    I.dontSeeElement('.error');
    I.see('Đăng xuất');
});

Scenario('[AUTH_TC11] Từ chối đăng nhập khi nhập sai mật khẩu (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', 'sai_mat_khau_123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Mật khẩu không đúng.');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC12] Từ chối đăng nhập khi Username hoặc Email không tồn tại (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', `khong_ton_tai_${ts}@gmail.com`);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
});

Scenario('[AUTH_TC13] Từ chối đăng nhập khi bỏ trống Tên đăng nhập hoặc Mật khẩu (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.executeScript(() => document.querySelectorAll('form').forEach(f => f.noValidate = true));
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu.');
});

Scenario('[AUTH_TC14] Chặn tài khoản Admin đăng nhập qua cổng người dùng thường (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
});

// ============================================================================
// NHÓM 3: ĐĂNG NHẬP ADMIN & PHÂN QUYỀN RBAC (/admin_login.php) — AUTH_TC15 -> AUTH_TC16
// ============================================================================

Scenario('[AUTH_TC15] Kiểm tra cổng Đăng nhập Quản trị viên (/admin_login.php)', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.seeElement('.login-form');
    I.see('Đăng Nhập Admin');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.dontSee('Fatal error');
});

Scenario('[AUTH_TC16] Chặn tài khoản người dùng thường (is_admin = 0) đăng nhập vào cổng Admin (/admin_login.php)', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại hoặc tài khoản này không phải admin.');
});

// ============================================================================
// NHÓM 4: QUẢN LÝ HỒ SƠ & BẢO VỆ ROUTE (/profile.php, /edit_profile.php) — AUTH_TC17 -> AUTH_TC20
// ============================================================================

Scenario('[AUTH_TC17] Chặn khách vãng lai chưa đăng nhập truy cập trực tiếp các trang bảo vệ (/profile.php, /edit_profile.php)', ({ I }) => {
    I.amOnPage('/profile.php');
    I.seeInCurrentUrl('/login.php');
    I.amOnPage('/edit_profile.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC18] Hiển thị Hồ sơ cá nhân & Kiểm tra trạng thái gói VIP (/profile.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/profile.php');
    I.see('Hồ sơ của bạn');
    I.see(testUser);
    I.see('Trạng thái VIP:');
});

Scenario('[AUTH_TC19] Cập nhật thông tin hồ sơ (username, email) thành công (/edit_profile.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/edit_profile.php');
    I.fillField('#username', updatedUser);
    I.fillField('#email', updatedEmail);
    I.click('button[name="update_profile"]');
    I.wait(1);
    I.see('Thông tin cá nhân đã được cập nhật thành công!');
    I.see(updatedUser);
});

Scenario('[AUTH_TC20] Từ chối cập nhật hồ sơ khi nhập Email sai định dạng (/edit_profile.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', updatedEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/edit_profile.php');
    I.executeScript(() => document.querySelectorAll('form').forEach(f => f.noValidate = true));
    I.fillField('#username', updatedUser);
    I.fillField('#email', 'sai_dinh_dang_email');
    I.click('button[name="update_profile"]');
    I.wait(1);
    I.see('Địa chỉ email không hợp lệ.');
});

// ============================================================================
// NHÓM 5: ĐỔI MẬT KHẨU, ĐĂNG XUẤT & BẢO MẬT OWASP — AUTH_TC21 -> AUTH_TC24
// ============================================================================

Scenario('[AUTH_TC21] Đổi mật khẩu thành công khi nhập đúng mật khẩu hiện tại và mật khẩu mới >= 6 ký tự (/edit_profile.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', updatedEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/edit_profile.php');
    I.fillField('#current_password', '123456');
    I.fillField('#new_password', '654321');
    I.fillField('#confirm_new_password', '654321');
    I.click('button[name="change_password"]');
    I.wait(1);
    I.see('Mật khẩu đã được thay đổi thành công!');
});

Scenario('[AUTH_TC22] Từ chối đổi mật khẩu khi nhập sai mật khẩu hiện tại hoặc mật khẩu mới < 6 ký tự (/edit_profile.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', updatedEmail);
    I.fillField('#password', '654321');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/edit_profile.php');
    I.fillField('#current_password', '654321');
    I.fillField('#new_password', '12345');
    I.fillField('#confirm_new_password', '12345');
    I.click('button[name="change_password"]');
    I.wait(1);
    I.see('Mật khẩu mới phải có ít nhất 6 ký tự.');
});

Scenario('[AUTH_TC23] Đăng xuất tài khoản thành công và hủy hoàn toàn phiên làm việc (/logout.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', updatedEmail);
    I.fillField('#password', '654321');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/logout.php');
    I.seeInCurrentUrl('/login.php');
    // Thử truy cập lại /profile.php sau khi đã đăng xuất -> phải bị chặn về login.php
    I.amOnPage('/profile.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC24] Phòng chống tấn công SQL Injection & XSS trên biểu mẫu Xác thực (/login.php)', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', "' OR '1'='1 -- <script>alert(1)</script>");
    I.fillField('#password', "' OR '1'='1 --");
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
    I.dontSee('SQL syntax');
});
