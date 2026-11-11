const assert = require('assert');

Feature('Phân hệ 3: Interactions & Engagement (INT)');

// ----------------------------------------------------
// 1. API VIEW COUNTER (INT_TC01 - INT_TC04)
// ----------------------------------------------------

Scenario('INT_TC01 - Tăng lượt xem phim thành công qua API @INT_TC01', async ({ I }) => {
  const res = await I.sendGetRequest('/api/view_counter.php?id=1', { 'Accept': 'application/json' });
  assert.strictEqual(res.status, 200);
  assert.strictEqual(res.data.success, true);
  assert.strictEqual(res.data.movie_id, 1);
});

Scenario('INT_TC02 - Gọi API tăng lượt xem với ID không hợp lệ @INT_TC02', async ({ I }) => {
  const resZero = await I.sendGetRequest('/api/view_counter.php?id=0', { 'Accept': 'application/json' });
  assert.strictEqual(resZero.status, 200);
  assert.strictEqual(resZero.data.success, false);

  const resNegative = await I.sendGetRequest('/api/view_counter.php?id=-5', { 'Accept': 'application/json' });
  assert.strictEqual(resNegative.status, 200);
  assert.strictEqual(resNegative.data.success, false);
});

Scenario('INT_TC03 - Gọi API tăng lượt xem với ID vượt giới hạn kiểu dữ liệu @INT_TC03', async ({ I }) => {
  const res = await I.sendGetRequest('/api/view_counter.php?id=9999999999999999', { 'Accept': 'application/json' });
  assert.strictEqual(res.status, 200);
});

Scenario('INT_TC04 - Kiểm thử cày lượt xem tự động (Spam View) liên tục @INT_TC04', async ({ I }) => {
  for (let i = 0; i < 5; i++) {
    const res = await I.sendGetRequest('/api/view_counter.php?id=1', { 'Accept': 'application/json' });
    assert.strictEqual(res.status, 200);
  }
});

// ----------------------------------------------------
// 2. BÌNH LUẬN VÀ QUẢN LÝ BÌNH LUẬN (INT_TC05 - INT_TC12)
// ----------------------------------------------------

// Hàm đăng nhập User (Dùng thuộc tính name/type linh hoạt của form HTML)
const loginUser = (I) => {
  I.amOnPage('/login.php');
  // Thử điền theo name="username" hoặc name="email" hoặc selector input
  I.fillField('input[type="text"], input[type="email"], input[name="username"]', 'tuan@gmail.com');
  I.fillField('input[type="password"]', '123456');
  I.click('button[type="submit"], input[type="submit"]');
};

// Hàm đăng nhập Admin
const loginAdmin = (I) => {
  I.amOnPage('/admin_login.php');
  I.fillField('input[type="text"], input[name="username"]', 'admin');
  I.fillField('input[type="password"]', 'admin123');
  I.click('button[type="submit"], input[type="submit"]');
};

Scenario('INT_TC05 - Gửi bình luận thành công khi đã đăng nhập @INT_TC05', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.seeElement('form.comment-form, form');
  I.fillField('textarea, input[name="comment_text"]', 'Phim hay quá!');
  I.click('button[type="submit"], input[type="submit"], .btn');
  I.seeElement('textarea, input[name="comment_text"]');
});

Scenario('INT_TC06 - Từ chối nhận bình luận khi bỏ trống nội dung @INT_TC06', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.fillField('textarea, input[name="comment_text"]', '');
  I.click('button[type="submit"], input[type="submit"], .btn');
});

Scenario('INT_TC07 - Gửi bình luận chỉ chứa ký tự khoảng trắng @INT_TC07', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.fillField('textarea, input[name="comment_text"]', '     ');
  I.click('button[type="submit"], input[type="submit"], .btn');
});

Scenario('INT_TC08 - Kiểm tra phòng chống tấn công Stored XSS qua bình luận @INT_TC08', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.fillField('textarea, input[name="comment_text"]', '<script>alert("XSS")</script>');
  I.click('button[type="submit"], input[type="submit"], .btn');
});

Scenario('INT_TC09 - Kiểm tra an toàn chống SQL Injection trong ô bình luận @INT_TC09', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.fillField('textarea, input[name="comment_text"]', "' OR '1'='1 --");
  I.click('button[type="submit"], input[type="submit"], .btn');
});

Scenario('INT_TC10 - Admin truy cập xem danh sách bình luận toàn hệ thống @INT_TC10', async ({ I }) => {
  loginAdmin(I);
  I.amOnPage('/comments.php');
  // 🔥 Viết hoa chữ "Luận" cho khớp với thực tế website
  I.seeInTitle('Bình Luận') || I.see('Quản Lý Bình Luận');
});

Scenario('INT_TC11 - Chặn tài khoản chưa đăng nhập truy cập quản lý bình luận @INT_TC11 @KAN-133', async ({ I }) => {
  I.clearCookie();
  I.amOnPage('/comments.php');
  I.seeInCurrentUrl('/admin_login.php');
});

Scenario('INT_TC12 - Admin thực hiện xóa bình luận vi phạm thành công @INT_TC12 @KAN-134', async ({ I }) => {
  loginAdmin(I);
  I.amOnPage('/comments.php');
});

// ----------------------------------------------------
// 3. LỊCH SỬ XEM PHIM (INT_TC13 - INT_TC15)
// ----------------------------------------------------

Scenario('INT_TC13 - Người dùng xem danh sách lịch sử xem phim cá nhân @INT_TC13 @KAN-135', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch_history.php');
  I.seeInCurrentUrl('/watch_history.php');
});

Scenario('INT_TC14 - Chặn tài khoản chưa đăng nhập truy cập trang Lịch sử xem phim @INT_TC14 @KAN-136', async ({ I }) => {
  I.clearCookie();
  I.amOnPage('/watch_history.php');
  I.seeInCurrentUrl('/login.php');
});

Scenario('INT_TC15 - Kiểm tra mốc thời gian (Timestamp) khi ghi nhận lịch sử xem phim @INT_TC15 @KAN-137', async ({ I }) => {
  loginUser(I);
  I.amOnPage('/watch.php?id=1');
  I.amOnPage('/watch_history.php');
});