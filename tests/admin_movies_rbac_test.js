Feature('KAN-37: Kiểm thử Phân quyền Quản trị & CRUD Nội dung Phim Admin (/add_movie.php, /manage_movies.php)');

Scenario('KAN-37-TC01: Khách vãng lai truy cập /add_movie.php bị chặn và điều hướng về trang đăng nhập Admin', ({ I }) => {
    // 1. Thử truy cập thẳng trang thêm phim khi chưa đăng nhập quyền admin
    I.amOnPage('/add_movie.php');

    // 2. Xác thực hệ thống chuyển hướng về admin_login.php
    I.seeInCurrentUrl('admin_login.php');
    I.dontSee('Fatal error');
});

Scenario('KAN-37-TC02: Khách vãng lai truy cập /manage_movies.php bị chặn và điều hướng về trang đăng nhập Admin', ({ I }) => {
    // 1. Thử truy cập trang quản lý phim
    I.amOnPage('/manage_movies.php');

    // 2. Xác thực hệ thống chuyển hướng về admin_login.php
    I.seeInCurrentUrl('admin_login.php');
    I.dontSee('Fatal error');
});

Scenario('KAN-37-TC03: Xác thực trang đăng nhập Quản trị viên (admin_login.php) hoạt động bình thường', ({ I }) => {
    // 1. Mở trang đăng nhập admin
    I.amOnPage('/admin_login.php');

    // 2. Kiểm tra các trường biểu mẫu đăng nhập
    I.seeElement('form');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});
