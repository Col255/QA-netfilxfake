-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 07:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `netfixvn`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `movie_id`, `user_id`, `comment_text`, `created_at`) VALUES
(2, 68, 22, 'hay nha', '2025-06-29 05:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

CREATE TABLE `episodes` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `episode_number` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) NOT NULL,
  `is_vip` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `episodes`
--

INSERT INTO `episodes` (`id`, `movie_id`, `episode_number`, `title`, `video_path`, `is_vip`, `created_at`, `updated_at`) VALUES
(23, 56, 50, 'Vương chiêu quân tập 1', 'Uploads/videos/1749378628_Vuongchieuquan.mp4', 0, '2025-06-08 10:17:06', '2025-06-08 10:30:28'),
(24, 56, 2, 'Vương chiêu quân tập 2', 'Uploads/videos/1749378723_vuongchieuquan_tap2.mp4', 0, '2025-06-08 10:32:03', '2025-06-08 10:32:03'),
(25, 57, 1, 'Khi anh chạy về phía em - tập 1', 'Uploads/videos/1749380358_Khianhchayvephiaem.mp4', 0, '2025-06-08 10:59:18', '2025-06-08 10:59:18'),
(26, 57, 2, 'Khi anh chạy về phía em- tập 2', 'Uploads/videos/1749380755_Khianhchayvephiaem_tap2.mp4', 1, '2025-06-08 11:05:55', '2025-06-29 02:58:33'),
(27, 58, 1, 'Hậu dụa mặt trời- tập 1', 'Uploads/videos/1749381714_hauduemattroi_tap1.mp4', 0, '2025-06-08 11:21:54', '2025-06-08 11:21:54'),
(28, 58, 2, 'Hậu duệ mặt trời- tập 2', 'Uploads/videos/1749381912_hauduemattroi_tap2.mp4', 1, '2025-06-08 11:25:12', '2025-06-29 02:59:00'),
(29, 59, 1, 'Hẹn hò chốn công sở- tập 1', 'Uploads/videos/1749382750_henhochoncongso_tap1.mp4', 0, '2025-06-08 11:39:10', '2025-06-08 11:39:10'),
(30, 59, 2, 'Hẹn hò chốn công sở- tập 2', 'Uploads/videos/1749382829_henhonoicongso_tap2.mp4', 0, '2025-06-08 11:40:29', '2025-06-08 11:40:29'),
(31, 68, 1, 'tập 1', 'Uploads/videos/1751170462_videoplayback.mp4', 1, '2025-06-29 04:14:22', '2025-06-29 04:14:22'),
(32, 68, 2, 'tập 2', 'Uploads/videos/1751170462_videoplayback.mp4', 1, '2025-06-29 04:14:22', '2025-06-29 04:14:22'),
(33, 68, 3, 'tập 3', 'Uploads/videos/1751170462_videoplayback.mp4', 0, '2025-06-29 04:14:22', '2025-06-29 04:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `episode_count` int(11) DEFAULT NULL,
  `genre` enum('Movie','Series','Cartoon','Cinema') NOT NULL,
  `sub_genre` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `actors` text DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `video_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_vip` tinyint(4) DEFAULT 0,
  `view_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `thumbnail`, `description`, `duration`, `episode_count`, `genre`, `sub_genre`, `year`, `language`, `country`, `actors`, `director`, `status`, `video_path`, `created_at`, `updated_at`, `is_vip`, `view_count`) VALUES
(52, 'Kẻ cắp mặt trăng', 'Uploads/thumbnails/1751132776_kecapmawttrang.jpg', 'Là một bộ phim hay hài hước', 3, 0, 'Cartoon', 'Hài hước', 2017, 'English', 'USA', 'Steve Carell, Jason Segel, Russell Brand, Julie Andrews, Will Arnett, Kristen Wiig, và Miranda Cosgrove.', ' Illumination Entertainment.', 'active', 'Uploads/videos/1749374299_ke_cap_mat_trang.mp4', '2025-06-08 09:18:19', '2025-06-28 17:56:02', 0, 0),
(53, 'Pokemon', 'Uploads/thumbnails/1751132735_pokemon.jpg', 'Là một bộ phim hay với nhiều tình tiết hấp dẫn', 3, 0, 'Cartoon', 'Vui nhộn, hài hước, kịch tính', 2016, 'English', 'Japan', 'Megumi Hayashibara, Rachael Lillis', ' Atsuhiro Tomioka ', 'active', 'Uploads/videos/1749375024_pokemon.mp4', '2025-06-08 09:30:24', '2025-06-28 17:55:49', 0, 0),
(54, 'Tom and  Jerry', 'Uploads/thumbnails/1751132696_tom and jerry.jpg', 'Là một bộ phim hay hài hước vui nhộn', 2, 0, 'Cartoon', 'Hài hước', 1941, 'English', 'USA', 'Gồm nhiều diễn viên lồng tiếng thực hiện', ' William Hanna và Joseph Barbera ', 'active', 'Uploads/videos/1749375610_Tom_and_jerry.mp4', '2025-06-08 09:40:10', '2025-06-28 17:56:07', 0, 0),
(55, 'Doraemon chú mèo máy thông minh', 'Uploads/thumbnails/1751132755_doreamon.jpg', 'Là một bộ phim hay vui nhộn hài hước', 4, 0, 'Cartoon', 'Vui nhộn, hài hước', 20, 'Japan', 'Japan', ' Sakamoto Makoto,i Suho Reiko', 'Kaminashi Mitsuo', 'active', 'Uploads/videos/1749376753_Doreamon.mp4', '2025-06-08 09:59:13', '2025-06-28 17:56:19', 0, 0),
(56, 'Vương Chiêu Quân', 'Uploads/thumbnails/1751159218_z6752759840336_03525c154ef81346b287f1cd462fdcda.jpg', 'Là một bộ tâm lý tình cảm', 15, 50, 'Series', 'Tình cảm, cổ trang', 2005, 'China', 'China', 'Dương Mịch, Trần Tư Thành,Lưu Hiểu Khánh, Phan Hồng, Ông Hồng, Tống Xuân Lệ', 'Trần Gia Lâm', 'active', '', '2025-06-08 10:17:06', '2025-06-29 01:06:58', 0, 0),
(57, 'Khi anh chạy về phía em', 'Uploads/thumbnails/1751159661_khianhchayvephiaem.jpg', 'Là một bộ phim tình cảm hay của giới trẻ', 12, 20, 'Series', 'Tình cảm,Thanh xuân', 2023, 'China', 'China', 'Chu Dực Nhiên, Trương Miểu Di, Biên Thiên Dương', 'Miêu Đích Thụ', 'active', '', '2025-06-08 10:59:18', '2025-06-29 02:58:33', 0, 19),
(58, 'Hậu duệ mặt trời', 'Uploads/thumbnails/1751132675_hauduemattroi2.jpg', 'Là một bộ phim tình cảm hay nhập tâm', 12, 30, 'Series', 'Tình cảm', 2016, 'Korea', 'Korea', 'Song Hye-kyo, Song Joong-ki, Kim Ji-won và Jin Goo...', 'Lee Eung Bok', 'active', '', '2025-06-08 11:21:54', '2025-06-29 02:59:00', 0, 0),
(59, 'Hẹn hò chốn công sở', 'Uploads/thumbnails/1751132616_hẹn hóa chốn công sở.jpg', 'Là một bộ phim tình cảm hay ', 13, 30, 'Series', 'Tình cảm,hài hước', 2022, 'Korea', 'Korea', 'Ahn Hyo Seop, Kim Se Jeong, Kim Min Gue', 'Park Seon Ho', 'active', '', '2025-06-08 11:39:10', '2025-06-28 17:57:05', 0, 0),
(60, 'Dưới đáy hồ', 'Uploads/thumbnails/1751159200_z6752759844013_848dd41ecf9a291044fa684b5f20d663.jpg', 'Là một bộ phim kinh dị gây cấn hấp dẫn', 3, 0, 'Cinema', 'Kinh dị', 2025, 'Việt Nam', 'Việt Nam', 'Hữu Tiến, Karen Nguyễn, Kay Trần, Thanh Duy, Lâm Hoàng Oanh, Nguyên Thảo, Mạc Trung Kiên...', 'Trần Hữu Tấn và Hoàng Quân', 'active', 'Uploads/videos/1749383432_đuoiayho.mp4', '2025-06-08 11:50:32', '2025-06-29 01:06:40', 0, 0),
(61, 'Thám tử kiên', 'Uploads/thumbnails/1751159169_z6752759847610_1f811149f84e49bca7e8ff195031de27.jpg', 'Đây là bộ phim không chỉ gây tò mò bởi nội dung xoay quanh yếu tố trinh thám, kinh dị, tâm linh', 20, 0, 'Cinema', '', 2025, 'Việt Nam', 'Việt Nam', 'Quốc Huy, Đinh Ngọc, Mỹ, Quốc Cường, Anh Phạm....', 'Victor Vũ', 'active', 'Uploads/videos/1749523866_thamtukien.mp4', '2025-06-10 02:51:06', '2025-06-29 01:06:09', 0, 0),
(62, 'Quỷ nhập tràng', 'Uploads/thumbnails/1751159153_z6752759850970_dfc34d3d3c8247954fae94cfc52ef77c.jpg', '“Quỷ Nhập Tràng” là một bộ phim kinh dị Việt Nam mới ra mắt, thuộc Vũ Trụ Linh Dị Ngũ Hành, lấy cảm hứng từ hiện tượng dân gian cùng tên. Sẽ mang đến cho người xem một cảm giác kinh dị, hồi hộp.', 30, 0, 'Cinema', 'Kinh dị', 2025, 'Việt Nam', 'Việt Nam', 'Quang Tuấn, Khả Như, Hoàng Mèo......', 'Pom Nguyễn', 'active', 'Uploads/videos/1749524932_quynhaptrang.mp4', '2025-06-10 03:08:52', '2025-06-29 01:05:53', 0, 0),
(63, 'Đèn âm hồn', 'Uploads/thumbnails/1751159139_z6752759857183_9c8e038e6ec99e22b71e6b1d748fe752.jpg', 'Đèn âm hồn (tên đầy đủ là Đèn âm hồn: Người con gái Nam Xương)là một bộ phim điện ảnh Việt Nam thuộc thể loại kinh dị - tâm linh', 35, 0, 'Cinema', 'Kinh dị, tâm linh', 2025, 'Việt Nam', 'Việt Nam', 'SyNi Trang, Phú Thịnh, Hoàng Kim Ngọc, Tuấn Mõ, Đình Khang, Kiều Trinh và Hạo Khang.', 'Hoàng Nam', 'active', 'Uploads/videos/1749525148_denamhon.mp4', '2025-06-10 03:12:28', '2025-06-29 01:05:39', 0, 0),
(64, 'Under Paris (Phía Dưới Sông Seine)', 'Uploads/thumbnails/1751159117_z6752759863512_bf17fd512b092e074ed66f5e9132ffdf.jpg', 'Là bộ phim hành động kinh dị giật gân của điện ảnh Pháp năm 2024', 36, 0, 'Movie', 'Hành động, giật gân', 2024, ' Paris', ' Paris', 'Bérénice Bejo, Nassim Lyes, Léa Léviant', 'Xavier Gens', 'active', 'Uploads/videos/1749528136_phiaduoisongnise.mp4', '2025-06-10 04:02:16', '2025-06-29 01:05:17', 0, 0),
(65, 'The Roundup Punishment', 'Uploads/thumbnails/1751159083_z6752759867631_2982c6c2c19ffe3e24b1fbd3cd683d4a.jpg', 'Là tác phẩm thứ 4 của thương hiệu The Roundup - loạt phim hài, hành động xoay quanh hành trình truy bắt tội phạm của thanh tra Ma Seok Do do nam diễn viên Ma Dong Seok đóng chính.\r\n\r\n', 40, 0, 'Movie', 'Hành động,kịch tính', 2025, 'Korea', 'Korea', 'Ma Dong-seok, Kim Mu-yeol, Park Ji-hwan và Lee Dong-hwi.', 'Heo Myeong Haeng ', 'active', 'Uploads/videos/1749528408_Roundup-Punishment-Official-Trailer_Media_0phxk3pV6hI_002_720p.mp4', '2025-06-10 04:06:48', '2025-06-29 01:04:43', 0, 0),
(66, 'Địa đạo', 'Uploads/thumbnails/1751159050_z6752759874576_ae573a1ebdedd1dbd91cec7260d62ba8.jpg', '“Địa đạo” không chỉ là một bộ phim về đề tài chiến tranh thông thường. Phim đưa khán giả trở về trận càn Cedar Falls năm 1967 - chiến dịch quân sự quy mô lớn của đế quốc Mỹ nhằm triệt phá căn cứ quân giải phóng miền Nam Việt Nam.', 45, 0, 'Movie', 'Lịch sử, chiến tranh', 2025, 'Việt Nam', 'Việt Nam', 'Hoàng Minh Triết, Nhật Ý, Quang Tuấn , A Tới, Karel Granat, Khánh Ly,NSƯT Cao Minh,Thái Hòa, Hồ Thu Anh...', 'Bùi Thạc Chuyên ', 'active', 'Uploads/videos/1749528729_điaao.mp4', '2025-06-10 04:12:09', '2025-06-29 02:58:18', 1, 1),
(67, 'Mai', 'Uploads/thumbnails/1751159022_z6752759858754_c0053f9bfbf66a9594f5482c1e068d12.jpg', 'Mai là một bộ phim điện ảnh Việt Nam thuộc thể loại hài – lãng mạn – chính kịch ra mắt vào năm 2024 do Trấn Thành làm đạo diễn và đồng sản xuất, đánh dấu đây là bộ phim điện ảnh thứ ba anh làm đạo diễn.', 35, 0, 'Movie', 'Hài lãng mạng, chính kịch', 2025, 'Việt Nam', 'Việt Nam', 'Phương Anh Đào, Tuấn TRần, Trấn Thành, Hồng Đào, Uyển Ân,Ngọc Giàu, Khả Như, Trần Tiểu Vi...', 'Trấn Thành', 'active', 'Uploads/videos/1749528996_MAI.mp4', '2025-06-10 04:16:36', '2025-06-29 01:03:42', 0, 0),
(68, 'Thư Quyển Nhất Mộng', 'Uploads/thumbnails/1751170462_a2.jpg', 'Phim kể về Tống Tiểu Ngư, một cô gái hiện đại vô tình xuyên không vào thế giới kịch bản, trở thành nữ chính bị nam phản diện “dùng xong là vứt”, hành hạ đến chết. Không cần suy nghĩ, cô chọn cách duy nhất: chạy càng xa càng tốt! Kế hoạch nghe thì hoàn hảo, nhưng hiện thực lại đầy tàn khốc.', 2, 3, 'Series', 'Chính kịch, Cổ Trang, Tâm Lý, Tình Cảm,', 2025, 'China', 'China', 'Chúc Tự Đan, Giả Cảnh Huy, Hoàng Vĩ Đức, Lăng Mỹ Sĩ, Lữ Hành, Lưu Vũ Ninh, Lý Khinh, Lý Nhất Đồng, Quách Tiếu Thiên, Sung Long,', 'Quách Hổ,', 'inactive', '', '2025-06-29 04:14:22', '2025-06-29 05:31:00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'completed',
  `transaction_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('pending','completed','cancelled','refunded') NOT NULL DEFAULT 'completed',
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_vip` tinyint(4) DEFAULT 0,
  `vip_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`, `created_at`, `updated_at`, `is_vip`, `vip_expires_at`) VALUES
(20, 'tuan', 'tuan@gmail.com', '$2y$10$Ez5hBM/.o2r55bwo8SXJN.BUmItP6C1jJSNV1Rq5qvOz0A2Z6egqu', 0, '2025-06-29 04:37:45', '2025-06-29 05:10:06', 0, NULL),
(22, 'admin', 'admin@netfixvn.com', 'admin123', 1, '2025-06-29 05:04:43', '2025-06-29 05:04:43', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vip_orders`
--

CREATE TABLE `vip_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `order_date` datetime DEFAULT current_timestamp(),
  `order_status` varchar(50) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vip_orders`
--

INSERT INTO `vip_orders` (`id`, `user_id`, `package_name`, `price`, `payment_method`, `status`, `order_date`, `order_status`) VALUES
(8, 20, 'Gói VIP 3 Tháng', 237000.00, 'VNPAY', 'Completed', '2025-06-29 11:38:25', 'cancelled'),
(9, 20, 'Gói VIP 6 Tháng', 474000.00, 'Momo', 'Pending', '2025-06-29 12:10:13', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

CREATE TABLE `watch_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_watched_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `watch_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `watch_history`
--

INSERT INTO `watch_history` (`id`, `user_id`, `movie_id`, `viewed_at`, `last_watched_at`, `watch_date`) VALUES
(34, 20, 60, '2025-06-29 04:38:50', '2025-06-28 23:38:50', '2025-06-29 11:38:30'),
(35, 20, 68, '2025-06-29 04:39:04', '2025-06-28 23:39:04', '2025-06-29 11:39:01'),
(36, 22, 68, '2025-06-29 05:32:07', '2025-06-29 00:32:07', '2025-06-29 12:31:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vip_orders`
--
ALTER TABLE `vip_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`movie_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `vip_orders`
--
ALTER TABLE `vip_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `watch_history`
--
ALTER TABLE `watch_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vip_orders`
--
ALTER TABLE `vip_orders`
  ADD CONSTRAINT `vip_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD CONSTRAINT `watch_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watch_history_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
