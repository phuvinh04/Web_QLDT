<?php
// Product Detail Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "../";
require_once '../config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { header("Location: products.php"); exit; }

// Get product details
$query = "SELECT p.*, c.name as category_name, b.name as brand_name
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE p.id = ? AND p.status = 'active'";
$stmt = $pdo->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { header("Location: products.php"); exit; }

$page_title = $product['name'] . " - PhoneStore";

// Get related products
$related_query = "SELECT p.*, c.name as category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                  ORDER BY RAND() LIMIT 4";
$related_stmt = $pdo->prepare($related_query);
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll();

include 'components/product_card.php';
$GLOBALS['base_url'] = '../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
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
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house"></i> Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                    <?php if ($product['category_name']): ?>
                        <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <!-- Product Details -->
        <div class="row mb-5">
            <div class="col-lg-5 mb-4">
                <?php if (!empty($product['image'])): ?>
                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="product-modal-image w-100"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="no-image" style="display: none; height: 400px;">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php else: ?>
                    <div class="no-image" style="height: 400px;">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-7">
                <h1 class="page-title mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="text-muted">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                </div>
                
                <div class="product-price mb-3"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</div>
                
                <div class="mb-3">
                    <?php if ($product['quantity'] > 0): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Còn hàng (<?php echo $product['quantity']; ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Hết hàng</span>
                    <?php endif; ?>
                    <?php if (!empty($product['brand_name'])): ?>
                        <span class="badge bg-primary ms-1"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($product['category_name'])): ?>
                        <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Add to Cart -->
                <?php if ($product['quantity'] > 0): ?>
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="text-muted">Số lượng:</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary px-3" onclick="changeQty(-1)">−</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control text-center border-secondary" style="width: 60px; border-radius: 0;">
                            <button type="button" class="btn btn-outline-secondary px-3" onclick="changeQty(1)">+</button>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-lg flex-fill" onclick="addToCartDetail(<?php echo $product['id']; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                        <button type="button" class="btn btn-success btn-lg" onclick="buyNowDetail(<?php echo $product['id']; ?>)">
                            <i class="bi bi-lightning"></i> Mua ngay
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>Sản phẩm tạm hết hàng. Vui lòng quay lại sau!
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Specs & Info Cards -->
        <div class="row">
            <!-- Thông số kỹ thuật -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cpu me-2"></i>Thông số kỹ thuật</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($product['description'])): ?>
                            <pre style="white-space: pre-wrap; font-family: inherit; margin: 0; font-size: 0.95rem; line-height: 1.8;"><?php echo htmlspecialchars($product['description']); ?></pre>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Chưa có thông số kỹ thuật.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin sản phẩm -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Mã sản phẩm</span>
                            <span><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Danh mục</span>
                            <span><?php echo htmlspecialchars($product['category_name'] ?? 'Chưa phân loại'); ?></span>
                        </div>
                        <?php if (!empty($product['brand_name'])): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Thương hiệu</span>
                            <span><?php echo htmlspecialchars($product['brand_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Tình trạng</span>
                            <span class="<?php echo $product['quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $product['quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <section class="mt-4">
            <h3 class="mb-4"><i class="bi bi-grid me-2"></i>Sản phẩm liên quan</h3>
            <div class="row">
                <?php foreach ($related_products as $related): ?>
                    <?php renderProductCard($related); ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <!-- Toast notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="successToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="errorToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/shop.js?v=2"></script>
    <script>
        function changeQty(delta) {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) + delta;
            const max = parseInt(input.max);
            if (value < 1) value = 1;
            if (value > max) value = max;
            input.value = value;
        }
        
        function addToCartDetail(productId) {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            shopManager.addToCart(productId, quantity);
        }
        
        function buyNowDetail(productId) {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            shopManager.buyNow(productId, quantity);
        }
    </script>
</body>
</html>
