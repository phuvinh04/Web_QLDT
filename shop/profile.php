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
                // Convert empty phone to NULL to avoid duplicate key error
                $phone_value = !empty($phone) ? $phone : null;
                
                // Handle avatar upload
                $avatar = $user['avatar']; // Keep current avatar by default
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $file_type = $_FILES['avatar']['type'];
                    $file_extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                        $message = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP).';
                        $message_type = 'danger';
                    } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) { // 5MB
                        $message = 'Kích thước file không được vượt quá 5MB.';
                        $message_type = 'danger';
                    } else {
                        $target_dir = "../assets/uploads/avatars/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        $new_filename = $user['username'] . "_" . time() . "." . $file_extension;
                        $target_file = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                            // Delete old avatar if it's a local file (not Google URL or default)
                            if ($user['avatar'] && 
                                $user['avatar'] !== 'default-avatar.png' && 
                                !filter_var($user['avatar'], FILTER_VALIDATE_URL) && 
                                file_exists($target_dir . $user['avatar'])) {
                                unlink($target_dir . $user['avatar']);
                            }
                            // Set new avatar filename (this will replace Google avatar URL)
                            $avatar = $new_filename;
                        } else {
                            $message = 'Không thể tải file lên. Vui lòng thử lại.';
                            $message_type = 'danger';
                        }
                    }
                }
                
                // Only update if no avatar upload error
                if (!isset($message) || $message_type !== 'danger') {
                    // Update users table
                    $update_user = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, avatar = ? WHERE id = ?");
                    $update_user->execute([$full_name, $phone_value, $avatar, $user_id]);
                    
                    // Update customers table
                    if ($customer) {
                        $update_customer = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ? WHERE id = ?");
                        $update_customer->execute([$full_name, $phone_value, $address, $city, $customer['id']]);
                    } else {
                        // Insert new customer record
                        $insert_customer = $pdo->prepare("INSERT INTO customers (name, phone, email, address, city, status) VALUES (?, ?, ?, ?, ?, 'active')");
                        $insert_customer->execute([$full_name, $phone_value, $user['email'], $address, $city]);
                    }
                    
                    // Update session
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['avatar'] = $avatar;
                    
                    $message = 'Cập nhật thông tin thành công!';
                    $message_type = 'success';
                    
                    // Refresh user data
                    $user_query->execute([$user_id]);
                    $user = $user_query->fetch();
                    $customer_query->execute([$user['email']]);
                    $customer = $customer_query->fetch();
                }
                
            } catch (Exception $e) {
                $message = 'Có lỗi xảy ra: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Password pattern: at least 8 chars, uppercase, lowercase, number, special char
        $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Vui lòng điền đầy đủ thông tin!';
            $message_type = 'danger';
        } elseif (!password_verify($current_password, $user['password'])) {
            $message = 'Mật khẩu hiện tại không đúng!';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Mật khẩu mới không khớp!';
            $message_type = 'danger';
        } elseif (!preg_match($password_pattern, $new_password)) {
            $message = 'Mật khẩu quá yếu! Cần ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.';
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
                        <div class="avatar-circle mx-auto mb-3" style="width: 120px; height: 120px; overflow: hidden; border-radius: 50%; border: 3px solid var(--primary-color);">
                            <?php 
                            $avatar_path = $user['avatar'] ?? 'default-avatar.png';
                            // Check if avatar is a URL (Google avatar) or local file
                            if (filter_var($avatar_path, FILTER_VALIDATE_URL)) {
                                // Google avatar - use URL directly
                                $avatar_url = $avatar_path;
                            } else {
                                // Local avatar
                                $avatar_url = '../assets/uploads/avatars/' . $avatar_path;
                            }
                            
                            if (filter_var($avatar_path, FILTER_VALIDATE_URL) || (file_exists($avatar_url) && $avatar_path !== 'default-avatar.png')): 
                            ?>
                                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 7rem; color: var(--primary-color); line-height: 120px;"></i>
                            <?php endif; ?>
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
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <!-- Avatar Upload -->
                                    <div class="mb-4 text-center">
                                        <label class="form-label d-block">Ảnh đại diện</label>
                                        <div class="avatar-preview mx-auto mb-3" style="width: 150px; height: 150px; overflow: hidden; border-radius: 50%; border: 3px solid #ddd; position: relative;">
                                            <?php 
                                            $avatar_path = $user['avatar'] ?? 'default-avatar.png';
                                            // Check if avatar is a URL (Google avatar) or local file
                                            if (filter_var($avatar_path, FILTER_VALIDATE_URL)) {
                                                // Google avatar - use URL directly
                                                $avatar_url = $avatar_path;
                                                $is_url = true;
                                            } else {
                                                // Local avatar
                                                $avatar_url = '../assets/uploads/avatars/' . $avatar_path;
                                                $is_url = false;
                                            }
                                            
                                            if (filter_var($avatar_path, FILTER_VALIDATE_URL) || (file_exists($avatar_url) && $avatar_path !== 'default-avatar.png')): 
                                            ?>
                                                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar" id="avatarPreviewImg" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                            <?php else: ?>
                                                <i class="bi bi-person-circle" id="avatarPreviewIcon" style="font-size: 9rem; color: #6c757d; line-height: 150px;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" name="avatar" id="avatarInput" class="form-control d-inline-block" style="max-width: 300px;" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                        <small class="text-muted d-block mt-1">JPG, PNG, GIF, WEBP (Tối đa 5MB)</small>
                                        <?php if (filter_var($avatar_path, FILTER_VALIDATE_URL)): ?>
                                            <small class="text-info d-block mt-1"><i class="bi bi-info-circle"></i> Đang dùng ảnh từ Google. Upload ảnh mới để thay đổi.</small>
                                        <?php endif; ?>
                                    </div>
                                    
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
                                <form method="POST" id="changePasswordForm">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <ul class="list-unstyled mt-2 mb-0 small text-muted">
                                            <li id="rule-length"><i class="bi bi-x-circle"></i> Tối thiểu 8 ký tự</li>
                                            <li id="rule-upper"><i class="bi bi-x-circle"></i> Chữ cái viết hoa (A-Z)</li>
                                            <li id="rule-lower"><i class="bi bi-x-circle"></i> Chữ cái thường (a-z)</li>
                                            <li id="rule-number"><i class="bi bi-x-circle"></i> Số (0-9)</li>
                                            <li id="rule-special"><i class="bi bi-x-circle"></i> Ký tự đặc biệt (!@#$...)</li>
                                        </ul>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirm_password', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted" id="match-message"></small>
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
    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Avatar preview
            const avatarInput = document.getElementById('avatarInput');
            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)');
                            avatarInput.value = '';
                            return;
                        }
                        
                        // Validate file size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Kích thước file không được vượt quá 5MB');
                            avatarInput.value = '';
                            return;
                        }
                        
                        // Preview image
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.querySelector('.avatar-preview');
                            const icon = document.getElementById('avatarPreviewIcon');
                            const img = document.getElementById('avatarPreviewImg');
                            
                            if (icon) {
                                icon.style.display = 'none';
                            }
                            
                            if (img) {
                                img.src = e.target.result;
                                img.style.display = 'block';
                            } else {
                                const newImg = document.createElement('img');
                                newImg.id = 'avatarPreviewImg';
                                newImg.src = e.target.result;
                                newImg.style.cssText = 'width: 100%; height: 100%; object-fit: cover; display: block;';
                                preview.appendChild(newImg);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            const passwordInput = document.getElementById('new_password');
            const confirmInput = document.getElementById('confirm_password');
            const matchMessage = document.getElementById('match-message');
            
            const rules = {
                'rule-length': /.{8,}/,
                'rule-upper': /[A-Z]/,
                'rule-lower': /[a-z]/,
                'rule-number': /[0-9]/,
                'rule-special': /[\W_]/
            };

            // Real-time password strength validation
            passwordInput.addEventListener('input', function() {
                const val = this.value;
                for (const [id, regex] of Object.entries(rules)) {
                    const element = document.getElementById(id);
                    const icon = element.querySelector('i');
                    if (regex.test(val)) {
                        element.classList.remove('text-danger');
                        element.classList.add('text-success');
                        icon.classList.remove('bi-x-circle');
                        icon.classList.add('bi-check-circle-fill');
                    } else {
                        element.classList.remove('text-success');
                        if(val.length > 0) {
                            element.classList.add('text-danger');
                        } else {
                            element.classList.remove('text-danger');
                        }
                        icon.classList.remove('bi-check-circle-fill');
                        icon.classList.add('bi-x-circle');
                    }
                }
                
                // Check confirm password match
                if (confirmInput.value) {
                    checkPasswordMatch();
                }
            });

            // Real-time password match validation
            confirmInput.addEventListener('input', checkPasswordMatch);

            function checkPasswordMatch() {
                if (confirmInput.value === '') {
                    matchMessage.textContent = '';
                    matchMessage.className = 'text-muted';
                } else if (confirmInput.value === passwordInput.value) {
                    matchMessage.textContent = '✓ Mật khẩu khớp';
                    matchMessage.className = 'text-success';
                } else {
                    matchMessage.textContent = '✗ Mật khẩu không khớp';
                    matchMessage.className = 'text-danger';
                }
            }

            // Form validation before submit
            document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirm = confirmInput.value;
                
                // Check all rules
                let allRulesPassed = true;
                for (const [id, regex] of Object.entries(rules)) {
                    if (!regex.test(password)) {
                        allRulesPassed = false;
                        break;
                    }
                }
                
                if (!allRulesPassed) {
                    e.preventDefault();
                    alert('Mật khẩu mới không đủ mạnh! Vui lòng đáp ứng tất cả các yêu cầu.');
                    return false;
                }
                
                if (password !== confirm) {
                    e.preventDefault();
                    alert('Mật khẩu xác nhận không khớp!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
