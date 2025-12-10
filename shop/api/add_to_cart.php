<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng',
        'redirect' => '../auth/login.php'
    ]);
    exit;
}

// Include config
require_once '../../config.php';

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

    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Thông tin sản phẩm không hợp lệ'
        ]);
        exit;
    }

    // Check if product exists and is active
    $product_check = $pdo->prepare("SELECT id, name, quantity, price FROM products WHERE id = ? AND status = 'active'");
    $product_check->execute([$product_id]);
    $product = $product_check->fetch();

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán'
        ]);
        exit;
    }

    if ($quantity > $product['quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng yêu cầu vượt quá tồn kho (còn ' . $product['quantity'] . ' sản phẩm)'
        ]);
        exit;
    }

    // Check if item already exists in cart
    $existing_check = $pdo->prepare("SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
    $existing_check->execute([$user_id, $product_id]);
    $existing = $existing_check->fetch();

    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;
        
        if ($new_quantity > $product['quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Tổng số lượng trong giỏ sẽ vượt quá tồn kho'
            ]);
            exit;
        }

        // Update existing cart item
        $update_stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        $update_stmt->execute([$new_quantity, $user_id, $product_id]);
        
        $message = 'Đã cập nhật số lượng sản phẩm trong giỏ hàng';
    } else {
        // Add new cart item
        $insert_stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->execute([$user_id, $product_id, $quantity]);
        
        $message = 'Đã thêm sản phẩm vào giỏ hàng';
    }

    // Get updated cart count
    $cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
    $cart_count_stmt->execute([$user_id]);
    $cart_count = $cart_count_stmt->fetch()['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $cart_count,
        'product_name' => $product['name']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>