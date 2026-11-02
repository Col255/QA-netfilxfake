<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NetfixVN - Thông tin về dịch vụ xem phim và series trực tuyến.">
    <meta name="keywords" content="giới thiệu, NetfixVN, phim, series">
    <title>Giới thiệu - NetfixVN</title>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- BOX ICONS -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <!-- APP CSS -->
    <link rel="stylesheet" href="iqiyi-style.css">
    <style>
    .section
        {
            padding: 120px 0;
            background:black;
            padding-left:30px; 
            padding-right: 30px;
        }
    </style>
</head>
<body>
   
<?php include 'header.php' ?>

    <!-- MAIN CONTENT -->
    <div class="section">
        <div class="container-about"  >
            <div class="section-header" >
                Giới thiệu về NetfixVN
            </div>
            <div class="about-content">
                <p>NetfixVN là nền tảng streaming trực tuyến hàng đầu tại Việt Nam, mang đến cho bạn kho tàng phim, series và hoạt hình phong phú với chất lượng cao. Chúng tôi cam kết cung cấp trải nghiệm giải trí không giới hạn với các gói cước phù hợp cho mọi nhu cầu.</p>
                <p>Đội ngũ của chúng tôi luôn nỗ lực cập nhật nội dung mới mỗi ngày, đảm bảo bạn không bỏ lỡ bất kỳ bộ phim bom tấn hay series hấp dẫn nào. Hãy đăng ký ngay để khám phá thế giới giải trí cùng NetfixVN!</p>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTENT -->

    <?php include 'footer.php' ?>


    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="app.js"></script>
</body>
</html>