<?php
// Shopping Cart Page - Using LocalStorage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Giỏ hàng - PhoneStore";
$base_url = "../";

// Include config
require_once '../config.php';
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
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house"></i> Trang chủ</a></li>
                    <li class="breadcrumb-item active">Giỏ hàng</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="bi bi-cart3"></i> Giỏ hàng của bạn
            </h1>
            <p class="text-muted" id="cartCount">0 sản phẩm trong giỏ hàng</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items Container - Rendered by JavaScript -->
                <div id="cartContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Đang tải giỏ hàng...</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card" id="cartSummaryCard">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="cartSubtotal">0₫</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span id="cartShipping">Tính khi thanh toán</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong id="cartTotal" class="text-primary">0₫</strong>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary btn-lg" id="checkoutBtn">
                                <i class="bi bi-credit-card me-2"></i>Thanh toán
                            </a>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                            </a>
                        </div>
                        
                        <div class="mt-3 text-center" id="freeShippingNotice" style="display: none;">
                            <small class="text-success">
                                <i class="bi bi-truck me-1"></i>Miễn phí vận chuyển cho đơn từ 500.000₫
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
</body>
</html>
