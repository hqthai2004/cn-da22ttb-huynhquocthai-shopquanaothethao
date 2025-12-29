-- =============================================
-- QT Shop - Full Database Setup
-- Bao gồm: Schema + 100 sản phẩm mới (25 sản phẩm/danh mục)
-- =============================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- 1. TẠO CÁC BẢNG (SCHEMA)
-- =============================================

-- Bảng users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng products
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `sizes` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng cart
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng wishlist
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng orders
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `status` enum('pending','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng order_items
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng contacts
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. THÊM DỮ LIỆU MẪU
-- =============================================

-- Admin user (password: admin123)
-- Hash được tạo bằng: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `status`) VALUES
(1, 'admin', 'admin@qtshop.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFtXPRnuJQVED1yMvjXpF6eKxqKzJHOi', 'Administrator', '0123456789', 'admin', 'active');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Áo thể thao', 'ao-the-thao', 'Áo thể thao nam nữ các loại', 'active'),
(2, 'Quần thể thao', 'quan-the-thao', 'Quần short, quần dài thể thao', 'active'),
(3, 'Phụ kiện', 'phu-kien', 'Phụ kiện thể thao đa dạng', 'active'),
(4, 'Giày thể thao', 'giay-the-thao', 'Giày chạy bộ, giày tập gym', 'active');

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'QT Shop', 'text'),
('site_description', 'Hệ thống bán hàng quần áo thể thao chất lượng cao', 'text'),
('site_email', 'contact@qtshop.com', 'text'),
('site_phone', '0123456789', 'text'),
('shipping_fee', '30000', 'number');

-- =============================================
-- 3. THÊM 100 SẢN PHẨM MỚI (25 sản phẩm/danh mục)
-- =============================================

