<?php
// Redirect to new shop structure
header("Location: shop/index.php");
exit;

// Include config để kết nối database
require_once 'config.php';

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

// Lấy filter từ URL
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng query sản phẩm
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_filter)) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $search_param = '%' . $search_filter . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where_conditions);

// Lấy danh sách sản phẩm
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE $where_clause
                   ORDER BY p.created_at DESC 
                   LIMIT 16";

$stmt = $pdo->prepare($products_query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh mục
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();

// Lấy sản phẩm nổi bật (top 4 sản phẩm mới nhất)
$featured_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.status = 'active' 
                   ORDER BY p.created_at DESC 
                   LIMIT 4";
$featured_products = $pdo->query($featured_query)->fetchAll();

// Include product card component function
include 'shop/components/product_card.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS - Sử dụng style hiện có của dự án -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    
    <!-- Shop Components CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>shop/assets/shop.css">
    
    <!-- Shop-specific styles -->
    <style>
        /* Shop Navigation */
        .shop-navbar {
            background: var(--primary);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .shop-navbar .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .shop-navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.2s ease;
        }

        .shop-navbar .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-section .lead {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Product Cards */
        .product-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 2px solid var(--border);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 30px;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: var(--light);
        }

        .product-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 250px);
        }

        .product-category {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: var(--radius-xl);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
            line-height: 1.4;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--danger);
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .no-image {
            background: linear-gradient(135deg, var(--light), var(--border-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 3rem;
        }

        /* Category Filter */
        .category-filter {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 25px;
            border: 2px solid var(--border);
            margin-bottom: 40px;
        }

        .category-btn {
            background: var(--light);
            border: 2px solid transparent;
            color: var(--secondary);
            padding: 10px 20px;
            border-radius: var(--radius-xl);
            margin: 5px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            text-decoration: none;
            transform: translateY(-1px);
        }

        /* Search Bar */
        .search-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 25px;
            border: 2px solid var(--border);
            margin-bottom: 30px;
        }

        .search-input {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: none;
        }

        /* Login Prompt */
        .login-prompt {
            background: linear-gradient(135deg, var(--warning), #f97316);
            color: white;
            padding: 40px;
            border-radius: var(--radius-lg);
            text-align: center;
            margin: 60px 0;
        }

        .login-prompt h3 {
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* Footer */
        .shop-footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 30px;
            margin-top: 80px;
        }

        .shop-footer h5 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .shop-footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .shop-footer a:hover {
            color: white;
        }

        /* Featured Section */
        .featured-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 40px;
            border: 2px solid var(--border);
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 40px;
            color: var(--dark);
            letter-spacing: -0.5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section .lead {
                font-size: 1rem;
            }
            
            .product-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Include shop header component
    include 'shop/components/shop_header.php'; 
    ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Điện Thoại Chính Hãng</h1>
                    <p class="lead">Khám phá bộ sưu tập điện thoại mới nhất với giá tốt nhất. Chất lượng đảm bảo, bảo hành chính hãng.</p>
                    <a href="#products" class="btn btn-light btn-lg mt-3">
                        <i class="bi bi-arrow-down"></i> Xem sản phẩm
                    </a>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-phone" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <?php 
        // Include product filters component
        $current_filters = [
            'category' => $category_filter,
            'search' => $search_filter,
            'price' => $_GET['price'] ?? '',
            'sort' => $_GET['sort'] ?? '',
            'brand' => $_GET['brand'] ?? ''
        ];
        
        include 'shop/components/product_filters.php';
        renderProductFilters($categories, $current_filters);
        ?>

        <!-- Featured Products Section -->
        <?php if (!empty($search_filter) || $category_filter > 0): ?>
            <section id="products">
                <h2 class="section-title">
                    <?php if (!empty($search_filter)): ?>
                        Kết quả tìm kiếm: "<?php echo htmlspecialchars($search_filter); ?>"
                    <?php elseif ($category_filter > 0): ?>
                        <?php 
                        $selected_category = array_filter($categories, function($cat) use ($category_filter) {
                            return $cat['id'] == $category_filter;
                        });
                        $selected_category = reset($selected_category);
                        echo htmlspecialchars($selected_category['name']);
                        ?>
                    <?php endif; ?>
                </h2>
        <?php else: ?>
            <!-- Featured Products for homepage -->
            <div class="featured-section">
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <div class="row">
                    <?php 
                    foreach ($featured_products as $product): 
                        // Add mock data for enhanced features
                        $product['rating'] = 4.8; // Featured products have higher rating
                        $product['is_new'] = true;
                        
                        renderProductCard($product);
                    endforeach; 
                    ?>
                </div>
            </div>

            <section id="products">
                <h2 class="section-title">Tất Cả Sản Phẩm</h2>
        <?php endif; ?>
            
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-muted);"></i>
                    <h4 class="mt-3">Không tìm thấy sản phẩm nào</h4>
                    <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác!</p>
                    <a href="shop.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Quay lại trang chủ
                    </a>
                </div>
            <?php else: ?>
                <div class="row products-container view-grid">
                    <?php 
                    foreach ($products as $product): 
                        // Add mock data for enhanced features
                        $product['rating'] = 4.5;
                        $product['is_new'] = (strtotime($product['created_at']) > strtotime('-30 days'));
                        
                        renderProductCard($product);
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Login Prompt -->
        <div class="login-prompt">
            <h3><i class="bi bi-info-circle"></i> Muốn mua hàng?</h3>
            <p class="mb-4">Đăng nhập để có thể đặt hàng và theo dõi đơn hàng của bạn!</p>
            <a href="auth/login.php" class="btn btn-light btn-lg me-3">
                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
            </a>
            <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                <i class="bi bi-person-plus"></i> Đăng ký ngay
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="shop-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5><i class="bi bi-phone"></i> PhoneStore</h5>
                    <p>Cửa hàng điện thoại uy tín, chất lượng cao với giá cả hợp lý. Chúng tôi cam kết mang đến cho khách hàng những sản phẩm chính hãng với dịch vụ tốt nhất.</p>
                </div>
                <div class="col-lg-4">
                    <h5>Liên hệ</h5>
                    <p><i class="bi bi-geo-alt me-2"></i> 123 Đường Nguyễn Trãi, Quận 1, TP.HCM</p>
                    <p><i class="bi bi-telephone me-2"></i> 0123 456 789</p>
                    <p><i class="bi bi-envelope me-2"></i> info@phonestore.com</p>
                    <p><i class="bi bi-clock me-2"></i> 8:00 - 22:00 (Thứ 2 - Chủ nhật)</p>
                </div>
                <div class="col-lg-4">
                    <h5>Hỗ trợ khách hàng</h5>
                    <p><a href="#">Chính sách bảo hành</a></p>
                    <p><a href="#">Hướng dẫn mua hàng</a></p>
                    <p><a href="#">Chính sách đổi trả</a></p>
                    <div class="mt-3">
                        <h6>Theo dõi chúng tôi</h6>
                        <div class="d-flex gap-3">
                            <a href="#"><i class="bi bi-facebook" style="font-size: 1.5rem;"></i></a>
                            <a href="#"><i class="bi bi-instagram" style="font-size: 1.5rem;"></i></a>
                            <a href="#"><i class="bi bi-youtube" style="font-size: 1.5rem;"></i></a>
                            <a href="#"><i class="bi bi-tiktok" style="font-size: 1.5rem;"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 PhoneStore Management System. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <?php 
    // Include modals component
    include 'shop/components/modals.php'; 
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Shop JavaScript -->
    <script>
        // Pass PHP session data to JavaScript
        window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        window.userRole = <?php echo isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 'null'; ?>;
    </script>
    <script src="<?php echo $base_url; ?>shop/assets/shop.js"></script>
    
    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>