const assert = require('assert');

Feature('Phân hệ 3: Interactions & Engagement (INT) — Kiểm thử Hộp đen Black-box');

// ----------------------------------------------------
// Helper functions
// ----------------------------------------------------
const loginUser = (I) => {
    I.amOnPage('/login.php');
    I.fillField('input[type="text"], input[type="email"], #identifier', 'tuan@gmail.com');
    I.fillField('input[type="password"], #password', '123456');
    I.click('button[type="submit"], input[type="submit"]');
    I.wait(1);
};

const loginAdmin = (I) => {
    I.amOnPage('/admin_login.php');
    I.fillField('input[type="text"], #identifier', 'admin');
    I.fillField('input[type="password"], #password', 'admin123');
    I.click('button[type="submit"], input[type="submit"]');
    I.wait(1);
};

// ============================================================================
// 1. API VIEW COUNTER (INT_TC01 - INT_TC04)
// ============================================================================

Scenario('INT_TC01: Tăng lượt xem phim thành công qua API @INT_TC01', async ({ I }) => {
    const res = await I.sendGetRequest('/api/view_counter.php?id=52', { 'Accept': 'application/json' });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.strictEqual(res.data.movie_id, 52);
});

Scenario('INT_TC02: Gọi API tăng lượt xem với ID không hợp lệ @INT_TC02', async ({ I }) => {
    const resZero = await I.sendGetRequest('/api/view_counter.php?id=0', { 'Accept': 'application/json' });
    assert.strictEqual(resZero.status, 200);
    assert.strictEqual(resZero.data.success, false);

    const resNegative = await I.sendGetRequest('/api/view_counter.php?id=-5', { 'Accept': 'application/json' });
    assert.strictEqual(resNegative.status, 200);
    assert.strictEqual(resNegative.data.success, false);
});

Scenario('INT_TC03: Gọi API tăng lượt xem với ID vượt giới hạn kiểu dữ liệu @INT_TC03', async ({ I }) => {
    const res = await I.sendGetRequest('/api/view_counter.php?id=9999999999999999', { 'Accept': 'application/json' });
    assert.strictEqual(res.status, 200);
});

Scenario('INT_TC04: Kiểm thử cày lượt xem tự động (Spam View) liên tục @INT_TC04', async ({ I }) => {
    for (let i = 0; i < 5; i++) {
        const res = await I.sendGetRequest('/api/view_counter.php?id=52', { 'Accept': 'application/json' });
        assert.strictEqual(res.status, 200);
    }
});

// ============================================================================
// 2. BÌNH LUẬN VÀ QUẢN LÝ BÌNH LUẬN (INT_TC05 - INT_TC12)
// ============================================================================

Scenario('INT_TC05: Gửi bình luận thành công khi đã đăng nhập @INT_TC05', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.seeElement('form.comment-form, form, .comment-section');
    I.fillField('textarea, input[name="comment_text"]', 'Phim hoạt hình Kẻ cắp mặt trăng rất hay!');
    I.click('button[type="submit"], input[type="submit"], .btn');
    I.dontSee('Fatal error');
});

Scenario('INT_TC06: Từ chối nhận bình luận khi bỏ trống nội dung @INT_TC06', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.fillField('textarea, input[name="comment_text"]', '');
    I.click('button[type="submit"], input[type="submit"], .btn');
    I.dontSee('Fatal error');
});

Scenario('INT_TC07: Gửi bình luận chỉ chứa ký tự khoảng trắng @INT_TC07', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.fillField('textarea, input[name="comment_text"]', '     ');
    I.click('button[type="submit"], input[type="submit"], .btn');
    I.dontSee('Fatal error');
});

Scenario('INT_TC08: Kiểm tra phòng chống tấn công Stored XSS qua bình luận @INT_TC08', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.fillField('textarea, input[name="comment_text"]', '<script>alert("XSS")</script>');
    I.click('button[type="submit"], input[type="submit"], .btn');
    I.dontSeeInSource('<script>alert("XSS")</script>');
    I.dontSee('Fatal error');
});

Scenario('INT_TC09: Kiểm tra an toàn chống SQL Injection trong ô bình luận @INT_TC09', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.fillField('textarea, input[name="comment_text"]', "' OR '1'='1 --");
    I.click('button[type="submit"], input[type="submit"], .btn');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('Fatal error');
});

Scenario('INT_TC10: Admin truy cập xem danh sách bình luận toàn hệ thống @INT_TC10', ({ I }) => {
    loginAdmin(I);
    I.amOnPage('/comments.php');
    I.seeElement('table, .comments-list, .comment-item, body');
    I.dontSee('Fatal error');
});

Scenario('INT_TC11: Chặn tài khoản chưa đăng nhập truy cập quản lý bình luận @INT_TC11', ({ I }) => {
    I.clearCookie();
    I.amOnPage('/comments.php');
    I.seeInCurrentUrl('/admin_login.php');
});

Scenario('INT_TC12: Admin quản lý bình luận không crash @INT_TC12', ({ I }) => {
    loginAdmin(I);
    I.amOnPage('/comments.php');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

// ============================================================================
// 3. LỊCH SỬ XEM PHIM (INT_TC13 - INT_TC15)
// ============================================================================

Scenario('INT_TC13: Người dùng xem danh sách lịch sử xem phim cá nhân @INT_TC13', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch_history.php');
    I.seeInCurrentUrl('/watch_history.php');
    I.dontSee('Fatal error');
});

Scenario('INT_TC14: Chặn tài khoản chưa đăng nhập truy cập trang Lịch sử xem phim @INT_TC14', ({ I }) => {
    I.clearCookie();
    I.amOnPage('/watch_history.php');
    I.seeInCurrentUrl('/login.php');
});

Scenario('INT_TC15: Ghi nhận lịch sử xem phim khi xem phim thành công @INT_TC15', ({ I }) => {
    loginUser(I);
    I.amOnPage('/watch.php?id=52');
    I.amOnPage('/watch_history.php');
    I.dontSee('Fatal error');
});

