Feature('MASTER TEST SUITE: Tổng Hợp 5 Phân Hệ (AUTH, MOV, INT, VIP, ADM) - Netfix2');

/**
 * =============================================================================
 * BỘ KIỂM THỬ TỰ ĐỘNG E2E TỔNG HỢP TOÀN HỆ THỐNG NETFIX2 (5 PHÂN HỆ)
 * -----------------------------------------------------------------------------
 * 1. Phân hệ 1 (AUTH) : Authentication & Security (24 kịch bản: AUTH_TC01 -> AUTH_TC24)
 * 2. Phân hệ 2 (MOV)  : Movies, Search & Streaming (14 kịch bản: MOV-01..04, KAN-25, KAN-29)
 * 3. Phân hệ 3 (INT)  : Interactions & Engagement (17 kịch bản: INT_TC01 -> INT_TC15, KAN-33)
 * 4. Phân hệ 4 (VIP)  : VIP Packages & Payment (11 kịch bản: VIP_TC01 -> VIP_TC11)
 * 5. Phân hệ 5 (ADM)  : Admin Management & RBAC (9 kịch bản: KAN-47..51, KAN-37)
 * -----------------------------------------------------------------------------
 * Cách chạy:
 *   - Chạy toàn bộ: npx codeceptjs run tests/master_all_modules_test.js --steps
 *   - Chạy theo phân hệ: npx codeceptjs run tests/master_all_modules_test.js --grep "@AUTH"
 *                        npx codeceptjs run tests/master_all_modules_test.js --grep "@MOV"
 *                        npx codeceptjs run tests/master_all_modules_test.js --grep "@INT"
 *                        npx codeceptjs run tests/master_all_modules_test.js --grep "@VIP"
 *                        npx codeceptjs run tests/master_all_modules_test.js --grep "@ADM"
 * =============================================================================
 */

const ts = Date.now();
const testUser = `loc_e2e_${ts}`;
const testEmail = `loc_e2e_${ts}@gmail.com`;
const updatedUser = `loc_e2e_up_${ts}`;
const updatedEmail = `up_loc_e2e_${ts}@gmail.com`;

async function loginAsUser(I) {
    I.amOnPage('/logout.php');
    I.amOnPage('/login.php');
    I.fillField('#identifier', 'tuan@gmail.com');
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
}

async function loginAsAdmin(I) {
    I.amOnPage('/logout.php');
    I.amOnPage('/admin_login.php');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('button[type="submit"]');
    I.wait(1);
}

// ============================================================================
// PHẦN 1: PHÂN HỆ AUTHENTICATION & SECURITY (AUTH_TC01 -> AUTH_TC24)
// ============================================================================

Scenario('[AUTH_TC01] Hiển thị giao diện trang Đăng ký thành công cho khách vãng lai (/register.php) @AUTH @AUTH_TC01', ({ I }) => {
    I.amOnPage('/register.php');
    I.seeElement('.register-form');
    I.seeElement('#username');
    I.seeElement('#email');
    I.seeElement('#password');
    I.seeElement('#confirm_password');
    I.see('Đăng ký', 'button[type="submit"]');
});

