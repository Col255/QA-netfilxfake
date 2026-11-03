Feature('Movies and Search (MOV) Testing');

// MOV-01: Lọc danh sách phim theo thể loại
Scenario('[MOV-01] Lọc danh sách phim theo thể loại Hành Động', ({ I }) => {
    I.amOnPage('/movies.php?genre=Hành Động');
    I.see('Hành Động');
    I.dontSee('Fatal error');
});

// MOV-02: Tìm kiếm phim theo từ khóa
Scenario('[MOV-02] Tìm kiếm phim với từ khóa hợp lệ (Doremon)', ({ I }) => {
    I.amOnPage('/search.php?query=Doremon');
    // Kiểm tra trang tải không lỗi và hiển thị giao diện tìm kiếm / kết quả
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('/search.php');
});

Scenario('[MOV-02] Tìm kiếm an toàn với ký tự đặc biệt SQL Injection', ({ I }) => {
    I.amOnPage("/search.php?query=' OR 1=1 --");
    I.dontSee('SQL syntax');
    I.dontSee('mysql_fetch');
});

// MOV-03: Xem phim và chi tiết phim
Scenario('[MOV-03] Truy cập trang xem chi tiết tập phim (/watch.php)', ({ I }) => {
    I.amOnPage('/watch.php?id=1&episode=1');
    // Kiểm tra trang xem phim tải thành công, không gặp lỗi hệ thống 500
    I.dontSee('Fatal error');
    I.seeInCurrentUrl('/watch.php');
});

// MOV-04: Trang chủ Netfix
Scenario('[MOV-04] Tải trang chủ Netfix hiển thị thương hiệu và danh mục', ({ I }) => {
    I.amOnPage('/index.php');
    I.see('Netfix');
});