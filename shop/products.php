<?php
// Shop Products Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Sản phẩm - PhoneStore";
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

// Lấy filter từ URL
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : '';
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Xây dựng query sản phẩm
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($brand_filter > 0) {
    $where_conditions[] = "p.brand_id = ?";
    $params[] = $brand_filter;
}

if (!empty($search_filter)) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $search_param = '%' . $search_filter . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($price_filter)) {
    $price_range = explode('-', $price_filter);
    if (count($price_range) == 2) {
        $where_conditions[] = "p.price BETWEEN ? AND ?";
        $params[] = (int)$price_range[0];
        $params[] = (int)$price_range[1];
    }
}

$where_clause = implode(' AND ', $where_conditions);

// Sắp xếp
$order_clause = "ORDER BY p.created_at DESC";
switch ($sort_filter) {
    case 'name_asc':
        $order_clause = "ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $order_clause = "ORDER BY p.name DESC";
        break;
    case 'price_asc':
        $order_clause = "ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $order_clause = "ORDER BY p.price DESC";
        break;
    case 'newest':
        $order_clause = "ORDER BY p.created_at DESC";
        break;
    case 'popular':
        $order_clause = "ORDER BY p.price DESC"; // Mock popularity by price
        break;
}

// Đếm tổng số sản phẩm
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];

// Tính pagination
$total_pages = ceil($total_products / $per_page);
$offset = ($current_page - 1) * $per_page;

// Lấy danh sách sản phẩm
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE $where_clause
                   $order_clause
                   LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($products_query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh mục
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();

// Lấy thương hiệu
$brands_query = "SELECT * FROM brands ORDER BY name";
$brands = $pdo->query($brands_query)->fetchAll();

// Include components
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

    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Sản phẩm</li>
                    <?php if ($category_filter > 0): ?>
                        <?php 
                        $selected_category = array_filter($categories, function($cat) use ($category_filter) {
                            return $cat['id'] == $category_filter;
                        });
                        $selected_category = reset($selected_category);
                        if ($selected_category):
                        ?>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($selected_category['name']); ?></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">
                        <?php if (!empty($search_filter)): ?>
                            Kết quả tìm kiếm: "<?php echo htmlspecialchars($search_filter); ?>"
                        <?php elseif ($category_filter > 0 && isset($selected_category)): ?>
                            <?php echo htmlspecialchars($selected_category['name']); ?>
                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                    </h1>
                    <p class="text-muted">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="view-controls">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="view" id="view_grid" value="grid" checked>
                            <label class="btn btn-outline-primary" for="view_grid">
                                <i class="bi bi-grid"></i>
                            </label>
                            <input type="radio" class="btn-check" name="view" id="view_list" value="list">
                            <label class="btn btn-outline-primary" for="view_list">
                                <i class="bi bi-list"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <?php 
        $current_filters = [
            'category' => $category_filter,
            'brand' => $brand_filter,
            'search' => $search_filter,
            'price' => $price_filter,
            'sort' => $sort_filter,
            'per_page' => $per_page
        ];
        
        include 'components/product_filters.php';
        renderProductFilters($categories, $brands, $current_filters);
        ?>

        <!-- Products Grid -->
        <section class="products-section">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-muted);"></i>
                        <h4 class="mt-3">Không tìm thấy sản phẩm nào</h4>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác!</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Xem tất cả sản phẩm
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row products-container view-grid">
                    <?php 
                    foreach ($products as $product): 
                        $product['rating'] = 4.5;
                        $product['is_new'] = (strtotime($product['created_at']) > strtotime('-30 days'));
                        renderProductCard($product);
                    endforeach; 
                    ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="pagination-nav">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>

    <?php include 'components/shop_footer.php'; ?>
    <?php include 'components/modals.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Shop JavaScript -->
    <script>
        window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        window.userRole = <?php echo isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 'null'; ?>;
    </script>
    <script src="assets/shop.js"></script>
</body>
</html>