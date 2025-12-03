-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 03:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_quanlydienthoai`
--

-- --------------------------------------------------------

--
-- Drop existing tables (in correct order due to foreign keys)
--
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'iPhone', 'Điện thoại Apple', '2025-11-26 01:47:46', '2025-11-26 01:47:46'),
(2, 'Samsung', 'Điện thoại Samsung', '2025-11-26 01:47:46', '2025-11-26 01:47:46'),
(3, 'Xiaomi', 'Điện thoại Xiaomi', '2025-11-26 01:47:46', '2025-11-26 01:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `total_purchases` decimal(15,2) DEFAULT 0.00,
  `purchase_count` int(11) DEFAULT 0,
  `loyalty_points` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `city`, `total_purchases`, `purchase_count`, `loyalty_points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', '0912345678', 'nguyenvana@gmail.com', '123 Nguyễn Trãi', NULL, 0.00, 0, 0, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47'),
(2, 'Trần Thị B', '0987654321', 'tranthib@gmail.com', '456 Lê Lợi', NULL, 0.00, 0, 0, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','card','transfer','cod') DEFAULT 'cash',
  `status` enum('pending','completed','cancelled','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_completed` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' AND NEW.customer_id IS NOT NULL THEN
        UPDATE `customers`
        SET
            `total_purchases` = `total_purchases` + NEW.total_amount,
            `purchase_count` = `purchase_count` + 1
        WHERE `id` = NEW.customer_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount_applied` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`quantity` * `unit_price` - `discount_applied`) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_quantity` int(11) DEFAULT 10,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `sku`, `description`, `price`, `cost`, `quantity`, `min_quantity`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', NULL, 32000000.00, NULL, 50, 10, NULL, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47'),
(2, 2, 'Samsung Galaxy S24 Ultra', 'SS24U512', NULL, 31500000.00, NULL, 30, 10, NULL, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('fixed','percent') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `min_amount` decimal(12,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Tên vai trò (admin, manager, sales, warehouse)',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết quyền hạn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Quản trị viên - Tất cả quyền'),
(2, 'manager', 'Quản lý cửa hàng'),
(3, 'sales', 'Nhân viên bán hàng'),
(4, 'warehouse', 'Nhân viên kho');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `stock_movements`
--
DELIMITER $$
CREATE TRIGGER `after_stock_movement_insert` AFTER INSERT ON `stock_movements` FOR EACH ROW BEGIN
    IF NEW.type = 'in' THEN
        UPDATE `products` SET `quantity` = `quantity` + NEW.quantity WHERE `id` = NEW.product_id;
    ELSEIF NEW.type = 'out' THEN
        UPDATE `products` SET `quantity` = `quantity` - NEW.quantity WHERE `id` = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`, `city`, `contact_person`, `tax_id`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'FPT Trading', '02873000911', 'info@fpt.com.vn', 'Số 10, Phạm Văn Bạch', 'Hà Nội', NULL, NULL, NULL, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47'),
(2, 'Digiworld', '02839268888', 'contact@dgw.com.vn', '195-197 Nguyễn Thái Bình', 'TP HCM', NULL, NULL, NULL, 'active', '2025-11-26 01:47:47', '2025-11-26 01:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3 COMMENT 'Mặc định là nhân viên sales hoặc khách hàng tùy logic',
  `username` varchar(50) DEFAULT NULL COMMENT 'Tên đăng nhập (Có thể NULL nếu dùng Google)',
  `password` varchar(255) DEFAULT NULL COMMENT 'Mật khẩu (NULL nếu đăng nhập bằng Google)',
  `full_name` varchar(100) NOT NULL COMMENT 'Họ và tên đầy đủ',
  `email` varchar(100) NOT NULL COMMENT 'Email (Dùng để định danh cho cả login thường và Google)',
  `phone` varchar(15) DEFAULT NULL COMMENT 'Số điện thoại',
  `avatar` varchar(255) DEFAULT 'default-avatar.png' COMMENT 'URL ảnh đại diện',
  `google_id` varchar(255) DEFAULT NULL COMMENT 'Lưu ID từ Google API để xác thực',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu thông tin tài khoản người dùng';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `full_name`, `email`, `phone`, `avatar`, `google_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', '$2y$10$V.vEUcNdfehENcXeUKQERujKdMw40rHRbUGk1MP5b3i6klr4gFf9u', 'Toàn Diện', 'admin@email.com', NULL, 'admin_face.jpg', NULL, 'active', '2025-11-26 01:47:46', '2025-11-26 02:11:33'),
(3, 3, 'sales01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Bình Minh', 'sales@email.com', NULL, 'sales_girl.jpg', NULL, 'active', '2025-11-26 01:47:46', '2025-11-26 01:47:46'),
(4, 4, 'kho01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Cường Trần', 'warehouse@email.com', NULL, 'default.png', NULL, 'active', '2025-11-26 01:47:46', '2025-11-26 01:47:46'),
(5, 3, NULL, NULL, 'Google User', 'googleuser@gmail.com', NULL, 'https://lh3.googleusercontent.com/a/ACg8oc...', '10293847561029384756', 'active', '2025-11-26 01:47:46', '2025-11-26 01:47:46'),
(6, 3, 'Thien1901', '$2y$10$JAvD6HD3G0gRcVqdF8c8cuPFQ.ngXN4Xxt1RXqoq5x/ezuCFQmXKK', 'Nguyen Thanh Thien', '19012004zoro@gmail.com', '0899750197', 'default-avatar.png', NULL, 'active', '2025-11-26 01:57:12', '2025-11-26 01:57:12'),
(7, 3, 'vinh', '$2y$10$yuLTSqUxuLIduyfgTaFioupRZ77lmnhG6fT3eYg8/loSuv1/89eJW', 'Nguyen Phú Vinh', 'vinhlo@gmail.com', '0112002', 'default-avatar.png', NULL, 'active', '2025-11-26 02:17:51', '2025-11-26 02:17:51'),
(8, 2, 'test1', '$2y$10$14RhK9/iOBAJao8dajKAkOZqhCKkcH3V2Th0YT.0LfmiYCrS7vC1e', 'test', 'test1@gmail.com', '011224755', 'default-avatar.png', NULL, 'active', '2025-11-26 02:20:57', '2025-11-26 02:20:57'),
(9, 3, 'test2', '$2y$10$iGc0Nkxpz3gkg1P4ujnBH.QAYXLkhozsZ1I8rFt7SyamgY14lw.VS', 'test2', 'test2@gmail.com', '011200200', 'default-avatar.png', NULL, 'active', '2025-11-26 02:21:37', '2025-11-26 02:21:37'),
(10, 4, 'test3', '$2y$10$yO7KnAg8S.e6.F6weL1sy.5OJk1AaXw9HJseI1VN41AxxAbVTGsbG', 'test3', 'test3@gmail.com', '0112002014', 'default-avatar.png', NULL, 'active', '2025-11-26 02:22:11', '2025-11-26 02:22:11'),
(11, 1, 'test4', '$2y$10$/RlfX3wQoIdeU7/rJjlaauulFeolBz.nBv.XMcxVRe3h3F2El.L1O', 'test4', 'test4@gmail.com', '01120020023', 'default-avatar.png', NULL, 'active', '2025-11-26 02:22:40', '2025-11-26 02:22:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
