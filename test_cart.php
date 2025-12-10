<?php
// Test script to check cart functionality
session_start();

// Simulate logged in user (use existing user ID)
$_SESSION['user_id'] = 1; // Admin user
$_SESSION['role_id'] = 1;
$_SESSION['full_name'] = 'Test User';

echo "Testing cart functionality...\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";

// Include config
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

    // Check products
    $products = $pdo->query("SELECT id, name, price, quantity FROM products WHERE status = 'active' LIMIT 3")->fetchAll();
    echo "\nAvailable products:\n";
    foreach ($products as $product) {
        echo "- ID: {$product['id']}, Name: {$product['name']}, Stock: {$product['quantity']}\n";
    }

    // Check cart table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'shopping_cart'")->fetchAll();
    if (empty($tables)) {
        echo "\n❌ shopping_cart table does not exist!\n";
        echo "Running setup script...\n";
        
        // Create shopping_cart table
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Created shopping_cart table\n";
    } else {
        echo "\n✓ shopping_cart table exists\n";
    }

    // Check current cart
    $cart_items = $pdo->prepare("SELECT * FROM shopping_cart WHERE user_id = ?");
    $cart_items->execute([$_SESSION['user_id']]);
    $items = $cart_items->fetchAll();
    
    echo "\nCurrent cart items: " . count($items) . "\n";
    foreach ($items as $item) {
        echo "- Product ID: {$item['product_id']}, Quantity: {$item['quantity']}\n";
    }

    echo "\n✅ Cart system is ready!\n";
    echo "You can now test adding products to cart on the website.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>