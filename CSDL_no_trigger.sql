-- =================================================================
-- SCRIPT TẠO CƠ SỞ DỮ LIỆU (KHÔNG CÓ TRIGGER - Dành cho Free Hosting)
-- =================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- Xóa bảng theo thứ tự để tránh lỗi khóa ngoại
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

-- =================================================================
-- TẠO CẤU TRÚC CÁC BẢNG
-- =================================================================

-- Bảng 1: roles
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) UNIQUE NOT NULL,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL DEFAULT 3,
  `username` VARCHAR(50) UNIQUE,
  `password` VARCHAR(255),
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(15),
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `google_id` VARCHAR(255) UNIQUE DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 3: categories
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) UNIQUE NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 4: suppliers
CREATE TABLE `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) UNIQUE NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `email` VARCHAR(100) UNIQUE,
  `address` TEXT,
  `city` VARCHAR(100),
  `contact_person` VARCHAR(100),
  `tax_id` VARCHAR(50),
  `notes` TEXT,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 5: products
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `sku` VARCHAR(50) UNIQUE NOT NULL,
  `description` TEXT,
  `price` DECIMAL(12,2) NOT NULL,
  `cost` DECIMAL(12,2),
  `quantity` INT DEFAULT 0,
  `min_quantity` INT DEFAULT 10,
  `image` VARCHAR(255),
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 6: promotions
CREATE TABLE `promotions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `discount_type` ENUM('fixed', 'percent') NOT NULL,
  `discount_value` DECIMAL(12,2) NOT NULL,
  `product_id` INT,
  `min_amount` DECIMAL(12,2),
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `priority` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 7: stock_movements
CREATE TABLE `stock_movements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT,
  `supplier_id` INT,
  `order_id` INT,
  `type` ENUM('in', 'out') NOT NULL,
  `quantity` INT NOT NULL,
  `reference_number` VARCHAR(50),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 8: customers
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(15) UNIQUE,
  `email` VARCHAR(100) UNIQUE,
  `address` TEXT,
  `city` VARCHAR(100),
  `total_purchases` DECIMAL(15,2) DEFAULT 0.00,
  `purchase_count` INT DEFAULT 0,
  `loyalty_points` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 9: orders
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) UNIQUE NOT NULL,
  `customer_id` INT,
  `user_id` INT,
  `subtotal` DECIMAL(15,2) DEFAULT 0.00,
  `discount` DECIMAL(15,2) DEFAULT 0.00,
  `tax` DECIMAL(15,2) DEFAULT 0.00,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'transfer', 'cod') DEFAULT 'cash',
  `status` ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 10: order_items
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `discount_applied` DECIMAL(12,2) DEFAULT 0.00,
  `subtotal` DECIMAL(12,2) AS (`quantity` * `unit_price` - `discount_applied`) STORED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =================================================================
-- CHÈN DỮ LIỆU MẪU
-- =================================================================

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Quản trị viên - Tất cả quyền'),
(2, 'manager', 'Quản lý cửa hàng'),
(3, 'sales', 'Nhân viên bán hàng'),
(4, 'warehouse', 'Nhân viên kho');

-- 2. Users (Password: 123456)
INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `full_name`, `email`, `avatar`, `google_id`) VALUES
(1, 1, 'admin', '$2y$10$V.vEUcNdfehENcXeUKQERujKdMw40rHRbUGk1MP5b3i6klr4gFf9u', 'Toàn Diện', 'admin@email.com', 'admin_face.jpg', NULL),
(2, 2, 'manager01', '$2y$10$V.vEUcNdfehENcXeUKQERujKdMw40rHRbUGk1MP5b3i6klr4gFf9u', 'An Nguyễn', 'manager@email.com', 'default.png', NULL),
(3, 3, 'sales01', '$2y$10$V.vEUcNdfehENcXeUKQERujKdMw40rHRbUGk1MP5b3i6klr4gFf9u', 'Bình Minh', 'sales@email.com', 'sales_girl.jpg', NULL),
(4, 4, 'kho01', '$2y$10$V.vEUcNdfehENcXeUKQERujKdMw40rHRbUGk1MP5b3i6klr4gFf9u', 'Cường Trần', 'warehouse@email.com', 'default.png', NULL);

-- 3. Categories
INSERT INTO `categories` (`name`, `description`) VALUES
('iPhone', 'Điện thoại Apple'),
('Samsung', 'Điện thoại Samsung'),
('Xiaomi', 'Điện thoại Xiaomi');

-- 4. Suppliers
INSERT INTO `suppliers` (`name`, `phone`, `email`, `address`, `city`) VALUES
('FPT Trading', '02873000911', 'info@fpt.com.vn', 'Số 10, Phạm Văn Bạch', 'Hà Nội'),
('Digiworld', '02839268888', 'contact@dgw.com.vn', '195-197 Nguyễn Thái Bình', 'TP HCM');

-- 5. Products
INSERT INTO `products` (`category_id`, `name`, `sku`, `price`, `quantity`) VALUES
(1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', 32000000, 50),
(2, 'Samsung Galaxy S24 Ultra', 'SS24U512', 31500000, 30);

-- 6. Customers
INSERT INTO `customers` (`name`, `phone`, `email`, `address`) VALUES
('Nguyễn Văn A', '0912345678', 'nguyenvana@gmail.com', '123 Nguyễn Trãi'),
('Trần Thị B', '0987654321', 'tranthib@gmail.com', '456 Lê Lợi');

COMMIT;
