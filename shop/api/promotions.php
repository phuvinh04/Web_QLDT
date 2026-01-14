<?php
/**
 * API Khuyến mãi - Kiểm tra và áp dụng khuyến mãi cho đơn hàng
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_available':
        // Lấy danh sách khuyến mãi đang hoạt động
        getAvailablePromotions($pdo);
        break;
    
    case 'apply':
        // Áp dụng khuyến mãi cho đơn hàng
        applyPromotion($pdo);
        break;
    
    case 'calculate':
        // Tính toán giảm giá cho giỏ hàng
        calculateDiscount($pdo);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

/**
 * Lấy danh sách khuyến mãi đang hoạt động
 * Chỉ lấy khuyến mãi áp dụng cho tất cả sản phẩm (product_id = NULL)
 * hoặc khuyến mãi cho sản phẩm có trong giỏ hàng
 */
function getAvailablePromotions($pdo) {
    $today = date('Y-m-d');
    
    // Lấy cart_data từ request (nếu có)
    $input = json_decode(file_get_contents('php://input'), true);
    $cart_data = $input['cart_data'] ?? [];
    
    // Nếu có giỏ hàng, lấy danh sách product_id
    $product_ids = !empty($cart_data) ? array_keys($cart_data) : [];
    
    if (!empty($product_ids)) {
        // Có giỏ hàng: Lấy khuyến mãi cho tất cả SP hoặc SP trong giỏ
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT p.*, pr.name as product_name 
            FROM promotions p
            LEFT JOIN products pr ON p.product_id = pr.id
            WHERE p.active = 1 
            AND (p.start_date IS NULL OR p.start_date <= ?)
            AND (p.end_date IS NULL OR p.end_date >= ?)
            AND (p.product_id IS NULL OR p.product_id IN ($placeholders))
            ORDER BY p.priority DESC, p.discount_value DESC
        ");
        $params = array_merge([$today, $today], $product_ids);
        $stmt->execute($params);
    } else {
        // Không có giỏ hàng: Chỉ lấy khuyến mãi cho tất cả SP
        $stmt = $pdo->prepare("
            SELECT p.*, pr.name as product_name 
            FROM promotions p
            LEFT JOIN products pr ON p.product_id = pr.id
            WHERE p.active = 1 
            AND (p.start_date IS NULL OR p.start_date <= ?)
            AND (p.end_date IS NULL OR p.end_date >= ?)
            AND p.product_id IS NULL
            ORDER BY p.priority DESC, p.discount_value DESC
        ");
        $stmt->execute([$today, $today]);
    }
    
    $promotions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'promotions' => $promotions
    ]);
}

/**
 * Tính toán giảm giá cho giỏ hàng
 */
function calculateDiscount($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $cart_data = $input['cart_data'] ?? [];
    $promotion_id = $input['promotion_id'] ?? null;
    
    if (empty($cart_data)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        return;
    }
    
    // Lấy thông tin sản phẩm trong giỏ
    $product_ids = array_keys($cart_data);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();
    
    // Tính tổng tiền
    $subtotal = 0;
    $cart_items = [];
    foreach ($products as $product) {
        $qty = (int)($cart_data[$product['id']] ?? 0);
        if ($qty > 0) {
            $subtotal += $product['price'] * $qty;
            $cart_items[$product['id']] = [
                'product' => $product,
                'quantity' => $qty,
                'total' => $product['price'] * $qty
            ];
        }
    }
    
    $discount = 0;
    $applied_promotion = null;
    
    if ($promotion_id) {
        // Áp dụng khuyến mãi cụ thể
        $result = applySpecificPromotion($pdo, $promotion_id, $subtotal, $cart_items);
        $discount = $result['discount'];
        $applied_promotion = $result['promotion'];
    } else {
        // Tự động tìm khuyến mãi tốt nhất
        $result = findBestPromotion($pdo, $subtotal, $cart_items);
        $discount = $result['discount'];
        $applied_promotion = $result['promotion'];
    }
    
    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'final_total' => $subtotal - $discount,
        'applied_promotion' => $applied_promotion
    ]);
}

