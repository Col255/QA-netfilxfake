Feature('KAN-33: Kiểm thử RESTful API Ghi nhận Bộ đếm Lượt xem Phim (/api/view_counter.php)');

Scenario('KAN-33-TC01: Gọi API view counter với ID hợp lệ trả về JSON success', ({ I }) => {
    // 1. Gửi request tới API với ID phim hợp lệ
    I.amOnPage('/api/view_counter.php?id=52');

    // 2. Xác thực cấu trúc JSON trả về
    I.see('"success":true');
    I.see('"movie_id":52');
    I.dontSee('Fatal error');
    I.dontSee('Warning:');
});

Scenario('KAN-33-TC02: Gọi API view counter với ID không hợp lệ (ID = 0 hoặc chuỗi ký tự)', ({ I }) => {
    // 1. ID = 0
    I.amOnPage('/api/view_counter.php?id=0');
    I.see('"success":false');
    I.see('"error"');

    // 2. ID dạng text injection
    I.amOnPage('/api/view_counter.php?id=invalid_text_id');
    I.see('"success":false');
    I.see('"error"');
    I.dontSee('syntax error');
    I.dontSee('SQLSTATE');
});
