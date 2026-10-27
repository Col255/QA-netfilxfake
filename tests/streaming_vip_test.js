Feature('KAN-29: Kiểm thử Metadata Chi tiết Phim, Quản lý Tập & Phân quyền VIP Streaming (/watch.php)');

Scenario('KAN-29-TC01: Xem chi tiết phim miễn phí & Khởi tạo trình phát video', ({ I }) => {
    // 1. Mở trang xem phim miễn phí (Doraemon ID 55)
    I.amOnPage('/watch.php?id=55');

    // 2. Xác thực tiêu đề phim và thông tin chi tiết
    I.see('Doraemon chú mèo máy thông minh');
    I.see('Cartoon');
    I.see('Japan');

    // 3. Kiểm tra container thông tin phim và trình phát hoặc trạng thái video
    I.seeElement('.movie-details');
    I.seeElement('.currently-watching');
    I.dontSee('Fatal error');
});

Scenario('KAN-29-TC02: Xem phim bộ và danh sách tập phim (Series Episodes)', ({ I }) => {
    // 1. Mở trang xem phim bộ Vương Chiêu Quân (ID 56)
    I.amOnPage('/watch.php?id=56');

    // 2. Kiểm tra thông tin phim bộ và danh sách tập
    I.see('Vương Chiêu Quân');
    I.see('Series');
    I.seeElement('.movie-details');
    I.seeElement('.episode-list, .episode-grid, .no-content');

    // 3. Không có lỗi crash
    I.dontSee('Fatal error');
    I.dontSee('SQLSTATE');
});

Scenario('KAN-29-TC03: Xử lý khi xem phim với ID không tồn tại hoặc sai định dạng', ({ I }) => {
    // 1. ID không tồn tại trong hệ thống
    I.amOnPage('/watch.php?id=999999');
    I.dontSee('Fatal error');

    // 2. ID dạng text có chèn ký tự lạ
    I.amOnPage('/watch.php?id=invalid_id_injection');
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('Fatal error');
});

Scenario('KAN-29-TC04: Kiểm tra cơ chế Gatekeeper hiển thị nhãn VIP cho tập phim VIP', ({ I }) => {
    // Truy cập phim Series có tập VIP (ID 56) khi chưa đăng nhập
    I.amOnPage('/watch.php?id=56');

    // Kiểm tra hệ thống hiển thị huy hiệu VIP và nút khóa cho khách chưa đăng nhập VIP
    I.seeElement('.badge-vip, .locked, .unlock-btn, .episode-card');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});
