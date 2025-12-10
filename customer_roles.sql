-- Thêm role cho khách hàng
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(5, 'customer', 'Khách hàng - Mua sắm trực tuyến');

-- Cập nhật role mặc định cho user mới là customer
ALTER TABLE `users` MODIFY `role_id` INT NOT NULL DEFAULT 5 COMMENT 'Mặc định là khách hàng';

-- Tạo bảng customer_profiles để lưu thông tin bổ sung của khách hàng
CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `address` TEXT,
  `city` VARCHAR(100),
  `district` VARCHAR(100),
  `ward` VARCHAR(100),
  `postal_code` VARCHAR(20),
  `date_of_birth` DATE,
  `gender` ENUM('male', 'female', 'other'),
  `loyalty_points` INT DEFAULT 0,
  `total_orders` INT DEFAULT 0,
  `total_spent` DECIMAL(15,2) DEFAULT 0.00,
  `preferred_categories` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_profile` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông tin bổ sung của khách hàng';

-- Tạo bảng shopping_cart để lưu giỏ hàng của khách hàng
CREATE TABLE IF NOT EXISTS `shopping_cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_cart_item` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Giỏ hàng của khách hàng';

-- Tạo bảng wishlist
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_wishlist_item` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh sách yêu thích của khách hàng';

-- Cập nhật bảng orders để liên kết với users thay vì customers riêng biệt
ALTER TABLE `orders` ADD COLUMN `customer_user_id` INT AFTER `user_id`;
ALTER TABLE `orders` ADD FOREIGN KEY (`customer_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

COMMIT;