-- =================================================================
-- SCRIPT TẠO CƠ SỞ DỮ LIỆU VÀ BẢNG CHO
-- DỰ ÁN WEBSITE QUẢN LÝ CỬA HÀNG ĐIỆN THOẠI
-- =================================================================
-- Version: 1.3 - Cập nhật bảng users (avatar, google_id)

-- PHẦN 1: TẠO VÀ SỬ DỤNG CƠ SỞ DỮ LIỆU
-- =================================================================

CREATE DATABASE IF NOT EXISTS `db_quanlydienthoai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_quanlydienthoai`;

-- =================================================================
-- PHẦN 2: CÀI ĐẶT BAN ĐẦU VÀ XÓA BẢNG CŨ
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
-- PHẦN 3: TẠO CẤU TRÚC CÁC BẢNG
-- =================================================================

-- Bảng 1: roles
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Tên vai trò (admin, manager, sales, warehouse)',
  `description` TEXT COMMENT 'Mô tả chi tiết quyền hạn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: users (Đã cập nhật Avatar và Google Login)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL DEFAULT 3 COMMENT 'Mặc định là nhân viên sales hoặc khách hàng tùy logic',
  `username` VARCHAR(50) UNIQUE COMMENT 'Tên đăng nhập (Có thể NULL nếu dùng Google)',
  `password` VARCHAR(255) COMMENT 'Mật khẩu (NULL nếu đăng nhập bằng Google)',
  `full_name` VARCHAR(100) NOT NULL COMMENT 'Họ và tên đầy đủ',
  `email` VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email (Dùng để định danh cho cả login thường và Google)',
  `phone` VARCHAR(15) COMMENT 'Số điện thoại',
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png' COMMENT 'URL ảnh đại diện',
  `google_id` VARCHAR(255) UNIQUE DEFAULT NULL COMMENT 'Lưu ID từ Google API để xác thực',
  `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu thông tin tài khoản người dùng';

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
  `subtotal` DECIMAL(12,2) GENERATED ALWAYS AS (`quantity` * `unit_price` - `discount_applied`) STORED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- PHẦN 4: TRIGGERS
-- =================================================================

DELIMITER $$

CREATE TRIGGER `after_stock_movement_insert`
AFTER INSERT ON `stock_movements`
FOR EACH ROW
BEGIN
    IF NEW.type = 'in' THEN
        UPDATE `products` SET `quantity` = `quantity` + NEW.quantity WHERE `id` = NEW.product_id;
    ELSEIF NEW.type = 'out' THEN
        UPDATE `products` SET `quantity` = `quantity` - NEW.quantity WHERE `id` = NEW.product_id;
    END IF;
END$$

CREATE TRIGGER `after_order_completed`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' AND NEW.customer_id IS NOT NULL THEN
        UPDATE `customers`
        SET
            `total_purchases` = `total_purchases` + NEW.total_amount,
            `purchase_count` = `purchase_count` + 1
        WHERE `id` = NEW.customer_id;
    END IF;
END$$

DELIMITER ;

-- =================================================================
-- PHẦN 5: CHÈN DỮ LIỆU MẪU (Đã cập nhật cho bảng Users)
-- =================================================================

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Quản trị viên - Tất cả quyền'),
(2, 'manager', 'Quản lý cửa hàng'),
(3, 'sales', 'Nhân viên bán hàng'),
(4, 'warehouse', 'Nhân viên kho');

-- 2. Users (Có thêm avatar và google_id)
-- Lưu ý: Password mẫu là '123456'. User số 5 mô phỏng đăng nhập bằng Google (không có pass, có google_id)
INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `full_name`, `email`, `avatar`, `google_id`) VALUES
(1, 1, 'admin', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Toàn Diện', 'admin@email.com', 'admin_face.jpg', NULL),
(2, 2, 'manager01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'An Nguyễn', 'manager@email.com', 'default.png', NULL),
(3, 3, 'sales01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Bình Minh', 'sales@email.com', 'sales_girl.jpg', NULL),
(4, 4, 'kho01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Cường Trần', 'warehouse@email.com', 'default.png', NULL),
(5, 3, NULL, NULL, 'Google User', 'googleuser@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8oc...', '10293847561029384756'); 

-- 3. Categories (Danh mục loại sản phẩm)
INSERT INTO `categories` (`name`, `description`) VALUES
('Điện thoại cao cấp', 'Smartphone flagship từ 20 triệu trở lên'),
('Điện thoại tầm trung', 'Smartphone từ 7-20 triệu'),
('Điện thoại phổ thông', 'Smartphone dưới 7 triệu'),
('Máy tính bảng', 'Tablet các hãng'),
('Đồng hồ thông minh', 'Smartwatch, đồng hồ thể thao'),
('Tai nghe', 'Tai nghe không dây, có dây các loại'),
('Sạc và cáp', 'Củ sạc, cáp sạc, sạc dự phòng'),
('Ốp lưng và bao da', 'Phụ kiện bảo vệ điện thoại'),
('Phụ kiện khác', 'Kính cường lực, giá đỡ, gậy selfie...');

-- 4. Suppliers
INSERT INTO `suppliers` (`name`, `phone`, `email`, `address`, `city`) VALUES
('FPT Trading', '02873000911', 'info@fpt.com.vn', 'Số 10, Phạm Văn Bạch', 'Hà Nội'),
('Digiworld', '02839268888', 'contact@dgw.com.vn', '195-197 Nguyễn Thái Bình', 'TP HCM'),
('Synnex FPT', '02839978899', 'info@synnexfpt.com.vn', '15 Lê Thánh Tôn', 'TP HCM'),
('Phú Thái Holdings', '02438224466', 'contact@phuthai.com.vn', '77 Hoàng Văn Thái', 'Hà Nội');

-- 5. Products
INSERT INTO `products` (`category_id`, `name`, `sku`, `price`, `cost`, `quantity`, `min_quantity`, `description`) VALUES
-- Điện thoại cao cấp (category_id = 1)
(1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', 34990000, 32000000, 50, 10, 'Apple - Chip A17 Pro, Camera 48MP, Titanium'),
(1, 'iPhone 15 Pro Max 512GB', 'IP15PM512', 40990000, 38000000, 30, 5, 'Apple - Chip A17 Pro, Camera 48MP, Titanium'),
(1, 'iPhone 15 Pro 128GB', 'IP15P128', 28990000, 26500000, 45, 10, 'Apple - Chip A17 Pro, Action Button'),
(1, 'Samsung Galaxy S24 Ultra 256GB', 'SS24U256', 31990000, 29500000, 40, 10, 'Samsung - Snapdragon 8 Gen 3, S Pen, 200MP'),
(1, 'Samsung Galaxy S24 Ultra 512GB', 'SS24U512', 36990000, 34000000, 25, 5, 'Samsung - Snapdragon 8 Gen 3, S Pen'),
(1, 'Samsung Galaxy Z Fold5 256GB', 'SSZF5256', 41990000, 39000000, 20, 5, 'Samsung - Màn hình gập 7.6 inch'),
(1, 'Xiaomi 14 Ultra 512GB', 'XM14U512', 29990000, 27500000, 25, 5, 'Xiaomi - Snapdragon 8 Gen 3, Leica Camera'),
(1, 'OPPO Find X7 Ultra 256GB', 'OPFX7U256', 28990000, 26500000, 20, 5, 'OPPO - Hasselblad Camera'),
(1, 'Vivo X100 Pro 256GB', 'VVX100P256', 27990000, 25500000, 25, 5, 'Vivo - Dimensity 9300, ZEISS Camera'),
(1, 'Google Pixel 8 Pro 128GB', 'GP8P128', 26990000, 25000000, 20, 5, 'Google - Tensor G3, AI Camera'),
(1, 'OnePlus 12 256GB', 'OP12256', 22990000, 21000000, 30, 10, 'OnePlus - Snapdragon 8 Gen 3, Hasselblad'),

-- Điện thoại tầm trung (category_id = 2)
(2, 'iPhone 15 128GB', 'IP15128', 22990000, 21000000, 60, 15, 'Apple - Chip A16 Bionic, USB-C'),
(2, 'iPhone 14 128GB', 'IP14128', 18990000, 17000000, 40, 10, 'Apple - Chip A15 Bionic'),
(2, 'Samsung Galaxy S24 256GB', 'SS24256', 21990000, 20000000, 50, 15, 'Samsung - Snapdragon 8 Gen 3, Galaxy AI'),
(2, 'Samsung Galaxy Z Flip5 256GB', 'SSZFL5256', 25990000, 24000000, 30, 10, 'Samsung - Màn hình gập nhỏ gọn'),
(2, 'Xiaomi 14 256GB', 'XM14256', 18990000, 17000000, 40, 10, 'Xiaomi - Snapdragon 8 Gen 3, Leica'),
(2, 'Xiaomi 13T Pro 256GB', 'XM13TP256', 13990000, 12500000, 45, 10, 'Xiaomi - Dimensity 9200+, 50MP'),
(2, 'OPPO Find N3 Flip 256GB', 'OPFN3F256', 22990000, 21000000, 25, 5, 'OPPO - Điện thoại gập thời trang'),
(2, 'OPPO Reno11 Pro 5G 256GB', 'OPR11P256', 12990000, 11500000, 50, 15, 'OPPO - Portrait Expert'),
(2, 'Vivo X100 256GB', 'VVX100256', 19990000, 18000000, 35, 10, 'Vivo - Dimensity 9300, 50MP'),
(2, 'Vivo V30 Pro 5G 256GB', 'VVV30P256', 12990000, 11500000, 45, 10, 'Vivo - Aura Light Portrait'),
(2, 'Realme GT5 Pro 256GB', 'RMGT5P256', 15990000, 14500000, 30, 10, 'Realme - Gaming flagship'),
(2, 'OnePlus 12R 256GB', 'OP12R256', 14990000, 13500000, 40, 10, 'OnePlus - 100W SUPERVOOC'),
(2, 'Google Pixel 8 128GB', 'GP8128', 18990000, 17500000, 30, 10, 'Google - Tensor G3, 7 năm update'),

-- Điện thoại phổ thông (category_id = 3)
(3, 'Samsung Galaxy A54 5G 128GB', 'SSA54128', 9990000, 8500000, 80, 20, 'Samsung - Exynos 1380, IP67'),
(3, 'Samsung Galaxy A34 5G 128GB', 'SSA34128', 7490000, 6500000, 70, 20, 'Samsung - Dimensity 1080'),
(3, 'Redmi Note 13 Pro+ 5G 256GB', 'RN13PP256', 9990000, 8800000, 70, 20, 'Xiaomi - Camera 200MP'),
(3, 'Redmi Note 13 Pro 5G 128GB', 'RN13P128', 7990000, 7000000, 90, 25, 'Xiaomi - AMOLED 120Hz'),
(3, 'POCO X6 Pro 256GB', 'POCOX6P256', 8990000, 7800000, 55, 15, 'Xiaomi - Gaming phone'),
(3, 'OPPO Reno11 5G 256GB', 'OPR11256', 10490000, 9200000, 60, 15, 'OPPO - AMOLED 120Hz'),
(3, 'OPPO A79 5G 128GB', 'OPA79128', 6990000, 6000000, 80, 20, 'OPPO - Sạc nhanh 33W'),
(3, 'Vivo V30 5G 256GB', 'VVV30256', 9990000, 8800000, 55, 15, 'Vivo - ZEISS Portrait'),
(3, 'Vivo Y36 128GB', 'VVY36128', 4990000, 4200000, 100, 30, 'Vivo - Pin 5000mAh'),
(3, 'Realme 12 Pro+ 5G 256GB', 'RM12PP256', 10990000, 9800000, 45, 10, 'Realme - Periscope Zoom'),
(3, 'Realme C67 128GB', 'RMC67128', 4490000, 3800000, 90, 25, 'Realme - Camera 108MP'),
(3, 'Nokia G42 5G 128GB', 'NKG42128', 5990000, 5200000, 50, 15, 'Nokia - 3 năm cập nhật'),
(3, 'Nokia C32 64GB', 'NKC3264', 2490000, 2000000, 80, 25, 'Nokia - Giá rẻ bền bỉ'),
(3, 'OnePlus Nord CE4 128GB', 'OPNCE4128', 7990000, 7000000, 50, 15, 'OnePlus - AMOLED 120Hz'),

-- Máy tính bảng (category_id = 4)
(4, 'iPad Pro M4 11 inch 256GB', 'IPDPROM4256', 28990000, 27000000, 25, 5, 'Apple - Chip M4, Liquid Retina'),
(4, 'iPad Air M2 11 inch 128GB', 'IPDAIRM2128', 18990000, 17500000, 35, 10, 'Apple - Chip M2, Touch ID'),
(4, 'iPad Gen 10 64GB', 'IPD10G64', 10990000, 9800000, 50, 15, 'Apple - Chip A14, USB-C'),
(4, 'Samsung Galaxy Tab S9 Ultra', 'SSTABS9U', 27990000, 26000000, 20, 5, 'Samsung - Snapdragon 8 Gen 2'),
(4, 'Samsung Galaxy Tab S9 FE', 'SSTABS9FE', 12990000, 11500000, 40, 10, 'Samsung - Exynos 1380'),
(4, 'Xiaomi Pad 6 128GB', 'XMPAD6128', 8490000, 7500000, 45, 10, 'Xiaomi - Snapdragon 870'),

-- Đồng hồ thông minh (category_id = 5)
(5, 'Apple Watch Series 9 45mm', 'AWS945', 12990000, 11500000, 40, 10, 'Apple - Chip S9, Double Tap'),
(5, 'Apple Watch Ultra 2 49mm', 'AWULTRA2', 23990000, 22000000, 20, 5, 'Apple - Titanium, Action Button'),
(5, 'Apple Watch SE 2 40mm', 'AWSE240', 7490000, 6500000, 50, 15, 'Apple - Chip S8'),
(5, 'Samsung Galaxy Watch 6 44mm', 'SSGW644', 7990000, 7000000, 45, 10, 'Samsung - Exynos W930'),
(5, 'Samsung Galaxy Watch Ultra', 'SSGWULTRA', 14990000, 13500000, 25, 5, 'Samsung - Titanium, 100m WR'),
(5, 'Xiaomi Watch S3', 'XMWS3', 3490000, 3000000, 60, 15, 'Xiaomi - AMOLED, GPS'),
(5, 'Garmin Venu 3', 'GARMVENU3', 11990000, 10500000, 30, 10, 'Garmin - Theo dõi sức khỏe'),

-- Tai nghe (category_id = 6)
(6, 'AirPods Pro 2 USB-C', 'APP2USBC', 6790000, 6000000, 70, 20, 'Apple - ANC, Spatial Audio'),
(6, 'AirPods 3', 'AP3', 4990000, 4300000, 60, 15, 'Apple - Spatial Audio'),
(6, 'Samsung Galaxy Buds3 Pro', 'SSGB3PRO', 5490000, 4800000, 50, 15, 'Samsung - ANC, 360 Audio'),
(6, 'Samsung Galaxy Buds FE', 'SSGBFE', 2490000, 2100000, 80, 20, 'Samsung - ANC giá tốt'),
(6, 'Sony WF-1000XM5', 'SONYWF5', 6990000, 6200000, 35, 10, 'Sony - ANC hàng đầu'),
(6, 'Sony WH-1000XM5', 'SONYWH5', 8490000, 7500000, 30, 10, 'Sony - Headphone ANC'),
(6, 'Xiaomi Buds 4 Pro', 'XMB4PRO', 2990000, 2500000, 70, 20, 'Xiaomi - ANC, LDAC'),
(6, 'JBL Tour Pro 2', 'JBLTP2', 5490000, 4800000, 40, 10, 'JBL - Smart case màn hình'),

-- Sạc và cáp (category_id = 7)
(7, 'Củ sạc Apple 20W USB-C', 'APCHARGER20', 590000, 450000, 150, 50, 'Apple - Sạc nhanh iPhone'),
(7, 'Củ sạc Apple 35W Dual USB-C', 'APCHARGER35', 1490000, 1200000, 80, 30, 'Apple - Sạc 2 thiết bị'),
(7, 'Cáp USB-C to Lightning 1m', 'APCABLE1M', 590000, 450000, 200, 50, 'Apple - Cáp chính hãng'),
(7, 'Củ sạc Samsung 25W', 'SSCHARGER25', 490000, 380000, 150, 50, 'Samsung - Super Fast Charging'),
(7, 'Củ sạc Samsung 45W', 'SSCHARGER45', 890000, 720000, 100, 30, 'Samsung - Super Fast Charging 2.0'),
(7, 'Sạc dự phòng Anker 10000mAh', 'ANKER10K', 790000, 650000, 100, 30, 'Anker - PowerCore, PD 20W'),
(7, 'Sạc dự phòng Anker 20000mAh', 'ANKER20K', 1190000, 980000, 80, 25, 'Anker - PowerCore, PD 22.5W'),
(7, 'Sạc dự phòng Xiaomi 20000mAh', 'XMPB20K', 590000, 480000, 120, 40, 'Xiaomi - 22.5W Fast Charge'),

-- Ốp lưng và bao da (category_id = 8)
(8, 'Ốp lưng iPhone 15 Pro Max Silicone', 'OPIP15PMSL', 390000, 280000, 200, 50, 'Apple - Silicone MagSafe'),
(8, 'Ốp lưng iPhone 15 Pro Max Clear', 'OPIP15PMCL', 290000, 200000, 250, 60, 'Apple - Clear Case MagSafe'),
(8, 'Ốp lưng Samsung S24 Ultra Clear', 'OPSS24UCL', 290000, 200000, 200, 50, 'Samsung - Clear Standing Cover'),
(8, 'Ốp lưng Samsung S24 Ultra Silicone', 'OPSS24USL', 390000, 280000, 180, 50, 'Samsung - Silicone Case'),
(8, 'Bao da iPad Pro 11 inch', 'BDIPDPRO11', 990000, 800000, 60, 20, 'Apple - Smart Folio'),
(8, 'Bao da Samsung Tab S9', 'BDSSTABS9', 790000, 650000, 50, 15, 'Samsung - Book Cover'),

-- Phụ kiện khác (category_id = 9)
(9, 'Kính cường lực iPhone 15 Pro Max', 'KLIP15PM', 190000, 120000, 300, 100, 'Mipow - 9H Hardness'),
(9, 'Kính cường lực Samsung S24 Ultra', 'KLSS24U', 190000, 120000, 250, 80, 'Mipow - Full màn hình'),
(9, 'Apple Pencil Gen 2', 'APPENCIL2', 3790000, 3400000, 40, 10, 'Apple - Magnetic, Wireless'),
(9, 'Apple Pencil USB-C', 'APPENCILUSBC', 2390000, 2100000, 50, 15, 'Apple - USB-C charging'),
(9, 'Samsung S Pen Fold Edition', 'SSSPENFE', 1490000, 1200000, 35, 10, 'Samsung - Fold5 compatible'),
(9, 'Giá đỡ điện thoại MagSafe', 'GDDTMS', 390000, 280000, 100, 30, 'Belkin - MagSafe Mount'),
(9, 'Gậy selfie Bluetooth', 'GAYSELFIE', 290000, 200000, 120, 40, 'Tripod - Kèm remote');

-- 6. Customers
INSERT INTO `customers` (`name`, `phone`, `email`, `address`) VALUES
('Nguyễn Văn A', '0912345678', 'nguyenvana@gmail.com', '123 Nguyễn Trãi'),
('Trần Thị B', '0987654321', 'tranthib@gmail.com', '456 Lê Lợi');

COMMIT;