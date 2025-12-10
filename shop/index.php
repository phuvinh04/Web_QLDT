<?php
// Shop Homepage - Flat Design
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "PhoneStore - Trang chủ";
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

$featured_query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   LEFT JOIN brands b ON p.brand_id = b.id
                   WHERE p.status = 'active' 
                   ORDER BY p.created_at DESC 
                   LIMIT 8";
$featured_products = $pdo->query($featured_query)->fetchAll();

$categories_query = "SELECT * FROM categories ORDER BY id";
$categories = $pdo->query($categories_query)->fetchAll();

// Lấy thương hiệu
$brands_query = "SELECT * FROM brands ORDER BY name";
$brands = $pdo->query($brands_query)->fetchAll();

$bestseller_query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN brands b ON p.brand_id = b.id
                     WHERE p.status = 'active' 
                     ORDER BY p.price DESC 
                     LIMIT 4";
$bestseller_products = $pdo->query($bestseller_query)->fetchAll();

include 'components/product_card.php';
$GLOBALS['base_url'] = '../';
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Điện Thoại Chính Hãng</h1>
                    <p class="lead">Khám phá bộ sưu tập điện thoại mới nhất với giá tốt nhất. Chất lượng đảm bảo, bảo hành chính hãng.</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-light btn-lg">
                            <i class="bi bi-grid"></i> Xem sản phẩm
                        </a>
                        <a href="#categories" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-arrow-down"></i> Khám phá
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image">
                        <i class="bi bi-phone" style="font-size: 10rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Categories Section -->
        <section id="categories" class="categories-section">
            <h2 class="section-title">Danh Mục Sản Phẩm</h2>
            <div class="row">
                <?php 
                // Icon mapping cho từng loại category
                $category_icons = [
                    'Điện thoại cao cấp' => 'bi-phone-fill',
                    'Điện thoại tầm trung' => 'bi-phone',
                    'Điện thoại giá rẻ' => 'bi-phone-vibrate',
                    'Máy tính bảng' => 'bi-tablet',
                    'Phụ kiện' => 'bi-headphones',
                ];
                foreach ($categories as $category): 
                    $icon = $category_icons[$category['name']] ?? 'bi-box';
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                            <div class="category-icon">
                                <i class="bi <?php echo $icon; ?>"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p><?php echo htmlspecialchars($category['description'] ?? 'Khám phá các sản phẩm ' . $category['name']); ?></p>
                            <div class="category-arrow">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Brands Section -->
        <section class="brands-section">
            <h2 class="section-title">Thương Hiệu Nổi Bật</h2>
            <div class="row justify-content-center">
                <?php foreach ($brands as $brand): ?>
                    <div class="col-lg-2 col-md-3 col-4 mb-3">
                        <a href="products.php?brand=<?php echo $brand['id']; ?>" class="brand-card text-center d-block p-3">
                            <div class="brand-name fw-bold"><?php echo htmlspecialchars($brand['name']); ?></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-section">
            <div class="section-header">
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <a href="products.php" class="btn btn-outline-primary">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="row">
                <?php 
                foreach ($featured_products as $product): 
                    $product['rating'] = 4.8;
                    $product['is_new'] = true;
                    renderProductCard($product);
                endforeach; 
                ?>
            </div>
        </section>

        <!-- Bestsellers -->
        <section class="bestseller-section">
            <div class="section-header">
                <h2 class="section-title">Sản Phẩm Bán Chạy</h2>
                <a href="products.php?sort=popular" class="btn btn-outline-primary">
                    Xem thêm <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="row">
                <?php 
                foreach ($bestseller_products as $product): 
                    $product['rating'] = 4.9;
                    $product['is_bestseller'] = true;
                    renderProductCard($product);
                endforeach; 
                ?>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5>Giao hàng miễn phí</h5>
                        <p>Miễn phí giao hàng cho đơn từ 500k</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5>Bảo hành chính hãng</h5>
                        <p>Bảo hành 12-24 tháng từ nhà sản xuất</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <h5>Đổi trả dễ dàng</h5>
                        <p>Đổi trả trong 7 ngày nếu có lỗi</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h5>Hỗ trợ 24/7</h5>
                        <p>Tư vấn và hỗ trợ khách hàng mọi lúc</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'components/shop_footer.php'; ?>
    <?php include 'components/modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        window.userRole = <?php echo isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 'null'; ?>;
    </script>
    <script src="assets/shop.js"></script>
</body>
</html>
