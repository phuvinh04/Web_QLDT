<?php
/**
 * API Thương hiệu
 * GET (list/detail), POST (create), PUT (update), DELETE
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
$method = $_SERVER['REQUEST_METHOD'];
$role_id = $_SESSION['role_id'] ?? 0;
$canEdit = in_array($role_id, [1, 2]);

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            if (!$canEdit) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                exit;
            }
            handlePost($db);
            break;
        case 'PUT':
            if (!$canEdit) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                exit;
            }
            handlePut($db);
            break;
        case 'DELETE':
            if (!$canEdit) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                exit;
            }
            handleDelete($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không hỗ trợ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}

function handleGet($db) {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([(int)$_GET['id']]);
        $brand = $stmt->fetch();
        echo $brand 
            ? json_encode(['success' => true, 'data' => $brand]) 
            : json_encode(['success' => false, 'message' => 'Không tìm thấy']);
        exit;
    }
    
    $sql = "SELECT b.*, COUNT(p.id) as product_count 
            FROM brands b 
            LEFT JOIN products p ON b.id = p.brand_id 
            GROUP BY b.id 
            ORDER BY b.name ASC";
    $stmt = $db->query($sql);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}

function handlePost($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên thương hiệu không được để trống']);
        exit;
    }
    
    $name = trim($input['name']);
    $description = trim($input['description'] ?? '');
    
    // Kiểm tra trùng tên
    $stmt = $db->prepare("SELECT id FROM brands WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên thương hiệu đã tồn tại']);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thêm thương hiệu thành công',
        'data' => ['id' => $db->lastInsertId(), 'name' => $name]
    ]);
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        exit;
    }
    
    $id = (int)$input['id'];
    $name = trim($input['name']);
    $description = trim($input['description'] ?? '');
    
    // Kiểm tra trùng tên
    $stmt = $db->prepare("SELECT id FROM brands WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên thương hiệu đã tồn tại']);
        exit;
    }
    
    $stmt = $db->prepare("UPDATE brands SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật thương hiệu thành công']);
}

function handleDelete($db) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID thương hiệu']);
        exit;
    }
    
    // Xóa brand_id trong products trước
    $db->prepare("UPDATE products SET brand_id = NULL WHERE brand_id = ?")->execute([$id]);
    
    $stmt = $db->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa thương hiệu thành công']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thương hiệu']);
    }
}
