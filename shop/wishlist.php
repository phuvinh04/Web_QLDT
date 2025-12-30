<?php
// Wishlist Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Sản phẩm yêu thích - PhoneStore";
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
$message = '';
$message_type = '';

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'remove') {
        $product_id = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $message = 'Đã xóa sản phẩm khỏi danh sách yêu thích!';
        $message_type = 'success';
    } elseif ($_POST['action'] === 'add_to_cart') {
        $product_id = (int)$_POST['product_id'];
        // Check product stock
        $check = $pdo->prepare("SELECT quantity FROM products WHERE id = ? AND status = 'active'");
        $check->execute([$product_id]);
        $product = $check->fetch();
        
        if ($product && $product['quantity'] > 0) {
            $cart_stmt = $pdo->prepare("
                INSERT INTO shopping_cart (user_id, product_id, quantity) 
                VALUES (?, ?, 1) 
                ON DUPLICATE KEY UPDATE quantity = quantity + 1
            ");
            $cart_stmt->execute([$user_id, $product_id]);
            $message = 'Đã thêm sản phẩm vào giỏ hàng!';
            $message_type = 'success';
        } else {
            $message = 'Sản phẩm đã hết hàng!';
            $message_type = 'danger';
        }
    }
}

// Get wishlist items
$wishlist_query = "
    SELECT w.*, p.name, p.price, p.image, p.quantity as stock, p.status, c.name as category_name
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";
$stmt = $pdo->prepare($wishlist_query);
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
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
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Sản phẩm yêu thích</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <h1 class="mb-4"><i class="bi bi-heart-fill text-danger"></i> Sản phẩm yêu thích</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($wishlist_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-heart" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Chưa có sản phẩm yêu thích</h4>
                <p class="text-muted">Hãy thêm sản phẩm vào danh sách yêu thích để theo dõi!</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="bi bi-grid"></i> Xem sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100">
                            <a href="product_detail.php?id=<?php echo $item['product_id']; ?>">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="height: 200px; object-fit: contain; padding: 15px;">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center" 
                                         style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="bi bi-phone text-white" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="card-body">
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></p>
                                <h6 class="card-title">
                                    <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h6>
                                <p class="text-primary fw-bold mb-2">
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                                </p>
                                <?php if ($item['stock'] > 0 && $item['status'] === 'active'): ?>
                                    <span class="badge bg-success mb-2">Còn hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-danger mb-2">Hết hàng</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex gap-2">
                                    <?php if ($item['stock'] > 0 && $item['status'] === 'active'): ?>
                                        <form method="POST" class="flex-fill">
                                            <input type="hidden" name="action" value="add_to_cart">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-cart-plus"></i> Thêm giỏ
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa khỏi yêu thích">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