-- ÁO THỂ THAO (25 sản phẩm)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sizes`, `is_featured`, `status`) VALUES
(1, 1, 'Áo Nike Dri-FIT Legend', 'ao-nike-dri-fit-legend', 'Áo thể thao Nike với công nghệ Dri-FIT thấm hút mồ hôi tuyệt vời', 450000.00, 380000.00, 50, 'S,M,L,XL,XXL', 1, 'active'),
(2, 1, 'Áo Adidas Techfit Compression', 'ao-adidas-techfit-compression', 'Áo bó body Adidas hỗ trợ cơ bắp khi tập luyện', 520000.00, NULL, 45, 'S,M,L,XL', 1, 'active'),
(3, 1, 'Áo Puma DryCELL Training', 'ao-puma-drycell-training', 'Áo tập luyện Puma với chất liệu thoáng khí cao cấp', 380000.00, 320000.00, 60, 'S,M,L,XL,XXL', 1, 'active'),
(4, 1, 'Áo Under Armour HeatGear', 'ao-under-armour-heatgear', 'Áo thể thao Under Armour công nghệ tản nhiệt', 590000.00, NULL, 35, 'S,M,L,XL', 0, 'active'),
(5, 1, 'Áo Reebok Speedwick', 'ao-reebok-speedwick', 'Áo tập gym Reebok thấm hút mồ hôi nhanh', 410000.00, 350000.00, 55, 'S,M,L,XL', 0, 'active'),
(6, 1, 'Áo Nike Pro Hypercool', 'ao-nike-pro-hypercool', 'Áo Nike Pro với lưới thoáng khí tối ưu', 480000.00, NULL, 40, 'S,M,L,XL,XXL', 0, 'active'),
(7, 1, 'Áo Adidas Alphaskin', 'ao-adidas-alphaskin', 'Áo bó sát Adidas hỗ trợ vận động', 550000.00, 470000.00, 48, 'S,M,L,XL', 0, 'active'),
(8, 1, 'Áo Puma Essential Logo', 'ao-puma-essential-logo', 'Áo thể thao Puma thiết kế đơn giản, năng động', 350000.00, NULL, 70, 'S,M,L,XL,XXL', 0, 'active'),
(9, 1, 'Áo Nike Breathe Elite', 'ao-nike-breathe-elite', 'Áo Nike với công nghệ thông gió tối ưu', 510000.00, 440000.00, 42, 'S,M,L,XL', 0, 'active'),
(10, 1, 'Áo Reebok CrossFit', 'ao-reebok-crossfit', 'Áo tập CrossFit chuyên dụng từ Reebok', 460000.00, NULL, 38, 'S,M,L,XL,XXL', 0, 'active'),
(11, 1, 'Áo Adidas FreeLift Sport', 'ao-adidas-freelift-sport', 'Áo Adidas không bị cuộn khi vận động', 490000.00, 420000.00, 52, 'S,M,L,XL', 0, 'active'),
(12, 1, 'Áo Puma Active Tee', 'ao-puma-active-tee', 'Áo thun thể thao Puma phong cách trẻ trung', 320000.00, NULL, 65, 'S,M,L,XL,XXL', 0, 'active'),
(13, 1, 'Áo Nike Miler Running', 'ao-nike-miler-running', 'Áo chạy bộ Nike nhẹ nhàng, thoải mái', 430000.00, 370000.00, 47, 'S,M,L,XL', 0, 'active'),
(14, 1, 'Áo Under Armour Tech 2.0', 'ao-under-armour-tech-2-0', 'Áo Under Armour phiên bản nâng cấp 2.0', 540000.00, NULL, 44, 'S,M,L,XL,XXL', 0, 'active'),
(15, 1, 'Áo Reebok Training Essentials', 'ao-reebok-training-essentials', 'Áo tập luyện cơ bản Reebok chất lượng cao', 380000.00, 320000.00, 58, 'S,M,L,XL', 0, 'active'),
(16, 1, 'Áo Nike Legend 2.0', 'ao-nike-legend-2-0', 'Áo Nike Legend phiên bản mới nhất', 470000.00, NULL, 41, 'S,M,L,XL,XXL', 0, 'active'),
(17, 1, 'Áo Adidas Own The Run', 'ao-adidas-own-the-run', 'Áo chạy bộ Adidas cho runner chuyên nghiệp', 560000.00, 480000.00, 36, 'S,M,L,XL', 0, 'active'),
(18, 1, 'Áo Puma Performance Graphic', 'ao-puma-performance-graphic', 'Áo Puma với họa tiết năng động', 400000.00, NULL, 54, 'S,M,L,XL,XXL', 0, 'active'),
(19, 1, 'Áo Nike Dri-FIT Academy', 'ao-nike-dri-fit-academy', 'Áo tập bóng đá Nike chuyên dụng', 420000.00, 360000.00, 49, 'S,M,L,XL', 0, 'active'),
(20, 1, 'Áo Reebok Workout Ready', 'ao-reebok-workout-ready', 'Áo Reebok sẵn sàng cho mọi bài tập', 390000.00, NULL, 62, 'S,M,L,XL,XXL', 0, 'active'),
(21, 1, 'Áo Adidas Designed To Move', 'ao-adidas-designed-to-move', 'Áo Adidas thiết kế tối ưu cho vận động', 440000.00, 380000.00, 46, 'S,M,L,XL', 0, 'active'),
(22, 1, 'Áo Puma Train Favorite', 'ao-puma-train-favorite', 'Áo tập luyện yêu thích từ Puma', 360000.00, NULL, 68, 'S,M,L,XL,XXL', 0, 'active'),
(23, 1, 'Áo Nike Pro Fitted', 'ao-nike-pro-fitted', 'Áo Nike Pro ôm body thoải mái', 500000.00, 430000.00, 39, 'S,M,L,XL', 0, 'active'),
(24, 1, 'Áo Under Armour Sportstyle', 'ao-under-armour-sportstyle', 'Áo Under Armour phong cách thể thao đường phố', 480000.00, NULL, 51, 'S,M,L,XL,XXL', 0, 'active'),
(25, 1, 'Áo Reebok Les Mills', 'ao-reebok-les-mills', 'Áo tập Les Mills chính hãng Reebok', 530000.00, 450000.00, 33, 'S,M,L,XL', 0, 'active');

-- QUẦN THỂ THAO (25 sản phẩm)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sizes`, `is_featured`, `status`) VALUES
(26, 2, 'Quần Nike Flex Stride', 'quan-nike-flex-stride', 'Quần chạy bộ Nike co giãn 4 chiều tuyệt vời', 520000.00, 450000.00, 42, 'S,M,L,XL,XXL', 1, 'active'),
(27, 2, 'Quần Adidas Tiro 21', 'quan-adidas-tiro-21', 'Quần training Adidas phong cách bóng đá', 580000.00, NULL, 38, 'S,M,L,XL', 1, 'active'),
(28, 2, 'Quần Puma Teamgoal', 'quan-puma-teamgoal', 'Quần thể thao Puma cho đội nhóm', 490000.00, 420000.00, 55, 'S,M,L,XL,XXL', 1, 'active'),
(29, 2, 'Quần Under Armour Rival', 'quan-under-armour-rival', 'Quần jogger Under Armour thoải mái', 610000.00, NULL, 34, 'S,M,L,XL', 0, 'active'),
(30, 2, 'Quần Reebok Workout Ready', 'quan-reebok-workout-ready', 'Quần tập gym Reebok linh hoạt', 450000.00, 380000.00, 48, 'S,M,L,XL,XXL', 0, 'active'),
(31, 2, 'Quần Nike Dri-FIT Academy', 'quan-nike-dri-fit-academy', 'Quần tập bóng Nike thấm hút mồ hôi', 550000.00, NULL, 41, 'S,M,L,XL', 0, 'active'),
(32, 2, 'Quần Adidas Essentials', 'quan-adidas-essentials', 'Quần thể thao Adidas thiết yếu', 420000.00, 360000.00, 62, 'S,M,L,XL,XXL', 0, 'active'),
(33, 2, 'Quần Puma Active Woven', 'quan-puma-active-woven', 'Quần dệt Puma nhẹ nhàng thoáng mát', 480000.00, NULL, 46, 'S,M,L,XL', 0, 'active'),
(34, 2, 'Quần Nike Tech Fleece', 'quan-nike-tech-fleece', 'Quần jogger Nike vải nỉ cao cấp', 780000.00, 680000.00, 28, 'S,M,L,XL,XXL', 0, 'active'),
(35, 2, 'Quần Reebok Training Essentials', 'quan-reebok-training-essentials', 'Quần training cơ bản Reebok', 390000.00, NULL, 58, 'S,M,L,XL', 0, 'active'),
(36, 2, 'Quần Adidas 3-Stripes', 'quan-adidas-3-stripes', 'Quần Adidas 3 sọc kinh điển', 520000.00, 450000.00, 44, 'S,M,L,XL,XXL', 0, 'active'),
(37, 2, 'Quần Puma Evostripe', 'quan-puma-evostripe', 'Quần Puma thiết kế sọc hiện đại', 460000.00, NULL, 52, 'S,M,L,XL', 0, 'active'),
(38, 2, 'Quần Nike Sportswear Club', 'quan-nike-sportswear-club', 'Quần thể thao Nike phong cách đường phố', 440000.00, 380000.00, 56, 'S,M,L,XL,XXL', 0, 'active'),
(39, 2, 'Quần Under Armour Sportstyle', 'quan-under-armour-sportstyle', 'Quần Under Armour đa năng', 590000.00, NULL, 36, 'S,M,L,XL', 0, 'active'),
(40, 2, 'Quần Reebok Speedwick', 'quan-reebok-speedwick', 'Quần Reebok khô nhanh chóng', 430000.00, 370000.00, 49, 'S,M,L,XL,XXL', 0, 'active'),
(41, 2, 'Quần Nike Pro Compression', 'quan-nike-pro-compression', 'Quần bó Nike hỗ trợ cơ bắp', 560000.00, NULL, 39, 'S,M,L,XL', 0, 'active'),
(42, 2, 'Quần Adidas Own The Run', 'quan-adidas-own-the-run', 'Quần chạy bộ Adidas chuyên nghiệp', 620000.00, 540000.00, 32, 'S,M,L,XL,XXL', 0, 'active'),
(43, 2, 'Quần Puma Performance', 'quan-puma-performance', 'Quần tập luyện Puma hiệu suất cao', 500000.00, NULL, 47, 'S,M,L,XL', 0, 'active'),
(44, 2, 'Quần Nike Challenger', 'quan-nike-challenger', 'Quần short Nike cho runner', 380000.00, 320000.00, 64, 'S,M,L,XL,XXL', 0, 'active'),
(45, 2, 'Quần Reebok CrossFit', 'quan-reebok-crossfit', 'Quần tập CrossFit chuyên dụng', 540000.00, NULL, 37, 'S,M,L,XL', 0, 'active'),
(46, 2, 'Quần Adidas Designed To Move', 'quan-adidas-designed-to-move', 'Quần Adidas tối ưu vận động', 470000.00, 400000.00, 51, 'S,M,L,XL,XXL', 0, 'active'),
(47, 2, 'Quần Puma Train Favorite', 'quan-puma-train-favorite', 'Quần tập yêu thích Puma', 410000.00, NULL, 59, 'S,M,L,XL', 0, 'active'),
(48, 2, 'Quần Nike Yoga', 'quan-nike-yoga', 'Quần tập yoga Nike co giãn tốt', 650000.00, 560000.00, 30, 'S,M,L,XL,XXL', 0, 'active'),
(49, 2, 'Quần Under Armour HeatGear', 'quan-under-armour-heatgear', 'Quần Under Armour tản nhiệt', 680000.00, NULL, 26, 'S,M,L,XL', 0, 'active'),
(50, 2, 'Quần Reebok Les Mills', 'quan-reebok-les-mills', 'Quần tập Les Mills Reebok', 570000.00, 490000.00, 43, 'S,M,L,XL,XXL', 0, 'active');

