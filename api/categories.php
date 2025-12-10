<?php
/**
 * API Danh mục sản phẩm
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([(int)$_GET['id']]);
        $category = $stmt->fetch();
        echo $category ? json_encode(['success' => true, 'data' => $category]) : json_encode(['success' => false, 'message' => 'Không tìm thấy']);
        exit;
    }
    
    $sql = "SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC";
    $stmt = $db->query($sql);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method không hỗ trợ']);
}
