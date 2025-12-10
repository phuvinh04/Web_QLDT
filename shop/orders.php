<?php
// My Orders Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Đơn hàng của tôi - PhoneStore";
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

// Handle cancel order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $order_id = (int)$_POST['order_id'];
    
    // Check if order belongs to user and is pending
    $check = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
    $check->execute([$order_id, $user_id]);
    $order = $check->fetch();
    
    if ($order && $order['status'] === 'pending') {
        $update = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $update->execute([$order_id]);
        $message = 'Đã hủy đơn hàng thành công!';
        $message_type = 'success';
    } else {
        $message = 'Không thể hủy đơn hàng này!';
        $message_type = 'danger';
    }
}


// Get all orders for user
$orders_query = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$orders_stmt = $pdo->prepare($orders_query);
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

// Status labels
$status_labels = [
    'pending' => ['text' => 'Chờ xử lý', 'class' => 'warning'],
    'completed' => ['text' => 'Hoàn thành', 'class' => 'success'],
    'cancelled' => ['text' => 'Đã hủy', 'class' => 'secondary'],
    'refunded' => ['text' => 'Hoàn tiền', 'class' => 'info']
];

// Payment method labels
$payment_labels = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'cash' => 'Tiền mặt',
    'card' => 'Thẻ',
    'transfer' => 'Chuyển khoản'
];
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
                    <li class="breadcrumb-item active">Đơn hàng của tôi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-bag-check"></i> Đơn hàng của tôi</h1>
            <p class="text-muted"><?php echo count($orders); ?> đơn hàng</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-cart">
                <div class="text-center py-5">
                    <i class="bi bi-bag-x" style="font-size: 5rem; color: var(--text-muted);"></i>
                    <h3 class="mt-4">Chưa có đơn hàng nào</h3>
                    <p class="text-muted mb-4">Hãy mua sắm để có đơn hàng đầu tiên!</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart"></i> Mua sắm ngay
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): 
                    $status = $status_labels[$order['status']] ?? ['text' => $order['status'], 'class' => 'secondary'];
                ?>
                    <div class="order-card mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="me-3">#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo $status['class']; ?>">
                                    <?php echo $status['text']; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong><?php echo $order['item_count']; ?> sản phẩm</strong></p>
                                        <small class="text-muted">
                                            <?php echo $payment_labels[$order['payment_method']] ?? $order['payment_method']; ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-md-center">
                                        <p class="mb-0 text-danger fw-bold fs-5">
                                            <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button class="btn btn-outline-primary btn-sm me-2" 
                                                onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </button>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="confirmCancelOrder(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')">
                                                <i class="bi bi-x-circle"></i> Hủy đơn
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt"></i> Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirm Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning"></i> Xác nhận hủy đơn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn hủy đơn hàng <strong id="cancelOrderNumber"></strong>?</p>
                    <p class="text-muted small">Hành động này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Hủy đơn hàng
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmCancelOrder(orderId, orderNumber) {
            document.getElementById('cancelOrderId').value = orderId;
            document.getElementById('cancelOrderNumber').textContent = '#' + orderNumber;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }

        async function viewOrderDetail(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            const body = document.getElementById('orderDetailBody');
            
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            try {
                const response = await fetch('api/order_detail.php?id=' + orderId);
                const data = await response.json();
                
                if (data.success) {
                    const order = data.order;
                    const items = data.items;
                    
                    let itemsHtml = items.map(item => `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <img src="../assets/uploads/${item.image || 'no-image.png'}" 
                                     alt="${item.name}" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" 
                                     class="me-3">
                                <div>
                                    <strong>${item.name}</strong>
                                    <small class="text-muted d-block">x${item.quantity}</small>
                                </div>
                            </div>
                            <span>${new Intl.NumberFormat('vi-VN').format(item.unit_price * item.quantity)}₫</span>
                        </div>
                    `).join('');

                    const statusMap = {
                        'pending': '<span class="badge bg-warning">Chờ xử lý</span>',
                        'completed': '<span class="badge bg-success">Hoàn thành</span>',
                        'cancelled': '<span class="badge bg-secondary">Đã hủy</span>',
                        'refunded': '<span class="badge bg-info">Hoàn tiền</span>'
                    };

                    body.innerHTML = `
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Mã đơn: <strong>${order.order_number}</strong></h6>
                                ${statusMap[order.status] || order.status}
                            </div>
                            <small class="text-muted">Ngày đặt: ${new Date(order.created_at).toLocaleString('vi-VN')}</small>
                        </div>
                        
                        <h6 class="mb-3">Sản phẩm</h6>
                        <div class="mb-4">${itemsHtml}</div>
                        
                        <div class="bg-light p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span>${new Intl.NumberFormat('vi-VN').format(order.subtotal)}₫</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Thuế:</span>
                                <span>${new Intl.NumberFormat('vi-VN').format(order.tax)}₫</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-danger">${new Intl.NumberFormat('vi-VN').format(order.total_amount)}₫</strong>
                            </div>
                        </div>
                        
                        ${order.notes ? `<div class="mt-3"><small class="text-muted">Ghi chú: ${order.notes}</small></div>` : ''}
                    `;
                } else {
                    body.innerHTML = '<div class="text-center py-4 text-danger">Không thể tải thông tin đơn hàng</div>';
                }
            } catch (error) {
                body.innerHTML = '<div class="text-center py-4 text-danger">Có lỗi xảy ra</div>';
            }
        }
    </script>
</body>
</html>
