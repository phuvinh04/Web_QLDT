<?php
// Order Detail API
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once '../../config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']);
    exit;
}

// Get order (check ownership)
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->execute([$order_id, $user_id]);
$order = $order_stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    exit;
}

// Get order items
$items_stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
