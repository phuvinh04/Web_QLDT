<?php
// Wishlist API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
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

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            // Check if already in wishlist
            $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            
            if ($check->fetch()) {
                echo json_encode(['success' => true, 'message' => 'Sản phẩm đã có trong danh sách yêu thích', 'in_wishlist' => true]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích', 'in_wishlist' => true]);
            }
            break;
            
        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích', 'in_wishlist' => false]);
            break;
            
        case 'toggle':
            $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            
            if ($check->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích', 'in_wishlist' => false]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích', 'in_wishlist' => true]);
            }
            break;
            
        case 'check':
            $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'in_wishlist' => (bool)$check->fetch()]);
            break;
            
        case 'count':
            $count = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
            $count->execute([$user_id]);
            $result = $count->fetch();
            echo json_encode(['success' => true, 'count' => $result['total']]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
