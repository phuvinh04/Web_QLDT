<?php
header('Content-Type: application/json');

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

    $product_ids = isset($_GET['ids']) ? $_GET['ids'] : '';

    if (empty($product_ids)) {
        echo json_encode(['success' => false, 'message' => 'Không có sản phẩm nào']);
        exit;
    }

    // Validate and sanitize product IDs
    $ids = array_map('intval', explode(',', $product_ids));
    $ids = array_filter($ids, function($id) { return $id > 0; });

    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        exit;
    }

    // Tạo placeholders cho prepared statement
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Lấy thông tin sản phẩm
    $query = "SELECT p.id, p.name, p.price, p.image, p.quantity, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id IN ($placeholders) AND p.status = 'active'";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>