/**
 * Áp dụng khuyến mãi cụ thể
 */
function applySpecificPromotion($pdo, $promotion_id, $subtotal, $cart_items) {
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT * FROM promotions 
        WHERE id = ? 
        AND active = 1 
        AND (start_date IS NULL OR start_date <= ?)
        AND (end_date IS NULL OR end_date >= ?)
    ");
    $stmt->execute([$promotion_id, $today, $today]);
    $promotion = $stmt->fetch();
    
    if (!$promotion) {
        return ['discount' => 0, 'promotion' => null, 'error' => 'Khuyến mãi không hợp lệ hoặc đã hết hạn'];
    }
    
    // Kiểm tra đơn tối thiểu
    if ($promotion['min_amount'] > 0 && $subtotal < $promotion['min_amount']) {
        return [
            'discount' => 0, 
            'promotion' => null, 
            'error' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($promotion['min_amount']) . 'đ'
        ];
    }
    
    // Tính giảm giá
    $discount = calculatePromotionDiscount($promotion, $subtotal, $cart_items);
    
    return ['discount' => $discount, 'promotion' => $promotion];
}

/**
 * Tìm khuyến mãi tốt nhất cho đơn hàng
 */
function findBestPromotion($pdo, $subtotal, $cart_items) {
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT * FROM promotions 
        WHERE active = 1 
        AND (start_date IS NULL OR start_date <= ?)
        AND (end_date IS NULL OR end_date >= ?)
        AND (min_amount IS NULL OR min_amount <= ?)
        ORDER BY priority DESC
    ");
    $stmt->execute([$today, $today, $subtotal]);
    $promotions = $stmt->fetchAll();
    
    $best_discount = 0;
    $best_promotion = null;
    
    foreach ($promotions as $promotion) {
        $discount = calculatePromotionDiscount($promotion, $subtotal, $cart_items);
        if ($discount > $best_discount) {
            $best_discount = $discount;
            $best_promotion = $promotion;
        }
    }
    
    return ['discount' => $best_discount, 'promotion' => $best_promotion];
}

/**
 * Tính giá trị giảm giá của một khuyến mãi
 */
function calculatePromotionDiscount($promotion, $subtotal, $cart_items) {
    $discount = 0;
    
    // Nếu khuyến mãi áp dụng cho sản phẩm cụ thể
    if ($promotion['product_id']) {
        if (isset($cart_items[$promotion['product_id']])) {
            $item_total = $cart_items[$promotion['product_id']]['total'];
            if ($promotion['discount_type'] === 'percent') {
                $discount = $item_total * ($promotion['discount_value'] / 100);
            } else {
                $discount = min($promotion['discount_value'], $item_total);
            }
        }
    } else {
        // Áp dụng cho toàn bộ đơn hàng
        if ($promotion['discount_type'] === 'percent') {
            $discount = $subtotal * ($promotion['discount_value'] / 100);
        } else {
            $discount = min($promotion['discount_value'], $subtotal);
        }
    }
    
    return round($discount);
}

/**
 * Áp dụng khuyến mãi (trả về thông tin để lưu vào đơn hàng)
 */
function applyPromotion($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $promotion_id = $input['promotion_id'] ?? null;
    $cart_data = $input['cart_data'] ?? [];
    
    if (!$promotion_id) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn khuyến mãi']);
        return;
    }
    
    if (empty($cart_data)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        return;
    }
    
    // Lấy thông tin sản phẩm
    $product_ids = array_keys($cart_data);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();
    
    $subtotal = 0;
    $cart_items = [];
    foreach ($products as $product) {
        $qty = (int)($cart_data[$product['id']] ?? 0);
        if ($qty > 0) {
            $subtotal += $product['price'] * $qty;
            $cart_items[$product['id']] = [
                'product' => $product,
                'quantity' => $qty,
                'total' => $product['price'] * $qty
            ];
        }
    }
    
    $result = applySpecificPromotion($pdo, $promotion_id, $subtotal, $cart_items);
    
    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => $result['error']]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'discount' => $result['discount'],
        'final_total' => $subtotal - $result['discount'],
        'promotion' => $result['promotion']
    ]);
}
