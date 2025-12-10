<?php
/**
 * API Quản lý Sản phẩm
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
        case 'GET': handleGet($db); break;
        case 'POST':
            if (!$canEdit) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Không có quyền']); exit; }
            handlePost($db); break;
        case 'PUT':
            if (!$canEdit) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Không có quyền']); exit; }
            handlePut($db); break;
        case 'DELETE':
            if (!$canEdit) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Không có quyền']); exit; }
            handleDelete($db); break;
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
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        }
        return;
    }
    
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $offset = ($page - 1) * $limit;
    
    $where = ["1=1"];
    $params = [];
    
    if (!empty($_GET['category_id'])) { $where[] = "p.category_id = ?"; $params[] = (int)$_GET['category_id']; }
    if (!empty($_GET['status'])) { $where[] = "p.status = ?"; $params[] = $_GET['status']; }
    if (isset($_GET['stock']) && $_GET['stock'] === 'low') { $where[] = "p.quantity <= p.min_quantity"; }
    if (!empty($_GET['search'])) {
        $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
        $search = '%' . $_GET['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereClause = implode(' AND ', $where);
    $orderBy = "p.created_at DESC";
    if (!empty($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'price_asc': $orderBy = "p.price ASC"; break;
            case 'price_desc': $orderBy = "p.price DESC"; break;
            case 'name_asc': $orderBy = "p.name ASC"; break;
        }
    }
    
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'total_pages' => ceil($total / $limit)]
    ]);
}

function handlePost($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug: log input
    error_log("POST input: " . print_r($input, true));
    
    // Validate required fields
    if (empty($input['name']) || trim($input['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu tên sản phẩm']);
        return;
    }
    if (empty($input['sku']) || trim($input['sku']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu mã SKU']);
        return;
    }
    if (!isset($input['category_id']) || $input['category_id'] === '' || $input['category_id'] === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu danh mục']);
        return;
    }
    if (!isset($input['price']) || $input['price'] === '' || $input['price'] === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu giá bán']);
        return;
    }
    
    $checkStmt = $db->prepare("SELECT id FROM products WHERE sku = ?");
    $checkStmt->execute([$input['sku']]);
    if ($checkStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mã SKU đã tồn tại']);
        return;
    }
    
    $sql = "INSERT INTO products (category_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        (int)$input['category_id'],
        trim($input['name']),
        trim($input['sku']),
        $input['description'] ?? null,
        (float)$input['price'],
        isset($input['cost']) ? (float)$input['cost'] : null,
        (int)($input['quantity'] ?? 0),
        (int)($input['min_quantity'] ?? 10),
        $input['image'] ?? null,
        $input['status'] ?? 'active'
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công', 'data' => ['id' => $db->lastInsertId()]]);
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm']);
        return;
    }
    
    $id = (int)$input['id'];
    
    $checkStmt = $db->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        return;
    }
    
    if (!empty($input['sku'])) {
        $checkSku = $db->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $checkSku->execute([$input['sku'], $id]);
        if ($checkSku->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã SKU đã tồn tại']);
            return;
        }
    }
    
    $fields = [];
    $params = [];
    $allowedFields = ['category_id', 'name', 'sku', 'description', 'price', 'cost', 'quantity', 'min_quantity', 'image', 'status'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $fields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có dữ liệu cập nhật']);
        return;
    }
    
    $params[] = $id;
    $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
}

function handleDelete($db) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm']);
        return;
    }
    
    $checkOrders = $db->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $checkOrders->execute([$id]);
    if ($checkOrders->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm đã có đơn hàng']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
    }
}
