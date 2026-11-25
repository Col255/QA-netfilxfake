const assert = require('assert');

Feature('Phân hệ 2: Movies & Discovery (MOV) — Kiểm thử Hộp đen Black-box');

// ============================================================================
// 1. DISCOVERY & FILTER (MOV_TC01 - MOV_TC04)
// ============================================================================

Scenario('MOV_TC01: Khám phá trang chủ thành công, hiển thị đầy đủ danh mục @MOV_TC01', ({ I }) => {
    I.amOnPage('/index.php');
    I.seeElement('.nav-wrapper, .nav, .logo, .nav-menu');
    I.seeElement('.movie-item, .movie-card, .slider, .carousel, .hero, body');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
    I.dontSee('Notice:');
});

Scenario('MOV_TC02: Lấy toàn bộ danh sách phim mặc định tại movies.php @MOV_TC02', ({ I }) => {
    I.amOnPage('/movies.php');
    I.seeElement('.movie-item, .movie-card, .film-item');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('MOV_TC03: Lọc phim hợp lệ theo Thể loại và Quốc gia @MOV_TC03', ({ I }) => {
    I.amOnPage('/movies.php?genre=Cartoon&country=Japan');
    I.seeElement('.movie-item, .movie-card, .film-item, body');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('MOV_TC04: Lọc phim với thể loại không tồn tại @MOV_TC04', ({ I }) => {
    I.amOnPage('/movies.php?genre=UnknownGenre123');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
    I.dontSee('syntax error');
});

// ============================================================================
// 2. SEARCH & SECURITY (MOV_TC05 - MOV_TC09)
// ============================================================================

Scenario('MOV_TC05: Tìm kiếm phim với từ khóa chính xác @MOV_TC05', ({ I }) => {
    I.amOnPage('/search.php?query=Doraemon');
    I.dontSee('Fatal error');
    I.seeElement('.movie-item, .movie-card, .no-content, body');
    I.see('Doraemon');
});

Scenario('MOV_TC06: Tìm kiếm không phân biệt chữ hoa thường (Case-Insensitive) @MOV_TC06', ({ I }) => {
    I.amOnPage('/search.php?query=POKEMON');
    I.dontSee('Fatal error');
    I.see('Pokemon');
});

Scenario('MOV_TC07: Tìm kiếm với chuỗi khoảng trắng hoặc rỗng @MOV_TC07', ({ I }) => {
    I.amOnPage('/search.php?query=%20%20');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('MOV_TC08: Kiểm tra phòng chống tấn công Reflected XSS trên ô tìm kiếm @MOV_TC08', ({ I }) => {
    I.amOnPage('/search.php?query=%3Cscript%3Ealert(%22XSS%22)%3C%2Fscript%3E');
    I.dontSeeInSource('<script>alert("XSS")</script>');
    I.dontSee('Fatal error');
});

Scenario('MOV_TC09: Kiểm tra an toàn chống SQL Injection trên ô tìm kiếm @MOV_TC09', ({ I }) => {
    I.amOnPage('/search.php?query=Doraemon%27%20OR%20%271%27=%271');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('SQLSTATE');
    I.dontSee('Fatal error');
});

// ============================================================================
// 3. STREAMING & VIP GATEKEEPER (MOV_TC10 - MOV_TC15)
// ============================================================================

Scenario('MOV_TC10: Xem chi tiết và phát luồng phim miễn phí thành công @MOV_TC10', ({ I }) => {
    I.amOnPage('/watch.php?id=52');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
    I.seeElement('.movie-details, .currently-watching, video, .player-container, body');
});

Scenario('MOV_TC11: Xem chi tiết phim bộ có danh sách tập phim @MOV_TC11', ({ I }) => {
    I.amOnPage('/watch.php?id=56');
    I.dontSee('Fatal error');
    I.seeElement('.movie-details, .episode-list, .episode-grid, .no-content, body');
});

Scenario('MOV_TC12: Xem phim với ID không tồn tại trong hệ thống @MOV_TC12', ({ I }) => {
    I.amOnPage('/watch.php?id=999999');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('MOV_TC13: Xem phim với ID chứa ký tự chữ hoặc SQL injection @MOV_TC13', ({ I }) => {
    I.amOnPage('/watch.php?id=invalid_id_injection');
    I.dontSee('Fatal error');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
});

Scenario('MOV_TC14: Chặn khách vãng lai xem phim VIP (Gatekeeper) @MOV_TC14', ({ I }) => {
    I.clearCookie();
    I.amOnPage('/watch.php?id=56');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('MOV_TC15: Đăng nhập tài khoản và truy cập trang xem phim @MOV_TC15', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('input[type="text"], input[type="email"], #identifier', 'tuan@gmail.com');
    I.fillField('input[type="password"], #password', '123456');
    I.click('button[type="submit"], input[type="submit"]');
    I.wait(1);
    I.amOnPage('/watch.php?id=52');
    I.dontSee('Fatal error');
});

// ============================================================================
// 4. API & ADMIN RBAC (MOV_TC16 - MOV_TC18)
// ============================================================================

Scenario('MOV_TC16: Gọi RESTful API tăng lượt xem phim thành công @MOV_TC16', async ({ I }) => {
    const res = await I.sendGetRequest('/api/view_counter.php?id=52');
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.strictEqual(res.data.movie_id, 52);
});

Scenario('MOV_TC17: Gọi API tăng lượt xem với ID không hợp lệ @MOV_TC17', async ({ I }) => {
    const res = await I.sendGetRequest('/api/view_counter.php?id=0');
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, false);
});

Scenario('MOV_TC18: Chặn tài khoản người dùng thường truy cập trang thêm phim @MOV_TC18', ({ I }) => {
    I.amOnPage('/login.php');
    I.fillField('input[type="text"], input[type="email"], #identifier', 'tuan@gmail.com');
    I.fillField('input[type="password"], #password', '123456');
    I.click('button[type="submit"], input[type="submit"]');
    I.wait(1);
    I.amOnPage('/add_movie.php');
    // Người dùng thường (is_admin = 0) bị đẩy về login.php hoặc index.php
    I.dontSeeInCurrentUrl('/add_movie.php');
});
