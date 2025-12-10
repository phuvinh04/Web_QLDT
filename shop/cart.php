<?php
// Shopping Cart Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Giỏ hàng - PhoneStore";
$base_url = "../";

// Include config
require_once '../config.php';

// Kết nối database
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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Check if product exists and is active
        $product_check = $pdo->prepare("SELECT quantity FROM products WHERE id = ? AND status = 'active'");
        $product_check->execute([$product_id]);
        $product = $product_check->fetch();
        
        if ($product && $quantity <= $product['quantity']) {
            // Add or update cart item
            $cart_stmt = $pdo->prepare("
                INSERT INTO shopping_cart (user_id, product_id, quantity) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ");
            $cart_stmt->execute([$user_id, $product_id, $quantity]);
            $success_message = "Đã thêm sản phẩm vào giỏ hàng!";
        }
    } elseif ($action === 'update') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0) {
            $update_stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update_stmt->execute([$quantity, $user_id, $product_id]);
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?");
            $delete_stmt->execute([$user_id, $product_id]);
        }
    } elseif ($action === 'remove') {
        $product_id = (int)$_POST['product_id'];
        $delete_stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?");
        $delete_stmt->execute([$user_id, $product_id]);
    }
}

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

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal >= 500000 ? 0 : 30000; // Free shipping over 500k
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $shipping + $tax;
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

    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Giỏ hàng</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi bi-cart3"></i> Giỏ hàng của bạn
            </h1>
            <p class="text-muted"><?php echo count($cart_items); ?> sản phẩm trong giỏ hàng</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 5rem; color: var(--text-muted);"></i>
                    <h3 class="mt-4">Giỏ hàng trống</h3>
                    <p class="text-muted mb-4">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Content -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item-card">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="cart-item-image">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
                                                <div class="no-image">
                                                    <i class="bi bi-phone"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="cart-item-info">
                                            <h6 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <p class="cart-item-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                            <p class="cart-item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="quantity-controls">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity(this)">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="1" max="<?php echo $item['stock_quantity']; ?>"
                                                           onchange="this.form.submit()">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity(this)">+</button>
                                                </div>
                                            </form>
                                            <small class="text-muted">Còn <?php echo $item['stock_quantity']; ?> sản phẩm</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="cart-item-total">
                                            <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5>Tóm tắt đơn hàng</h5>
                        
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>
                                <?php if ($shipping == 0): ?>
                                    <span class="text-success">Miễn phí</span>
                                <?php else: ?>
                                    <?php echo number_format($shipping, 0, ',', '.'); ?>₫
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Thuế (10%):</span>
                            <span><?php echo number_format($tax, 0, ',', '.'); ?>₫</span>
                        </div>
                        
                        <hr>
                        
                        <div class="summary-row total">
                            <span><strong>Tổng cộng:</strong></span>
                            <span><strong><?php echo number_format($total, 0, ',', '.'); ?>₫</strong></span>
                        </div>
                        
                        <div class="checkout-buttons">
                            <a href="checkout.php" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-credit-card"></i> Thanh toán
                            </a>
                            <a href="products.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                        
                        <?php if ($subtotal < 500000): ?>
                            <div class="shipping-notice">
                                <i class="bi bi-info-circle"></i>
                                Mua thêm <?php echo number_format(500000 - $subtotal, 0, ',', '.'); ?>₫ để được miễn phí vận chuyển!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function increaseQuantity(btn) {
            const input = btn.parentElement.querySelector('input[name="quantity"]');
            const max = parseInt(input.getAttribute('max'));
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
                input.form.submit();
            }
        }
        
        function decreaseQuantity(btn) {
            const input = btn.parentElement.querySelector('input[name="quantity"]');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
                input.form.submit();
            }
        }
    </script>
</body>
</html>