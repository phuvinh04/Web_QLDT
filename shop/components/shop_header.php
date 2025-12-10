<?php
// Shop Header Component
// Include config if not already included
if (!function_exists('env')) {
    require_once '../config.php';
}

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    // Get cart count from database
    try {
        $pdo_header = new PDO(
            "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
            env('DB_USER', 'root'),
            env('DB_PASS', ''),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $cart_stmt = $pdo_header->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
        $cart_stmt->execute([$_SESSION['user_id']]);
        $cart_result = $cart_stmt->fetch();
        $cart_count = $cart_result['total'] ?? 0;
    } catch (Exception $e) {
        $cart_count = 0;
    }
}
?>
<!-- Shop Navigation -->
<nav class="navbar navbar-expand-lg shop-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-phone"></i> PhoneStore
        </a>
        
        <!-- Mobile Search Toggle -->
        <button class="btn d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch">
            <i class="bi bi-search text-white"></i>
        </button>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Desktop Search -->
        <div class="d-none d-lg-flex flex-grow-1 mx-4">
            <form method="GET" action="products.php" class="d-flex w-100">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-input-nav" 
                           placeholder="Tìm kiếm sản phẩm..." 
                           value="<?php echo htmlspecialchars($search_filter ?? ''); ?>">
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <?php if (isset($category_filter) && $category_filter > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                <?php endif; ?>
            </form>
        </div>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto d-lg-none">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#products">
                        <i class="bi bi-grid"></i> Sản phẩm
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <!-- Cart Icon -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="cart.php">
                        <i class="bi bi-cart3"></i> Giỏ hàng
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                

                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged in user menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (!empty($_SESSION['avatar']) && strpos($_SESSION['avatar'], 'http') === 0): ?>
                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="Avatar" 
                                     style="width: 24px; height: 24px; border-radius: 50%; margin-right: 5px;">
                            <?php else: ?>
                                <i class="bi bi-person-circle"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showProfileModal()">
                                <i class="bi bi-person"></i> Thông tin cá nhân
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showOrderHistoryModal()">
                                <i class="bi bi-clock-history"></i> Lịch sử đơn hàng
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showOrderTrackingModal()">
                                <i class="bi bi-truck"></i> Tra cứu đơn hàng
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($_SESSION['role_id'] != 5): ?>
                                <li><a class="dropdown-item" href="index.php">
                                    <i class="bi bi-speedometer2"></i> Quản lý
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Guest user menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> Tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                            </a></li>
                            <li><a class="dropdown-item" href="../auth/register.php">
                                <i class="bi bi-person-plus"></i> Đăng ký
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showOrderTrackingModal()">
                                <i class="bi bi-truck"></i> Tra cứu đơn hàng
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Mobile Search Collapse -->
    <div class="collapse w-100 d-lg-none" id="mobileSearch">
        <div class="container py-3">
            <form method="GET" action="products.php" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm kiếm sản phẩm..." 
                           value="<?php echo htmlspecialchars($search_filter ?? ''); ?>">
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</nav>

<style>
.search-input-nav {
    border: none;
    border-radius: 25px 0 0 25px;
    padding: 8px 15px;
}

.search-input-nav:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.shop-navbar .dropdown-menu {
    border-radius: var(--radius-md);
    border: 2px solid var(--border);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.shop-navbar .dropdown-item {
    padding: 10px 20px;
    font-weight: 500;
}

.shop-navbar .dropdown-item:hover {
    background: var(--light);
    color: var(--primary);
}
</style>