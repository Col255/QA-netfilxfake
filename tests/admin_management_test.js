Feature('Admin Management CMS (ADM) Testing');

// KAN-47: Kiểm thử bảo mật thêm phim mới (/add_movie.php)
Scenario('[KAN-47] Bảo mật: Chặn truy cập trái phép khi chưa đăng nhập Admin (/add_movie.php)', ({ I }) => {
    I.amOnPage('/add_movie.php');
    I.dontSee('Fatal error');
    // Hệ thống tự động chuyển hướng về trang đăng nhập hoặc giữ ở admin_login.php
    I.seeInCurrentUrl('login.php');
});

Scenario('[KAN-47] Kiểm tra trang đăng nhập quản trị Admin', ({ I }) => {
    I.amOnPage('/admin_login.php');
    I.dontSee('Fatal error');
    I.dontSee('SQL syntax');
});

// KAN-48: Kiểm thử bảo mật danh sách phim (/manage_movies.php)
Scenario('[KAN-48] Bảo mật: Chặn truy cập danh sách quản lý phim khi chưa có phiên', ({ I }) => {
    I.amOnPage('/manage_movies.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

// KAN-49: Kiểm thử bảo mật đơn hàng VIP (/orders.php)
Scenario('[KAN-49] Bảo mật: Chặn truy cập đơn hàng VIP trái phép', ({ I }) => {
    I.amOnPage('/orders.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

// KAN-50: Kiểm thử bảo mật quản lý khách hàng (/customers.php)
Scenario('[KAN-50] Bảo mật: Chặn truy cập danh sách khách hàng trái phép', ({ I }) => {
    I.amOnPage('/customers.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});

// KAN-51: Kiểm thử bảo mật báo cáo thống kê (/statistics.php)
Scenario('[KAN-51] Bảo mật: Chặn truy cập báo cáo doanh thu thống kê trái phép', ({ I }) => {
    I.amOnPage('/statistics.php');
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('login.php');
});