<?php
// Profile Page - Thông tin cá nhân khách hàng
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Thông tin cá nhân - PhoneStore";
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

// Get user info
$user_query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch();

// Get customer info if exists
$customer_query = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
$customer_query->execute([$user['email']]);
$customer = $customer_query->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        
        if (empty($full_name)) {
            $message = 'Vui lòng nhập họ tên!';
            $message_type = 'danger';
        } else {
            try {
                // Update users table
                $update_user = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                $update_user->execute([$full_name, $phone, $user_id]);
                
                // Update customers table
                if ($customer) {
                    $update_customer = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ? WHERE id = ?");
                    $update_customer->execute([$full_name, $phone, $address, $city, $customer['id']]);
                } else {
                    // Insert new customer record
                    $insert_customer = $pdo->prepare("INSERT INTO customers (name, phone, email, address, city, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $insert_customer->execute([$full_name, $phone, $user['email'], $address, $city]);
                }
                
                // Update session
                $_SESSION['full_name'] = $full_name;
                
                $message = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
                
                // Refresh user data
                $user_query->execute([$user_id]);
                $user = $user_query->fetch();
                $customer_query->execute([$user['email']]);
                $customer = $customer_query->fetch();
                
            } catch (Exception $e) {
                $message = 'Có lỗi xảy ra: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Vui lòng điền đầy đủ thông tin!';
            $message_type = 'danger';
        } elseif (!password_verify($current_password, $user['password'])) {
            $message = 'Mật khẩu hiện tại không đúng!';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Mật khẩu mới không khớp!';
            $message_type = 'danger';
        } elseif (strlen($new_password) < 8) {
            $message = 'Mật khẩu mới phải có ít nhất 8 ký tự!';
            $message_type = 'danger';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pw = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pw->execute([$hashed_password, $user_id]);
            
            $message = 'Đổi mật khẩu thành công!';
            $message_type = 'success';
        }
    }
}

// Get order statistics
$order_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount END), 0) as total_spent,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders
    FROM orders WHERE user_id = ?
");
$order_stats->execute([$user_id]);
$stats = $order_stats->fetch();
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
                    <li class="breadcrumb-item active">Thông tin cá nhân</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-circle mx-auto mb-3">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--primary-color);"></i>
                        </div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="badge bg-primary">Khách hàng</span>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#profile-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                                <i class="bi bi-person me-2"></i> Thông tin cá nhân
                            </a>
                            <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="bi bi-key me-2"></i> Đổi mật khẩu
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-bag me-2"></i> Đơn hàng của tôi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Tổng đơn hàng</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_orders']; ?></h3>
                                    </div>
                                    <i class="bi bi-bag fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Đã chi tiêu</h6>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_spent'], 0, ',', '.'); ?>đ</h3>
                                    </div>
                                    <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Chờ xử lý</h6>
                                        <h3 class="mb-0"><?php echo $stats['pending_orders']; ?></h3>
                                    </div>
                                    <i class="bi bi-clock fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Profile Info -->
                    <div class="tab-pane fade show active" id="profile-info">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Thông tin cá nhân</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                            <small class="text-muted">Email không thể thay đổi</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Thành phố</label>
                                            <input type="text" name="city" class="form-control" 
                                                   value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Địa chỉ</label>
                                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Lưu thay đổi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="tab-pane fade" id="change-password">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Đổi mật khẩu</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                        <input type="password" name="new_password" class="form-control" required minlength="8">
                                        <small class="text-muted">Tối thiểu 8 ký tự</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Đổi mật khẩu
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/shop_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/shop.js"></script>
</body>
</html>