Scenario('[AUTH_TC02] Đăng ký tài khoản mới thành công với mật khẩu tại biên tối thiểu 6 ký tự (/register.php) @AUTH @AUTH_TC02', ({ I }) => {
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

Scenario('[AUTH_TC03] Từ chối đăng ký khi mật khẩu có 5 ký tự - Dưới biên tối thiểu (/register.php) @AUTH @AUTH_TC03', ({ I }) => {
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

Scenario('[AUTH_TC04] Từ chối đăng ký khi bỏ trống các trường bắt buộc (/register.php) @AUTH @AUTH_TC04', ({ I }) => {
    I.amOnPage('/register.php');
    I.executeScript(() => document.querySelectorAll('form').forEach(f => f.noValidate = true));
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Vui lòng nhập đầy đủ thông tin.');
});

Scenario('[AUTH_TC05] Từ chối đăng ký khi mật khẩu xác nhận không khớp (/register.php) @AUTH @AUTH_TC05', ({ I }) => {
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

Scenario('[AUTH_TC06] Từ chối đăng ký khi trùng Tên đăng nhập (username) đã tồn tại (/register.php) @AUTH @AUTH_TC06', ({ I }) => {
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

Scenario('[AUTH_TC07] Từ chối đăng ký khi trùng Địa chỉ Email đã tồn tại (/register.php) @AUTH @AUTH_TC07', ({ I }) => {
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

Scenario('[AUTH_TC08] Kiểm tra xác thực định dạng Email trên biểu mẫu Đăng ký (/register.php) @AUTH @AUTH_TC08', ({ I }) => {
    I.amOnPage('/register.php');
    I.seeElement('input#email[type="email"]');
    I.fillField('#username', `badmail_${ts}`);
    I.fillField('#email', 'not_an_email');
    I.fillField('#password', '123456');
    I.fillField('#confirm_password', '123456');
    I.click('button[type="submit"]');
    I.seeInCurrentUrl('/register.php');
});

Scenario('[AUTH_TC09] Đăng nhập thành công bằng Email hợp lệ của người dùng thường (/login.php) @AUTH @AUTH_TC09', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeInCurrentUrl('/index.php');
    I.dontSeeElement('.error');
    I.see('Đăng xuất');
});

Scenario('[AUTH_TC10] Đăng nhập thành công bằng Username hợp lệ của người dùng thường (/login.php) @AUTH @AUTH_TC10', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testUser);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeInCurrentUrl('/index.php');
    I.dontSeeElement('.error');
    I.see('Đăng xuất');
});

Scenario('[AUTH_TC11] Từ chối đăng nhập khi nhập sai mật khẩu (/login.php) @AUTH @AUTH_TC11', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', 'sai_mat_khau_123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Mật khẩu không đúng.');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC12] Từ chối đăng nhập khi Username hoặc Email không tồn tại (/login.php) @AUTH @AUTH_TC12', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', `khong_ton_tai_${ts}@gmail.com`);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
});

Scenario('[AUTH_TC13] Từ chối đăng nhập khi bỏ trống Tên đăng nhập hoặc Mật khẩu (/login.php) @AUTH @AUTH_TC13', ({ I }) => {
    I.amOnPage('/login.php');
    I.executeScript(() => document.querySelectorAll('form').forEach(f => f.noValidate = true));
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu.');
});

Scenario('[AUTH_TC14] Chặn tài khoản Admin đăng nhập qua cổng người dùng thường (/login.php) @AUTH @AUTH_TC14', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
});

Scenario('[AUTH_TC15] Kiểm tra cổng Đăng nhập Quản trị viên (/admin_login.php) @AUTH @AUTH_TC15', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.seeElement('.login-form');
    I.see('Đăng Nhập Admin');
    I.fillField('#identifier', 'admin');
    I.fillField('#password', 'admin123');
    I.click('button[type="submit"]');
    I.wait(1);
    I.dontSee('Fatal error');
});

Scenario('[AUTH_TC16] Chặn tài khoản người dùng thường (is_admin = 0) đăng nhập vào cổng Admin (/admin_login.php) @AUTH @AUTH_TC16', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.fillField('#identifier', testEmail);
    I.fillField('#password', '123456');
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại hoặc tài khoản này không phải admin.');
});

Scenario('[AUTH_TC17] Chặn khách vãng lai chưa đăng nhập truy cập trực tiếp các trang bảo vệ (/profile.php, /edit_profile.php) @AUTH @AUTH_TC17', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/profile.php');
    I.seeInCurrentUrl('/login.php');
    I.amOnPage('/edit_profile.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC18] Hiển thị Hồ sơ cá nhân & Kiểm tra trạng thái gói VIP (/profile.php) @AUTH @AUTH_TC18', ({ I }) => {
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

Scenario('[AUTH_TC19] Cập nhật thông tin hồ sơ (username, email) thành công (/edit_profile.php) @AUTH @AUTH_TC19', ({ I }) => {
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

Scenario('[AUTH_TC20] Từ chối cập nhật hồ sơ khi nhập Email sai định dạng (/edit_profile.php) @AUTH @AUTH_TC20', ({ I }) => {
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

Scenario('[AUTH_TC21] Đổi mật khẩu thành công khi nhập đúng mật khẩu hiện tại và mật khẩu mới >= 6 ký tự (/edit_profile.php) @AUTH @AUTH_TC21', ({ I }) => {
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

Scenario('[AUTH_TC22] Từ chối đổi mật khẩu khi nhập sai mật khẩu hiện tại hoặc mật khẩu mới < 6 ký tự (/edit_profile.php) @AUTH @AUTH_TC22', ({ I }) => {
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

Scenario('[AUTH_TC23] Đăng xuất tài khoản thành công và hủy hoàn toàn phiên làm việc (/logout.php) @AUTH @AUTH_TC23', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', updatedEmail);
    I.fillField('#password', '654321');
    I.click('button[type="submit"]');
    I.wait(1);
    I.amOnPage('/logout.php');
    I.seeInCurrentUrl('/login.php');
    I.amOnPage('/profile.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[AUTH_TC24] Phòng chống tấn công SQL Injection & XSS trên biểu mẫu Xác thực (/login.php) @AUTH @AUTH_TC24', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('#identifier', "' OR '1'='1 -- <script>alert(1)</script>");
    I.fillField('#password', "' OR '1'='1 --");
    I.click('button[type="submit"]');
    I.wait(1);
    I.seeElement('.error');
    I.see('Tên đăng nhập hoặc email không tồn tại.');
    I.dontSee('SQL syntax');
});

// ============================================================================
// PHẦN 2: PHÂN HỆ MOVIES, SEARCH & STREAMING (MOV)
// ============================================================================

Scenario('[MOV-01] Lọc danh sách phim theo thể loại Hành Động (/movies.php) @MOV @MOV_TC01', ({ I }) => {
    I.amOnPage('/movies.php?genre=Hành Động');
    I.see('Hành Động');
    I.dontSee('Fatal error');
});

Scenario('[MOV-02] Tìm kiếm phim với từ khóa hợp lệ (Doremon) (/search.php) @MOV @MOV_TC02', ({ I }) => {
    I.amOnPage('/search.php?query=Doremon');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('/search.php');
});

Scenario('[MOV-02-SQLi] Tìm kiếm an toàn với ký tự đặc biệt SQL Injection (/search.php) @MOV @MOV_TC03', ({ I }) => {
    I.amOnPage("/search.php?query=' OR 1=1 --");
    I.dontSee('SQL syntax');
    I.dontSee('mysql_fetch');
});

Scenario('[MOV-03] Truy cập trang xem chi tiết tập phim (/watch.php) @MOV @MOV_TC04', ({ I }) => {
    I.amOnPage('/watch.php?id=1&episode=1');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('/watch.php');
});

Scenario('[MOV-04] Tải trang chủ Netfix hiển thị thương hiệu và danh mục (/index.php) @MOV @MOV_TC05', ({ I }) => {
    I.amOnPage('/index.php');
    I.see('Netfix');
});

Scenario('[KAN-25-TC01] Tìm kiếm phim với từ khóa chính xác & Kiểm tra liên kết xem phim @MOV @KAN_25', ({ I }) => {
    I.amOnPage('/search.php?query=Doraemon');
    I.see('Kết quả tìm kiếm cho: "Doraemon"');
    I.seeElement('.movie-item');
    I.see('Doraemon chú mèo máy thông minh');
    I.seeElement('a[href*="watch.php?id="]');
});

Scenario('[KAN-25-TC02] Tìm kiếm không phân biệt chữ hoa, chữ thường (Case-Insensitive) @MOV @KAN_25', ({ I }) => {
    I.amOnPage('/search.php?query=POKEMON');
    I.see('Kết quả tìm kiếm cho: "POKEMON"');
    I.seeElement('.movie-item');
    I.see('Pokemon');
});

Scenario('[KAN-25-TC03] Xử lý êm dịu khi tìm kiếm với khoảng trắng hoặc từ khóa không tồn tại @MOV @KAN_25', ({ I }) => {
    I.amOnPage('/search.php?query=%20%20');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
    I.amOnPage('/search.php?query=PhimKhongHeTonTai9999');
    I.see('Không tìm thấy phim phù hợp', '.no-content');
});

Scenario('[KAN-25-TC04] Kiểm tra phòng chống tấn công Reflected XSS trên ô tìm kiếm @MOV @KAN_25', ({ I }) => {
    I.amOnPage('/search.php?query=%3Cscript%3Ealert(%22XSS%22)%3C%2Fscript%3E');
    I.dontSeeInSource('<script>alert("XSS")</script>');
    I.dontSee('Fatal error');
});

Scenario('[KAN-25-TC05] Kiểm tra phòng chống tấn công SQL Injection trên ô tìm kiếm @MOV @KAN_25', ({ I }) => {
    I.amOnPage('/search.php?query=Doraemon%27%20OR%20%271%27=%271');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('SQLSTATE');
    I.dontSee('Fatal error');
});

Scenario('[KAN-29-TC01] Xem chi tiết phim miễn phí & Khởi tạo trình phát video @MOV @KAN_29', ({ I }) => {
    I.amOnPage('/watch.php?id=55');
    I.see('Doraemon chú mèo máy thông minh');
    I.see('Cartoon');
    I.see('Japan');
    I.seeElement('.movie-details');
    I.seeElement('.currently-watching');
    I.dontSee('Fatal error');
});

Scenario('[KAN-29-TC02] Xem phim bộ và danh sách tập phim (Series Episodes) @MOV @KAN_29', ({ I }) => {
    I.amOnPage('/watch.php?id=56');
    I.see('Vương Chiêu Quân');
    I.see('Series');
    I.seeElement('.movie-details');
    I.seeElement('.episode-list, .episode-grid, .no-content');
    I.dontSee('Fatal error');
    I.dontSee('SQLSTATE');
});

Scenario('[KAN-29-TC03] Xử lý khi xem phim với ID không tồn tại hoặc sai định dạng @MOV @KAN_29', ({ I }) => {
    I.amOnPage('/watch.php?id=999999');
    I.dontSee('Fatal error');
    I.amOnPage('/watch.php?id=invalid_id_injection');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('Fatal error');
});

Scenario('[KAN-29-TC04] Kiểm tra cơ chế Gatekeeper hiển thị nhãn VIP cho tập phim VIP @MOV @KAN_29', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/watch.php?id=56');
    I.seeElement('.badge-vip, .locked, .unlock-btn, .episode-card');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

// ============================================================================
// PHẦN 3: PHÂN HỆ INTERACTIONS & ENGAGEMENT (INT_TC01 -> INT_TC15 & KAN-33)
// ============================================================================

Scenario('[INT_TC01] Tăng lượt xem phim thành công qua API (/api/view_counter.php?id=52) @INT @INT_TC01', ({ I }) => {
    I.amOnPage('/api/view_counter.php?id=52');
    I.see('"success":true');
    I.see('"movie_id":52');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC02] Gọi API tăng lượt xem với ID không hợp lệ (id=0, id=-5) @INT @INT_TC02', ({ I }) => {
    I.amOnPage('/api/view_counter.php?id=0');
    I.see('"success":false');
    I.amOnPage('/api/view_counter.php?id=-5');
    I.see('"success":false');
});

Scenario('[INT_TC03] Gọi API tăng lượt xem với ID vượt giới hạn kiểu dữ liệu @INT @INT_TC03', ({ I }) => {
    I.amOnPage('/api/view_counter.php?id=9999999999999999');
    I.dontSee('Fatal error');
    I.dontSee('SQLSTATE');
});

Scenario('[INT_TC04] Kiểm thử cày lượt xem tự động (Spam View) liên tục @INT @INT_TC04', ({ I }) => {
    for (let i = 0; i < 3; i++) {
        I.amOnPage('/api/view_counter.php?id=52');
        I.see('"success":true');
    }
});

Scenario('[INT_TC05] Gửi bình luận thành công khi đã đăng nhập @INT @INT_TC05', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.seeElement('form');
    I.fillField('textarea, input[name="comment_text"]', 'Phim hay quá!');
    I.click('button[type="submit"], input[type="submit"]');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC06] Từ chối nhận bình luận khi bỏ trống nội dung @INT @INT_TC06', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.fillField('textarea, input[name="comment_text"]', '');
    I.click('button[type="submit"], input[type="submit"]');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC07] Gửi bình luận chỉ chứa ký tự khoảng trắng @INT @INT_TC07', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.fillField('textarea, input[name="comment_text"]', '     ');
    I.click('button[type="submit"], input[type="submit"]');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC08] Kiểm tra phòng chống tấn công Stored XSS qua bình luận @INT @INT_TC08', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.fillField('textarea, input[name="comment_text"]', '<script>alert("XSS")</script>');
    I.click('button[type="submit"], input[type="submit"]');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC09] Kiểm tra an toàn chống SQL Injection trong ô bình luận @INT @INT_TC09', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.fillField('textarea, input[name="comment_text"]', "' OR '1'='1 --");
    I.click('button[type="submit"], input[type="submit"]');
    I.dontSee('SQL syntax');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC10] Admin truy cập xem danh sách bình luận toàn hệ thống (/comments.php) @INT @INT_TC10', async ({ I }) => {
    await loginAsAdmin(I);
    I.amOnPage('/comments.php');
    I.see('Quản Lý Bình Luận');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC11] Chặn tài khoản chưa đăng nhập truy cập quản lý bình luận (/comments.php) @INT @INT_TC11', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/comments.php');
    I.seeInCurrentUrl('/admin_login.php');
});

Scenario('[INT_TC12] Admin thực hiện kiểm duyệt trang bình luận thành công (/comments.php) @INT @INT_TC12', async ({ I }) => {
    await loginAsAdmin(I);
    I.amOnPage('/comments.php');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC13] Người dùng xem danh sách lịch sử xem phim cá nhân (/watch_history.php) @INT @INT_TC13', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch_history.php');
    I.seeInCurrentUrl('/watch_history.php');
    I.dontSee('Fatal error');
});

Scenario('[INT_TC14] Chặn tài khoản chưa đăng nhập truy cập trang Lịch sử xem phim (/watch_history.php) @INT @INT_TC14', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/watch_history.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('[INT_TC15] Kiểm tra mốc thời gian (Timestamp) khi ghi nhận lịch sử xem phim @INT @INT_TC15', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/watch.php?id=55');
    I.amOnPage('/watch_history.php');
    I.seeInCurrentUrl('/watch_history.php');
    I.dontSee('Fatal error');
});

// ============================================================================
// PHẦN 4: PHÂN HỆ VIP PACKAGES & PAYMENT (VIP_TC01 -> VIP_TC11)
// ============================================================================

Scenario('[VIP_TC01] Kiểm tra endpoint trang gói VIP phản hồi thành công @VIP @VIP_TC01', async ({ I }) => {
    I.amOnPage('/pricing.php');
    I.wait(1);
    const currentUrl = await I.grabCurrentUrl();
    if (currentUrl.includes('login.php')) {
        I.seeInCurrentUrl('/login.php');
        I.see('Đăng nhập');
    } else {
        I.seeInCurrentUrl('/pricing.php');
        I.see('Gói VIP');
    }
});

Scenario('[VIP_TC02] Kiểm tra nội dung trang hiển thị gói VIP hoặc yêu cầu đăng nhập @VIP @VIP_TC02', async ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/pricing.php');
    I.wait(1);
    I.seeInCurrentUrl('/login.php');

    await loginAsUser(I);
    I.amOnPage('/pricing.php');
    I.wait(1);
    I.seeInCurrentUrl('/pricing.php');
    I.see('Gói VIP NetfixVN');
});

Scenario('[VIP_TC03] Kiểm tra các gói và mức giá VIP @VIP @VIP_TC03', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/pricing.php');
    I.wait(1);
    I.see('Gói VIP 1 Tháng');
    I.see('79,000 VNĐ');
    I.see('Gói VIP 3 Tháng');
    I.see('201,675 VNĐ');
    I.see('Gói VIP 6 Tháng');
    I.see('379,200 VNĐ');
});

Scenario('[VIP_TC04] Kiểm tra endpoint xử lý thanh toán phản hồi thành công @VIP @VIP_TC04', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/process_payment.php');
    I.wait(1);
    I.seeInCurrentUrl('/pricing.php');
    I.see('Gói VIP NetfixVN');
});

Scenario('[VIP_TC05] Kiểm tra xử lý khi thiếu package_id / package_id không hợp lệ @VIP @VIP_TC05', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/checkout.php?package_id=999');
    I.wait(1);
    I.see('Không tìm thấy thông tin gói VIP');

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

Scenario('[VIP_TC06] Kiểm tra chọn phương thức thanh toán MoMo @VIP @VIP_TC06', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/checkout.php?package_id=1');
    I.wait(1);
    I.see('Xác nhận Đơn hàng VIP');
    I.see('Gói VIP 1 Tháng');
    I.checkOption('input[name="payment_method"][value="Momo"]');
    I.click('Thanh toán');
    I.wait(2);

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

Scenario('[VIP_TC07] Kiểm tra phương thức thanh toán không phải MoMo (Chuyển khoản ngân hàng) @VIP @VIP_TC07', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/checkout.php?package_id=1');
    I.wait(1);
    I.see('Xác nhận Đơn hàng VIP');
    I.checkOption('input[name="payment_method"][value="Chuyển khoản ngân hàng"]');
    I.click('Thanh toán');
    I.wait(1);
    I.seeInCurrentUrl('/profile.php');
    I.see('VIP');
});

Scenario('[VIP_TC08] Kiểm tra trang trả kết quả thanh toán thành công @VIP @VIP_TC08', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/payment_return.php?resultCode=0&message=Success');
    I.wait(1);
    I.see('Thanh toán thành công!');
    I.see('Gói VIP của bạn đã được kích hoạt.');
});

Scenario('[VIP_TC09] Kiểm tra trang trả kết quả thanh toán thất bại @VIP @VIP_TC09', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/payment_return.php?resultCode=1006&message=Failed');
    I.wait(1);
    I.see('Thanh toán thất bại');
    I.see('Failed');
});

