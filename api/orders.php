<?php
/**
 * API Quản lý Đơn hàng
 * Endpoints:
 *   GET    /api/orders.php?id=X  - Lấy chi tiết đơn hàng
 *   POST   /api/orders.php       - Tạo đơn hàng mới
 *   PUT    /api/orders.php       - Cập nhật trạng thái đơn hàng
 *   DELETE /api/orders.php       - Xóa đơn hàng
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handlePost($db);
            break;
        case 'PUT':
            handlePut($db);
            break;
        case 'DELETE':
            handleDelete($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}

/**
 * GET - Lấy chi tiết đơn hàng
 */
function handleGet($db) {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']);
        return;
    }

    $id = (int)$_GET['id'];
    
    // Lấy thông tin đơn hàng
    $stmt = $db->prepare("
        SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
               u.full_name as user_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        return;
    }

    // Lấy chi tiết sản phẩm trong đơn
    $itemsStmt = $db->prepare("
        SELECT oi.*, p.name as product_name, p.sku, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$id]);
    $order['items'] = $itemsStmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $order]);
}

/**
 * POST - Tạo hoặc cập nhật đơn hàng
 */
function handlePost($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['items']) || !is_array($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 sản phẩm']);
        return;
    }

    $db->beginTransaction();

    try {
        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $discount = floatval($data['discount'] ?? 0);
        $notes = $data['notes'] ?? '';
        $userId = $_SESSION['user_id'];

        // Tính tổng tiền
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += floatval($item['unit_price']) * intval($item['quantity']);
        }
        $totalAmount = $subtotal - $discount;

        if (!empty($data['id'])) {
            // === CẬP NHẬT ĐƠN HÀNG ===
            $orderId = (int)$data['id'];

            // Kiểm tra đơn hàng tồn tại và còn pending
            $checkStmt = $db->prepare("SELECT status FROM orders WHERE id = ?");
            $checkStmt->execute([$orderId]);
            $existingOrder = $checkStmt->fetch();

            if (!$existingOrder) {
                throw new Exception('Không tìm thấy đơn hàng');
            }

            if ($existingOrder['status'] !== 'pending') {
                throw new Exception('Chỉ có thể sửa đơn hàng đang chờ xử lý');
            }

            // Hoàn lại số lượng tồn kho cũ
            $oldItemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $oldItemsStmt->execute([$orderId]);
            $oldItems = $oldItemsStmt->fetchAll();

            foreach ($oldItems as $oldItem) {
                $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                   ->execute([$oldItem['quantity'], $oldItem['product_id']]);
            }

            // Xóa items cũ
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);

            // Cập nhật đơn hàng
            $updateStmt = $db->prepare("
                UPDATE orders SET 
                    customer_id = ?, payment_method = ?, subtotal = ?, 
                    discount = ?, total_amount = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$customerId, $paymentMethod, $subtotal, $discount, $totalAmount, $notes, $orderId]);

        } else {
            // === TẠO ĐƠN HÀNG MỚI ===
            
            // Tạo mã đơn hàng
            $orderNumber = 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Xác định trạng thái dựa trên phương thức thanh toán:
            // - COD: Chờ xử lý (pending) vì còn phải ship và thu tiền
            // - Tiền mặt/Thẻ/Chuyển khoản: Hoàn thành ngay (completed)
            $status = ($paymentMethod === 'cod') ? 'pending' : 'completed';

            $insertStmt = $db->prepare("
                INSERT INTO orders (order_number, customer_id, user_id, subtotal, discount, total_amount, payment_method, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $insertStmt->execute([$orderNumber, $customerId, $userId, $subtotal, $discount, $totalAmount, $paymentMethod, $status, $notes]);
            $orderId = $db->lastInsertId();
        }

        // Thêm items mới và trừ tồn kho
        $itemStmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($data['items'] as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $unitPrice = floatval($item['unit_price']);

            // Kiểm tra tồn kho
            $stockStmt = $db->prepare("SELECT quantity, name FROM products WHERE id = ?");
            $stockStmt->execute([$productId]);
            $product = $stockStmt->fetch();

            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại");
            }

            if ($product['quantity'] < $quantity) {
                throw new Exception("Sản phẩm '{$product['name']}' chỉ còn {$product['quantity']} sản phẩm");
            }

            // Thêm item
            $itemStmt->execute([$orderId, $productId, $quantity, $unitPrice]);

            // Trừ tồn kho
            $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
               ->execute([$quantity, $productId]);
        }

        // Cập nhật thông tin khách hàng nếu có
        if ($customerId) {
            $db->prepare("
                UPDATE customers SET 
                    total_purchases = total_purchases + ?,
                    purchase_count = purchase_count + 1,
                    updated_at = NOW()
                WHERE id = ?
            ")->execute([$totalAmount, $customerId]);
        }

        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => !empty($data['id']) ? 'Cập nhật đơn hàng thành công' : 'Tạo đơn hàng thành công',
            'order_id' => $orderId
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * PUT - Cập nhật trạng thái đơn hàng
 */
function handlePut($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        return;
    }

    $id = (int)$data['id'];
    $status = $data['status'];
    $validStatuses = ['pending', 'completed', 'cancelled', 'refunded'];

    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        return;
    }

    $db->beginTransaction();

    try {
        // Lấy thông tin đơn hàng hiện tại
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception('Không tìm thấy đơn hàng');
        }

        // Nếu hủy đơn hoặc hoàn tiền -> hoàn lại tồn kho
        if (($status === 'cancelled' || $status === 'refunded') && $order['status'] === 'pending') {
            $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                   ->execute([$item['quantity'], $item['product_id']]);
            }

            // Hoàn lại thông tin khách hàng
            if ($order['customer_id']) {
                $db->prepare("
                    UPDATE customers SET 
                        total_purchases = total_purchases - ?,
                        purchase_count = purchase_count - 1
                    WHERE id = ?
                ")->execute([$order['total_amount'], $order['customer_id']]);
            }
        }

        // Cập nhật trạng thái
        $updateStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $id]);

        $db->commit();

        $statusMessages = [
            'completed' => 'Đơn hàng đã hoàn thành',
            'cancelled' => 'Đơn hàng đã bị hủy',
            'refunded' => 'Đơn hàng đã hoàn tiền',
            'pending' => 'Đơn hàng đang chờ xử lý'
        ];

        echo json_encode(['success' => true, 'message' => $statusMessages[$status]]);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * DELETE - Xóa đơn hàng
 */
function handleDelete($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']);
        return;
    }

    $id = (int)$data['id'];

    // Chỉ admin mới được xóa
    if ($_SESSION['role_id'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa đơn hàng']);
        return;
    }

    $db->beginTransaction();

    try {
        // Lấy thông tin đơn hàng
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception('Không tìm thấy đơn hàng');
        }

        // Hoàn lại tồn kho nếu đơn chưa hủy
        if ($order['status'] !== 'cancelled' && $order['status'] !== 'refunded') {
            $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                   ->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // Xóa items (cascade sẽ tự động xóa nhưng ta làm rõ ràng)
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);
        
        // Xóa đơn hàng
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Đã xóa đơn hàng']);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
