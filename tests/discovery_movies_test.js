Feature('KAN-21: Kiểm thử API Khám phá Trang chủ & Lọc Danh mục Phim (/index.php, /movies.php)');

Scenario('KAN-21-TC01: Trang chủ /index.php hiển thị đầy đủ Top slider và các khối nội dung phim', ({ I }) => {
    // 1. Truy cập trang chủ Netfix2
    I.amOnPage('/index.php');

    // 2. Kiểm tra tiêu đề trang và header điều hướng
    I.seeInTitle('NetfixVN - Xem Phim Online');
    I.see('NetfixVN');

    // 3. Kiểm tra khối Top Movies Slider hiển thị phim nổi bật
    I.seeElement('#top-movies-slide');
    I.seeElement('.top-movie-item');

    // 4. Kiểm tra các phân loại danh mục phim chính
    I.see('Phim mới nhất');
    I.see('Series mới nhất');
    I.see('Hoạt hình mới nhất');

    // 5. Kiểm tra dữ liệu phim từ database xuất hiện trên giao diện
    I.seeElement('.movie-item');
    I.dontSee('Fatal error');
    I.dontSee('Warning: mysqli');
});

Scenario('KAN-21-TC02: Lọc danh mục phim trên /movies.php theo thể loại (Genre: Cartoon, Series)', ({ I }) => {
    // 1. Kiểm tra lọc thể loại Hoạt hình (Cartoon)
    I.amOnPage('/movies.php?genre=Cartoon');
    I.seeInCurrentUrl('/movies.php?genre=Cartoon');
    I.see('Phim Cartoon', '.section-header');
    I.seeElement('.movie-item');
    I.see('Pokemon');

    // 2. Kiểm tra lọc thể loại Phim bộ (Series)
    I.amOnPage('/movies.php?genre=Series');
    I.seeInCurrentUrl('/movies.php?genre=Series');
    I.see('Phim Series', '.section-header');
    I.seeElement('.movie-item');
    I.see('Vương Chiêu Quân');
});

Scenario('KAN-21-TC03: Lọc danh mục phim trên /movies.php theo quốc gia (Country: Japan, USA)', ({ I }) => {
    // 1. Kiểm tra lọc phim theo quốc gia Nhật Bản (Japan)
    I.amOnPage('/movies.php?country=Japan');
    I.seeInCurrentUrl('/movies.php?country=Japan');
    I.see('Phim từ Japan', '.section-header');
    I.seeElement('.movie-item');
    I.see('Pokemon');

    // 2. Kiểm tra lọc phim theo quốc gia Mỹ (USA)
    I.amOnPage('/movies.php?country=USA');
    I.seeInCurrentUrl('/movies.php?country=USA');
    I.see('Phim từ USA', '.section-header');
    I.seeElement('.movie-item');
    I.see('Kẻ cắp mặt trăng');
});

Scenario('KAN-21-TC04: Xử lý êm dịu khi lọc danh mục không có dữ liệu (Negative)', ({ I }) => {
    // Truy cập thể loại không tồn tại trong CSDL
    I.amOnPage('/movies.php?genre=NonExistentGenre123');
    I.seeInCurrentUrl('/movies.php?genre=NonExistentGenre123');
    I.see('Phim NonExistentGenre123', '.section-header');
    
    // Kiểm tra hiển thị thông báo rỗng thân thiện, không bị lỗi PHP/SQL
    I.see('Không có phim trong danh mục này.', '.no-content');
    I.dontSee('Fatal error');
    I.dontSee('SQLSTATE');
});
