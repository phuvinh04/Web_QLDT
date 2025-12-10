<?php
// Setup Customer Roles Script
require_once 'config.php';

try {
    // Kết nối database
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "Đang thiết lập role khách hàng...\n";

    // 1. Thêm role customer nếu chưa có
    $check_role = $pdo->query("SELECT COUNT(*) as count FROM roles WHERE id = 5")->fetch();
    if ($check_role['count'] == 0) {
        $pdo->exec("INSERT INTO `roles` (`id`, `name`, `description`) VALUES (5, 'customer', 'Khách hàng - Mua sắm trực tuyến')");
        echo "✓ Đã thêm role customer\n";
    } else {
        echo "✓ Role customer đã tồn tại\n";
    }

    // 2. Cập nhật role mặc định
    $pdo->exec("ALTER TABLE `users` MODIFY `role_id` INT NOT NULL DEFAULT 5 COMMENT 'Mặc định là khách hàng'");
    echo "✓ Đã cập nhật role mặc định\n";

    // 3. Tạo bảng customer_profiles
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông tin bổ sung của khách hàng'
    ");
    echo "✓ Đã tạo bảng customer_profiles\n";

    // 4. Tạo bảng shopping_cart
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Giỏ hàng của khách hàng'
    ");
    echo "✓ Đã tạo bảng shopping_cart\n";

    // 5. Tạo bảng wishlist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `wishlist` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
          UNIQUE KEY `unique_wishlist_item` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh sách yêu thích của khách hàng'
    ");
    echo "✓ Đã tạo bảng wishlist\n";

    // 6. Thêm cột customer_user_id vào orders nếu chưa có
    $columns = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_user_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `customer_user_id` INT AFTER `user_id`");
        $pdo->exec("ALTER TABLE `orders` ADD FOREIGN KEY (`customer_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        echo "✓ Đã thêm cột customer_user_id vào bảng orders\n";
    } else {
        echo "✓ Cột customer_user_id đã tồn tại\n";
    }

    echo "\n🎉 Thiết lập hoàn tất! Bây giờ bạn có thể:\n";
    echo "- Đăng ký tài khoản mới sẽ tự động là khách hàng\n";
    echo "- Khách hàng đăng nhập sẽ được chuyển về shop\n";
    echo "- Nhân viên đăng nhập sẽ được chuyển về dashboard\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
?>