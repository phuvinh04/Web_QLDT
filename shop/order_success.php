<?php
// Order Success Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Đặt hàng thành công - PhoneStore";
$order_code = $_GET['order'] ?? '';

if (empty($order_code)) {
    header("Location: index.php");
    exit;
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">Đặt hàng thành công!</h2>
                        <p class="text-muted mb-4">
                            Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đang được xử lý.
                        </p>
                        <div class="bg-light p-3 rounded mb-4">
                            <p class="mb-1"><strong>Mã đơn hàng:</strong></p>
                            <h4 class="text-primary mb-0"><?php echo htmlspecialchars($order_code); ?></h4>
                        </div>
                        <p class="text-muted small mb-4">
                            Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng trong thời gian sớm nhất.
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="products.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-house"></i> Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/shop_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
