<?php
// Checkout Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Thanh toán - PhoneStore";
$base_url = "../";

require_once '../config.php';

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
    die("Lỗi kết nối database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_query = "
    SELECT c.*, p.name, p.price, p.image, p.quantity as stock_quantity, cat.name as category_name
    FROM shopping_cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ? AND p.status = 'active'
    ORDER BY c.added_at DESC
";
$cart_stmt = $pdo->prepare($cart_query);
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll();

// Redirect if cart is empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 500000 ? 0 : 30000;
$tax = $subtotal * 0.1;
$total = $subtotal + $shipping + $tax;


// Get user info
$user_query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    
    if (empty($full_name) || empty($phone) || empty($address)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin giao hàng';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $order_code = 'HD' . date('YmdHis') . rand(100, 999);
            
            $order_stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, subtotal, tax, total_amount, payment_method, notes, status)
                VALUES (?, ?, ?, ?, ?, 'cod', ?, 'pending')
            ");
            $order_stmt->execute([
                $order_code, $user_id, $subtotal, $tax, $total,
                "Tên: $full_name\nSĐT: $phone\nĐịa chỉ: $address\nGhi chú: $note"
            ]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items (subtotal is auto-generated column)
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $item) {
                $item_stmt->execute([
                    $order_id, $item['product_id'], $item['quantity'], $item['price']
                ]);
                
                // Update product stock
                $update_stock = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $update_stock->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $clear_cart = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
            $clear_cart->execute([$user_id]);
            
            $pdo->commit();
            
            // Redirect to success page
            header("Location: order_success.php?order=" . $order_code);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/shop.css">
</head>
<body>
    <?php include 'components/shop_header.php'; ?>

    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Giỏ hàng</a></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-credit-card"></i> Thanh toán</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-lg-7">
                    <!-- Shipping Info -->
                    <div class="checkout-section mb-4">
                        <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Thông tin giao hàng</h5>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Họ và tên *</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại *</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ giao hàng *</label>
                                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú cho đơn hàng..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h5 class="mb-3"><i class="bi bi-wallet2"></i> Phương thức thanh toán</h5>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-coin text-success me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng (COD)</strong>
                                        <p class="text-muted mb-0 small">Bạn sẽ thanh toán bằng tiền mặt khi nhận được hàng</p>
                                    </div>
                                </div>
                                <input type="hidden" name="payment_method" value="cod">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <!-- Order Summary -->
                    <div class="cart-summary">
                        <h5><i class="bi bi-receipt"></i> Đơn hàng của bạn</h5>
                        
                        <div class="order-items mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <span class="fw-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <small class="text-muted d-block">x<?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span><?php echo $shipping == 0 ? '<span class="text-success">Miễn phí</span>' : number_format($shipping, 0, ',', '.') . '₫'; ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Thuế (10%):</span>
                            <span><?php echo number_format($tax, 0, ',', '.'); ?>₫</span>
                        </div>
                        <hr>
                        <div class="summary-row total">
                            <span><strong>Tổng cộng:</strong></span>
                            <span class="text-danger"><strong><?php echo number_format($total, 0, ',', '.'); ?>₫</strong></span>
                        </div>
                        
                        <div class="checkout-buttons mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                                <i class="bi bi-check-circle"></i> Đặt hàng
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'components/shop_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