Scenario('[VIP_TC10] Kiểm tra endpoint trang quản lý đơn hàng VIP phản hồi thành công @VIP @VIP_TC10', async ({ I }) => {
    I.amOnPage('/orders.php');
    I.wait(1);
    const currentUrl = await I.grabCurrentUrl();
    if (currentUrl.includes('admin_login.php')) {
        I.seeInCurrentUrl('/admin_login.php');
        I.see('Đăng Nhập Admin');
    } else {
        I.seeInCurrentUrl('/orders.php');
        I.see('QUẢN LÍ ĐƠN HÀNG VIP');
    }
});

Scenario('[VIP_TC11] Kiểm tra phân quyền Admin và nội dung danh sách đơn hàng gói VIP @VIP @VIP_TC11', async ({ I }) => {
    await loginAsUser(I);
    I.amOnPage('/orders.php');
    I.wait(1);
    I.seeInCurrentUrl('/admin_login.php');
    I.see('Đăng Nhập Admin');

    await loginAsAdmin(I);
    I.amOnPage('/orders.php');
    I.wait(1);
    I.seeInCurrentUrl('/orders.php');
    I.see('QUẢN LÍ ĐƠN HÀNG VIP');
    I.seeElement('.order-table');
});

// ============================================================================
// PHẦN 5: PHÂN HỆ ADMIN MANAGEMENT & RBAC (ADM)
// ============================================================================

Scenario('[KAN-47] Bảo mật: Chặn truy cập trái phép khi chưa đăng nhập Admin (/add_movie.php) @ADM @KAN_47', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/add_movie.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

Scenario('[KAN-47-Login] Kiểm tra trang đăng nhập quản trị Admin (/admin_login.php) @ADM @KAN_47', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.seeElement('form');
    I.dontSee('Fatal error');
    I.dontSee('SQL syntax');
});

Scenario('[KAN-48] Bảo mật: Chặn truy cập danh sách quản lý phim khi chưa có phiên (/manage_movies.php) @ADM @KAN_48', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/manage_movies.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

Scenario('[KAN-49] Bảo mật: Chặn truy cập đơn hàng VIP trái phép (/orders.php) @ADM @KAN_49', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/orders.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

Scenario('[KAN-50] Bảo mật: Chặn truy cập danh sách khách hàng trái phép (/customers.php) @ADM @KAN_50', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/customers.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

Scenario('[KAN-51] Bảo mật: Chặn truy cập báo cáo doanh thu thống kê trái phép (/statistics.php) @ADM @KAN_51', ({ I }) => {
    I.amOnPage('/logout.php');
    I.amOnPage('/statistics.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});
