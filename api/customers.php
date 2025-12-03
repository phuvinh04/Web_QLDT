<?php
/**
 * API Quản lý Khách hàng
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

// Admin, Manager, Sales có quyền quản lý khách hàng
$canView = in_array($role_id, [1, 2, 3]);
$canEdit = in_array($role_id, [1, 2, 3]);
$canDelete = in_array($role_id, [1, 2]);

if (!$canView) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

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
            if (!$canDelete) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Không có quyền']); exit; }
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
    // Lấy chi tiết 1 khách hàng
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            // Lấy lịch sử đơn hàng gần đây
            $ordersStmt = $db->prepare("
                SELECT id, order_number, total_amount, status, created_at 
                FROM orders 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $ordersStmt->execute([$id]);
            $customer['recent_orders'] = $ordersStmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $customer]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
        }
        return;
    }
    
    // Danh sách khách hàng với phân trang và filter
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 15;
    $offset = ($page - 1) * $limit;
    
    $where = ["1=1"];
    $params = [];
    
    // Filter theo trạng thái
    if (!empty($_GET['status'])) {
        $where[] = "status = ?";
        $params[] = $_GET['status'];
    }
    
    // Filter theo thành phố
    if (!empty($_GET['city'])) {
        $where[] = "city = ?";
        $params[] = $_GET['city'];
    }
    
    // Tìm kiếm theo tên, phone, email
    if (!empty($_GET['search'])) {
        $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $search = '%' . $_GET['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Sắp xếp
    $orderBy = "created_at DESC";
    if (!empty($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'name_asc': $orderBy = "name ASC"; break;
            case 'name_desc': $orderBy = "name DESC"; break;
            case 'purchases_desc': $orderBy = "total_purchases DESC"; break;
            case 'purchases_asc': $orderBy = "total_purchases ASC"; break;
            case 'orders_desc': $orderBy = "purchase_count DESC"; break;
            case 'points_desc': $orderBy = "loyalty_points DESC"; break;
            case 'oldest': $orderBy = "created_at ASC"; break;
        }
    }
    
    // Đếm tổng
    $countStmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // Lấy danh sách
    $sql = "SELECT * FROM customers WHERE $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
    
    // Thống kê tổng quan
    $statsStmt = $db->query("
        SELECT 
            COUNT(*) as total_customers,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_customers,
            SUM(total_purchases) as total_revenue,
            SUM(purchase_count) as total_orders
        FROM customers
    ");
    $stats = $statsStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'data' => $customers,
        'stats' => $stats,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function handlePost($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate
    if (empty($input['name']) || trim($input['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên khách hàng']);
        return;
    }
    
    // Kiểm tra phone unique nếu có
    if (!empty($input['phone'])) {
        $checkPhone = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $checkPhone->execute([$input['phone']]);
        if ($checkPhone->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã tồn tại']);
            return;
        }
    }
    
    // Kiểm tra email unique nếu có
    if (!empty($input['email'])) {
        $checkEmail = $db->prepare("SELECT id FROM customers WHERE email = ?");
        $checkEmail->execute([$input['email']]);
        if ($checkEmail->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
    }
    
    $sql = "INSERT INTO customers (name, phone, email, address, city, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        trim($input['name']),
        !empty($input['phone']) ? trim($input['phone']) : null,
        !empty($input['email']) ? trim($input['email']) : null,
        !empty($input['address']) ? trim($input['address']) : null,
        !empty($input['city']) ? trim($input['city']) : null,
        $input['status'] ?? 'active'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thêm khách hàng thành công',
        'data' => ['id' => $db->lastInsertId()]
    ]);
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID khách hàng']);
        return;
    }
    
    $id = (int)$input['id'];
    
    // Kiểm tra tồn tại
    $checkStmt = $db->prepare("SELECT id FROM customers WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
        return;
    }
    
    // Kiểm tra phone unique
    if (!empty($input['phone'])) {
        $checkPhone = $db->prepare("SELECT id FROM customers WHERE phone = ? AND id != ?");
        $checkPhone->execute([$input['phone'], $id]);
        if ($checkPhone->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã tồn tại']);
            return;
        }
    }
    
    // Kiểm tra email unique
    if (!empty($input['email'])) {
        $checkEmail = $db->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $checkEmail->execute([$input['email'], $id]);
        if ($checkEmail->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
    }
    
    $fields = [];
    $params = [];
    $allowedFields = ['name', 'phone', 'email', 'address', 'city', 'status', 'loyalty_points'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $fields[] = "$field = ?";
            $value = $input[$field];
            // Cho phép set null cho phone và email
            if (($field === 'phone' || $field === 'email') && $value === '') {
                $value = null;
            }
            $params[] = $value;
        }
    }
    
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có dữ liệu cập nhật']);
        return;
    }
    
    $params[] = $id;
    $sql = "UPDATE customers SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật khách hàng thành công']);
}

function handleDelete($db) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID khách hàng']);
        return;
    }
    
    // Kiểm tra có đơn hàng không
    $checkOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
    $checkOrders->execute([$id]);
    if ($checkOrders->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể xóa khách hàng đã có đơn hàng. Bạn có thể chuyển trạng thái sang "Ngừng hoạt động"']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa khách hàng thành công']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
    }
}
