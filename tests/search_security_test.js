Feature('KAN-25: Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php)');

Scenario('KAN-25-TC01: Tìm kiếm phim với từ khóa chính xác & Kiểm tra liên kết xem phim', ({ I }) => {
    // 1. Gửi request tìm kiếm phim Doraemon
    I.amOnPage('/search.php?query=Doraemon');

    // 2. Xác thực tiêu đề kết quả tìm kiếm
    I.see('Kết quả tìm kiếm cho: "Doraemon"');

    // 3. Kiểm tra danh sách hiển thị thẻ phim khớp với từ khóa
    I.seeElement('.movie-item');
    I.see('Doraemon chú mèo máy thông minh');

    // 4. Kiểm tra liên kết điều hướng xem phim
    I.seeElement('a[href*="watch.php?id="]');
});

Scenario('KAN-25-TC02: Tìm kiếm không phân biệt chữ hoa, chữ thường (Case-Insensitive)', ({ I }) => {
    // Tìm kiếm với từ khóa chữ in hoa POKEMON
    I.amOnPage('/search.php?query=POKEMON');
    I.see('Kết quả tìm kiếm cho: "POKEMON"');
    I.seeElement('.movie-item');
    I.see('Pokemon');
});

Scenario('KAN-25-TC03: Xử lý êm dịu khi tìm kiếm với khoảng trắng hoặc từ khóa không tồn tại', ({ I }) => {
    // 1. Tìm kiếm chuỗi khoảng trắng
    I.amOnPage('/search.php?query=%20%20');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');

    // 2. Tìm kiếm từ khóa không tồn tại
    I.amOnPage('/search.php?query=PhimKhongHeTonTai9999');
    I.see('Không tìm thấy phim phù hợp', '.no-content');
});

Scenario('KAN-25-TC04: Kiểm tra phòng chống tấn công Reflected XSS trên ô tìm kiếm', ({ I }) => {
    // Gửi payload XSS vào query parameter
    I.amOnPage('/search.php?query=%3Cscript%3Ealert(%22XSS%22)%3C%2Fscript%3E');
    
    // Đảm bảo không render thẻ script thô gây popup
    I.dontSeeInSource('<script>alert("XSS")</script>');
    I.dontSee('Fatal error');
});

Scenario('KAN-25-TC05: Kiểm tra phòng chống tấn công SQL Injection trên ô tìm kiếm', ({ I }) => {
    // Gửi payload SQLi vào query parameter
    I.amOnPage('/search.php?query=Doraemon%27%20OR%20%271%27=%271');

    // Đảm bảo không xuất hiện lỗi cú pháp MySQL
    I.dontSee('syntax error');
    I.dontSee('mysqli_query');
    I.dontSee('SQLSTATE');
    I.dontSee('Fatal error');
});