-- PHỤ KIỆN (25 sản phẩm)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sizes`, `is_featured`, `status`) VALUES
(51, 3, 'Balo Nike Brasilia', 'balo-nike-brasilia', 'Balo thể thao Nike đa năng, nhiều ngăn', 580000.00, 520000.00, 75, NULL, 1, 'active'),
(52, 3, 'Túi Adidas Duffel', 'tui-adidas-duffel', 'Túi xách thể thao Adidas size lớn', 720000.00, NULL, 48, NULL, 1, 'active'),
(53, 3, 'Găng tay Puma Training', 'gang-tay-puma-training', 'Găng tay tập gym Puma chống trượt', 180000.00, 150000.00, 120, 'M,L,XL', 1, 'active'),
(54, 3, 'Băng đô Nike Swoosh', 'bang-do-nike-swoosh', 'Băng đô thấm mồ hôi Nike', 120000.00, NULL, 150, NULL, 0, 'active'),
(55, 3, 'Tất Reebok Active', 'tat-reebok-active', 'Tất thể thao Reebok 3 đôi', 150000.00, 120000.00, 200, NULL, 0, 'active'),
(56, 3, 'Bình nước Nike HyperCharge', 'binh-nuoc-nike-hypercharge', 'Bình nước Nike 700ml chống đổ', 350000.00, NULL, 95, NULL, 0, 'active'),
(57, 3, 'Khăn Adidas Towel', 'khan-adidas-towel', 'Khăn thể thao Adidas thấm hút tốt', 220000.00, 190000.00, 110, NULL, 0, 'active'),
(58, 3, 'Dây nhảy Puma Speed', 'day-nhay-puma-speed', 'Dây nhảy Puma có đếm số', 280000.00, NULL, 85, NULL, 0, 'active'),
(59, 3, 'Đai lưng Nike Structured', 'dai-lung-nike-structured', 'Đai lưng tập gym Nike hỗ trợ lưng', 480000.00, 420000.00, 60, 'M,L,XL', 0, 'active'),
(60, 3, 'Kính Reebok Running', 'kinh-reebok-running', 'Kính chạy bộ Reebok chống UV', 380000.00, NULL, 70, NULL, 0, 'active'),
(61, 3, 'Mũ Adidas Baseball', 'mu-adidas-baseball', 'Mũ lưỡi trai Adidas thể thao', 290000.00, 250000.00, 130, NULL, 0, 'active'),
(62, 3, 'Vớ Puma Performance', 'vo-puma-performance', 'Vớ cao cổ Puma 3 đôi', 180000.00, NULL, 160, NULL, 0, 'active'),
(63, 3, 'Balo Under Armour Hustle', 'balo-under-armour-hustle', 'Balo Under Armour chống nước', 890000.00, 780000.00, 42, NULL, 0, 'active'),
(64, 3, 'Túi Nike Gym Club', 'tui-nike-gym-club', 'Túi tập gym Nike nhỏ gọn', 450000.00, NULL, 88, NULL, 0, 'active'),
(65, 3, 'Găng tay Adidas Essential', 'gang-tay-adidas-essential', 'Găng tay tập tạ Adidas có đệm', 220000.00, 180000.00, 105, 'M,L,XL', 0, 'active'),
(66, 3, 'Băng cổ tay Nike Pro', 'bang-co-tay-nike-pro', 'Băng cổ tay Nike hỗ trợ cổ tay', 90000.00, NULL, 180, NULL, 0, 'active'),
(67, 3, 'Tất Adidas Cushioned', 'tat-adidas-cushioned', 'Tất Adidas có đệm êm ái', 160000.00, 130000.00, 145, NULL, 0, 'active'),
(68, 3, 'Bình lắc Puma Shaker', 'binh-lac-puma-shaker', 'Bình lắc protein Puma 600ml', 250000.00, NULL, 92, NULL, 0, 'active'),
(69, 3, 'Khăn Reebok Cooling', 'khan-reebok-cooling', 'Khăn làm mát Reebok công nghệ', 180000.00, 150000.00, 78, NULL, 0, 'active'),
(70, 3, 'Dây kháng lực Nike', 'day-khang-luc-nike', 'Dây kháng lực Nike 3 mức độ', 420000.00, NULL, 65, NULL, 0, 'active'),
(71, 3, 'Đai bụng Adidas', 'dai-bung-adidas', 'Đai bụng giảm mỡ Adidas', 350000.00, 300000.00, 55, 'M,L,XL', 0, 'active'),
(72, 3, 'Kính Puma Sport', 'kinh-puma-sport', 'Kính thể thao Puma phân cực', 450000.00, NULL, 58, NULL, 0, 'active'),
(73, 3, 'Mũ Nike Featherlight', 'mu-nike-featherlight', 'Mũ chạy bộ Nike siêu nhẹ', 320000.00, 280000.00, 115, NULL, 0, 'active'),
(74, 3, 'Vớ Under Armour HeatGear', 'vo-under-armour-heatgear', 'Vớ Under Armour tản nhiệt', 200000.00, NULL, 135, NULL, 0, 'active'),
(75, 3, 'Túi đeo chéo Reebok', 'tui-deo-cheo-reebok', 'Túi đeo chéo Reebok nhỏ gọn', 380000.00, 320000.00, 72, NULL, 0, 'active');

-- GIÀY THỂ THAO (25 sản phẩm)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `sizes`, `is_featured`, `status`) VALUES
(76, 4, 'Giày Nike Air Zoom Pegasus', 'giay-nike-air-zoom-pegasus', 'Giày chạy bộ Nike đệm khí Zoom', 2800000.00, 2500000.00, 35, '38,39,40,41,42,43,44', 1, 'active'),
(77, 4, 'Giày Adidas Ultraboost 22', 'giay-adidas-ultraboost-22', 'Giày Adidas đệm Boost năng lượng', 3200000.00, NULL, 28, '38,39,40,41,42,43,44', 1, 'active'),
(78, 4, 'Giày Puma Velocity Nitro', 'giay-puma-velocity-nitro', 'Giày chạy Puma công nghệ Nitro', 2600000.00, 2300000.00, 42, '38,39,40,41,42,43,44', 1, 'active'),
(79, 4, 'Giày Nike React Infinity', 'giay-nike-react-infinity', 'Giày Nike React êm ái bền bỉ', 2900000.00, NULL, 31, '38,39,40,41,42,43,44', 0, 'active'),
(80, 4, 'Giày Reebok Nano X2', 'giay-reebok-nano-x2', 'Giày tập gym Reebok đa năng', 2700000.00, 2400000.00, 38, '38,39,40,41,42,43,44', 0, 'active'),
(81, 4, 'Giày Under Armour HOVR', 'giay-under-armour-hovr', 'Giày Under Armour công nghệ HOVR', 3100000.00, NULL, 26, '38,39,40,41,42,43,44', 0, 'active'),
(82, 4, 'Giày Adidas Solarboost', 'giay-adidas-solarboost', 'Giày chạy Adidas năng lượng mặt trời', 2500000.00, 2200000.00, 44, '38,39,40,41,42,43,44', 0, 'active'),
(83, 4, 'Giày Nike Free RN', 'giay-nike-free-rn', 'Giày Nike Free chạy tự nhiên', 2350000.00, NULL, 48, '38,39,40,41,42,43,44', 0, 'active'),
(84, 4, 'Giày Puma Deviate Nitro', 'giay-puma-deviate-nitro', 'Giày Puma carbon plate tốc độ', 3400000.00, 3000000.00, 22, '38,39,40,41,42,43,44', 0, 'active'),
(85, 4, 'Giày Reebok Floatride', 'giay-reebok-floatride', 'Giày Reebok đệm Floatride nhẹ', 2850000.00, NULL, 33, '38,39,40,41,42,43,44', 0, 'active'),
(86, 4, 'Giày Nike ZoomX Vaporfly', 'giay-nike-zoomx-vaporfly', 'Giày marathon Nike chuyên nghiệp', 4500000.00, 4000000.00, 18, '38,39,40,41,42,43,44', 0, 'active'),
(87, 4, 'Giày Adidas Adizero Boston', 'giay-adidas-adizero-boston', 'Giày chạy Adidas siêu nhẹ', 3000000.00, NULL, 29, '38,39,40,41,42,43,44', 0, 'active'),
(88, 4, 'Giày Puma RS-X', 'giay-puma-rs-x', 'Giày Puma phong cách retro', 2400000.00, 2100000.00, 52, '38,39,40,41,42,43,44', 0, 'active'),
(89, 4, 'Giày Nike Metcon 8', 'giay-nike-metcon-8', 'Giày tập gym Nike ổn định', 3300000.00, NULL, 24, '38,39,40,41,42,43,44', 0, 'active'),
(90, 4, 'Giày Reebok Legacy Lifter', 'giay-reebok-legacy-lifter', 'Giày cử tạ Reebok chuyên dụng', 3800000.00, 3400000.00, 16, '38,39,40,41,42,43,44', 0, 'active'),
(91, 4, 'Giày Under Armour TriBase', 'giay-under-armour-tribase', 'Giày Under Armour đế 3 điểm', 2900000.00, NULL, 36, '38,39,40,41,42,43,44', 0, 'active'),
(92, 4, 'Giày Adidas Supernova', 'giay-adidas-supernova', 'Giày chạy Adidas thoải mái', 2200000.00, 1900000.00, 46, '38,39,40,41,42,43,44', 0, 'active'),
(93, 4, 'Giày Nike Joyride', 'giay-nike-joyride', 'Giày Nike công nghệ bi đệm', 2650000.00, NULL, 39, '38,39,40,41,42,43,44', 0, 'active'),
(94, 4, 'Giày Puma Hybrid Runner', 'giay-puma-hybrid-runner', 'Giày Puma kết hợp công nghệ', 2550000.00, 2200000.00, 41, '38,39,40,41,42,43,44', 0, 'active'),
(95, 4, 'Giày Reebok Zig Kinetica', 'giay-reebok-zig-kinetica', 'Giày Reebok đế Zig năng lượng', 2750000.00, NULL, 34, '38,39,40,41,42,43,44', 0, 'active'),
(96, 4, 'Giày Nike Air Max 270', 'giay-nike-air-max-270', 'Giày Nike Air Max đệm khí lớn', 3100000.00, 2700000.00, 30, '38,39,40,41,42,43,44', 0, 'active'),
(97, 4, 'Giày Adidas NMD R1', 'giay-adidas-nmd-r1', 'Giày Adidas NMD phong cách đường phố', 2800000.00, NULL, 37, '38,39,40,41,42,43,44', 0, 'active'),
(98, 4, 'Giày Puma Future Rider', 'giay-puma-future-rider', 'Giày Puma thiết kế tương lai', 2300000.00, 2000000.00, 49, '38,39,40,41,42,43,44', 0, 'active'),
(99, 4, 'Giày Nike Revolution 6', 'giay-nike-revolution-6', 'Giày Nike giá tốt cho người mới', 1800000.00, NULL, 68, '38,39,40,41,42,43,44', 0, 'active'),
(100, 4, 'Giày Adidas Duramo SL', 'giay-adidas-duramo-sl', 'Giày Adidas bền bỉ giá rẻ', 1650000.00, 1400000.00, 75, '38,39,40,41,42,43,44', 0, 'active');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

-- =============================================
-- HƯỚNG DẪN SỬ DỤNG
-- =============================================
-- 1. Tạo database: CREATE DATABASE shopquanao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 2. Import file này vào phpMyAdmin
-- 3. Đăng nhập admin: username = admin, password = admin123
-- 4. Website đã có đầy đủ 100 sản phẩm mới, sẵn sàng sử dụng!
-- 
-- THỐNG KÊ SẢN PHẨM:
-- - Áo thể thao: 25 sản phẩm (ID 1-25)
-- - Quần thể thao: 25 sản phẩm (ID 26-50)
-- - Phụ kiện: 25 sản phẩm (ID 51-75)
-- - Giày thể thao: 25 sản phẩm (ID 76-100)
-- TỔNG: 100 sản phẩm
-- =============================================
