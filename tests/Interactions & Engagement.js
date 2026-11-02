Feature('Phân hệ 3: Interactions & Engagement (INT)');

// ==========================================
// 1. API VIEW COUNTER (INT_TC01 - INT_TC04)
// ==========================================

Scenario('INT_TC01 - Tăng lượt xem phim thành công qua API @INT_TC01', async ({ I }) => {
  const res = await I.sendGetRequest('/api/view_counter.php?id=1', { 'Accept': 'application/json' });
  I.assertEqual(res.status, 200);
  I.assertEqual(res.data.success, true);
  I.assertEqual(res.data.movie_id, 1);
});

Scenario('INT_TC02 - Gọi API tăng lượt xem với ID không hợp lệ @INT_TC02', async ({ I }) => {
  // Kiểm thử với ID = 0
  const resZero = await I.sendGetRequest('/api/view_counter.php?id=0', { 'Accept': 'application/json' });
  I.assertEqual(resZero.status, 200);
  I.assertEqual(resZero.data.success, false);

  // Kiểm thử với ID số âm
  const resNegative = await I.sendGetRequest('/api/view_counter.php?id=-5', { 'Accept': 'application/json' });
  I.assertEqual(resNegative.status, 200);
  I.assertEqual(resNegative.data.success, false);
});

Scenario('INT_TC03 - Gọi API tăng lượt xem với ID vượt giới hạn kiểu dữ liệu @INT_TC03', async ({ I }) => {
  const res = await I.sendGetRequest('/api/view_counter.php?id=9999999999999999');
  I.assertTrue(res.status === 200 || res.status === 400);
  I.assertNotEqual(res.status, 500, 'Server không được văng lỗi 500 Fatal Error');
});

Scenario('INT_TC04 - Kiểm thử cày lượt xem tự động (Spam View) liên tục @INT_TC04', async ({ I }) => {
  const loopCount = 20;
  for (let i = 1; i <= loopCount; i++) {
    const res = await I.sendGetRequest('/api/view_counter.php?id=1');
    I.assertEqual(res.status, 200);
  }
});

// ==========================================
// 2. BÌNH LUẬN TRÊN WATCH.PHP (INT_TC05 - INT_TC09)
// ==========================================

Scenario('INT_TC05 - Gửi bình luận thành công khi đã đăng nhập @INT_TC05', async ({ I }) => {
  I.amOnPage('/watch.php?id=1');
  I.seeElement('form.comment-form');
  I.fillField('comment_text', 'Phim hay quá, rất xuất sắc!');
  I.click('Gửi bình luận');
  I.see('Phim hay quá, rất xuất sắc!', '.comment-list');
});

Scenario('INT_TC06 - Từ chối nhận bình luận khi bỏ trống nội dung @INT_TC06', async ({ I }) => {
  I.amOnPage('/watch.php?id=1');
  I.fillField('comment_text', '');
  I.click('Gửi bình luận');
  I.dontSeeElement('.comment-item-empty');
  I.seeInCurrentUrl('/watch.php?id=1');
});

Scenario('INT_TC07 - Gửi bình luận chỉ chứa ký tự khoảng trắng @INT_TC07', async ({ I }) => {
  I.amOnPage('/watch.php?id=1');
  I.fillField('comment_text', '     ');
  I.click('Gửi bình luận');
  I.dontSee('     ', '.comment-item');
});

Scenario('INT_TC08 - Kiểm tra phòng chống tấn công Stored XSS qua bình luận @INT_TC08', async ({ I }) => {
  const xssPayload = '<script>alert("XSS")</script>';
  I.amOnPage('/watch.php?id=1');
  I.fillField('comment_text', xssPayload);
  I.click('Gửi bình luận');
  I.dontSeeInSource(xssPayload); // Script không được nằm trong HTML nguồn dạng thô
});

Scenario('INT_TC09 - Kiểm tra an toàn chống SQL Injection trong ô bình luận @INT_TC09', async ({ I }) => {
  const sqliPayload = "' OR '1'='1 --";
  I.amOnPage('/watch.php?id=1');
  I.fillField('comment_text', sqliPayload);
  I.click('Gửi bình luận');
  I.see(sqliPayload, '.comment-list'); // Lưu dạng văn bản thuần, không văng lỗi CSDL
});

// ==========================================
// 3. QUẢN LÝ BÌNH LUẬN ADMIN (INT_TC10 - INT_TC12)
// ==========================================

Scenario('INT_TC10 - Admin truy cập xem danh sách bình luận toàn hệ thống @INT_TC10', async ({ I }) => {
  I.amOnPage('/comments.php');
  I.see('Quản lý bình luận');
  I.seeElement('table.comments-table');
  I.seeElement('.comment-user-name');
  I.seeElement('.comment-movie-title');
});

Scenario('INT_TC11 - Chặn tài khoản chưa đăng nhập truy cập quản lý bình luận @INT_TC11', async ({ I }) => {
  I.clearCookie(); // Xóa Session/Cookie
  I.amOnPage('/comments.php');
  I.seeInCurrentUrl('/admin_login.php');
});

Scenario('INT_TC12 - Admin thực hiện xóa bình luận vi phạm thành công @INT_TC12', async ({ I }) => {
  I.amOnPage('/comments.php');
  I.seeElement('#comment-row-2');
  I.click('#delete-btn-2');
  I.see('Xóa bình luận thành công');
  I.dontSeeElement('#comment-row-2');
});

// ==========================================
// 4. LỊCH SỬ XEM PHIM (INT_TC13 - INT_TC15)
// ==========================================

Scenario('INT_TC13 - Người dùng xem danh sách lịch sử xem phim cá nhân @INT_TC13', async ({ I }) => {
  I.amOnPage('/watch_history.php');
  I.see('Lịch sử xem phim');
  I.seeElement('.history-list-item');
});

Scenario('INT_TC14 - Chặn tài khoản chưa đăng nhập truy cập trang Lịch sử xem phim @INT_TC14', async ({ I }) => {
  I.clearCookie();
  I.amOnPage('/watch_history.php');
  I.seeInCurrentUrl('/login.php');
});

Scenario('INT_TC15 - Kiểm tra mốc thời gian (Timestamp) khi ghi nhận lịch sử xem phim @INT_TC15', async ({ I }) => {
  I.amOnPage('/watch.php?id=1'); // Tạo lịch sử mới
  I.amOnPage('/watch_history.php');
  I.seeElement('.history-timestamp');
  const timeText = await I.grabTextFrom('.history-timestamp:first-child');
  I.assertRegExp(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/, timeText); // Định dạng YYYY-MM-DD HH:mm:ss
});