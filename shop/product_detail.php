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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

// Get product details
$query = "SELECT p.*, c.name as category_name, b.name as brand_name
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE p.id = ? AND p.status = 'active'";
$stmt = $pdo->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

$page_title = $product['name'] . " - PhoneStore";

// Get related products
$related_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                  ORDER BY RAND() 
                  LIMIT 4";
$related_stmt = $pdo->prepare($related_query);
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll();

// Get product reviews
$reviews_query = "SELECT r.*, u.full_name, u.avatar 
                  FROM product_reviews r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = ? 
                  ORDER BY r.created_at DESC";
$reviews_stmt = $pdo->prepare($reviews_query);
try {
    $reviews_stmt->execute([$product_id]);
    $reviews = $reviews_stmt->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}

// Calculate average rating
$avg_rating = 0;
$rating_count = count($reviews);
if ($rating_count > 0) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total_rating / $rating_count, 1);
}

// Handle review submission
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    if (!isset($_SESSION['user_id'])) {
        $review_message = 'error:Vui lòng đăng nhập để đánh giá sản phẩm!';
    } else {
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment'] ?? '');
        
        if ($rating < 1 || $rating > 5) {
            $review_message = 'error:Vui lòng chọn số sao đánh giá!';
        } else {
            try {
                // Check if user already reviewed
                $check_stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
                $check_stmt->execute([$product_id, $_SESSION['user_id']]);
                
                if ($check_stmt->fetch()) {
                    // Update existing review
                    $update_stmt = $pdo->prepare("UPDATE product_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE product_id = ? AND user_id = ?");
                    $update_stmt->execute([$rating, $comment, $product_id, $_SESSION['user_id']]);
                    $review_message = 'success:Đã cập nhật đánh giá của bạn!';
                } else {
                    // Insert new review
                    $insert_stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $insert_stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
                    $review_message = 'success:Cảm ơn bạn đã đánh giá sản phẩm!';
                }
                
                // Refresh reviews
                $reviews_stmt->execute([$product_id]);
                $reviews = $reviews_stmt->fetchAll();
                $rating_count = count($reviews);
                if ($rating_count > 0) {
                    $total_rating = array_sum(array_column($reviews, 'rating'));
                    $avg_rating = round($total_rating / $rating_count, 1);
                }
            } catch (Exception $e) {
                $review_message = 'error:Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

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
    <style>
        .product-image-main {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }
        .product-image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .product-specs {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .spec-item:last-child {
            border-bottom: none;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-control input {
            width: 60px;
            text-align: center;
        }
        .rating-stars {
            color: #ffc107;
        }
        .review-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            font-size: 1.5rem;
            color: #ddd;
            padding: 0 2px;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include 'components/shop_header.php'; ?>

    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
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
                         class="product-image-main">
                <?php else: ?>
                    <div class="product-image-placeholder">
                        <i class="bi bi-phone" style="font-size: 8rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= round($avg_rating) ? '-fill' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted"><?php echo $avg_rating; ?>/5 (<?php echo $rating_count; ?> đánh giá)</span>
                </div>
                
                <div class="product-price mb-3">
                    <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                </div>
                
                <div class="mb-3">
                    <?php if ($product['quantity'] > 0): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Còn hàng (<?php echo $product['quantity']; ?> sản phẩm)</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Hết hàng</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['brand_name'])): ?>
                        <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="text-muted mb-4"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả')); ?></p>
                
                <?php if ($product['quantity'] > 0): ?>
                    <form action="cart.php" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="row align-items-end g-3">
                            <div class="col-auto">
                                <label class="form-label">Số lượng</label>
                                <div class="quantity-control">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">-</button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>
                                </div>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- Product Specs -->
                <div class="product-specs">
                    <h5 class="mb-3">Thông tin sản phẩm</h5>
                    <div class="spec-item">
                        <span class="text-muted">Mã sản phẩm:</span>
                        <span><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="text-muted">Danh mục:</span>
                        <span><?php echo htmlspecialchars($product['category_name'] ?? 'Chưa phân loại'); ?></span>
                    </div>
                    <?php if (!empty($product['brand_name'])): ?>
                    <div class="spec-item">
                        <span class="text-muted">Thương hiệu:</span>
                        <span><?php echo htmlspecialchars($product['brand_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="spec-item">
                        <span class="text-muted">Tình trạng:</span>
                        <span><?php echo $product['quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Đánh giá sản phẩm (<?php echo $rating_count; ?>)</h5>
            </div>
            <div class="card-body">
                <?php 
                if (!empty($review_message)) {
                    $parts = explode(':', $review_message, 2);
                    $type = $parts[0] === 'success' ? 'success' : 'danger';
                    $msg = $parts[1] ?? $review_message;
                    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>' . htmlspecialchars($msg) . '</div>';
                }
                ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" class="mb-4 p-3 bg-light rounded">
                        <input type="hidden" name="action" value="add_review">
                        <h6 class="mb-3">Viết đánh giá của bạn</h6>
                        <div class="mb-3">
                            <label class="form-label">Đánh giá sao</label>
                            <div class="star-rating">
                                <input type="radio" name="rating" value="5" id="star5"><label for="star5"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="4" id="star4"><label for="star4"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="3" id="star3"><label for="star3"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="2" id="star2"><label for="star2"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" value="1" id="star1"><label for="star1"><i class="bi bi-star-fill"></i></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nhận xét</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Gửi đánh giá
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mb-4">
                        <a href="../auth/login.php">Đăng nhập</a> để viết đánh giá sản phẩm.
                    </div>
                <?php endif; ?>
                
                <?php if (empty($reviews)): ?>
                    <p class="text-muted text-center py-4">Chưa có đánh giá nào cho sản phẩm này.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($review['full_name'] ?? 'Ẩn danh'); ?></strong>
                                    <div class="rating-stars small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <section class="related-products">
                <h3 class="section-title">Sản phẩm liên quan</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-lg-3 col-md-4 col-6 mb-4">
                            <?php renderProductCard($related); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeQty(delta) {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) + delta;
            const max = parseInt(input.max);
            if (value < 1) value = 1;
            if (value > max) value = max;
            input.value = value;
        }
    </script>
</body>
</html>
