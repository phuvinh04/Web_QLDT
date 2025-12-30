<?php
// Shop Header Component - Flat Design
if (!function_exists('env')) {
    require_once __DIR__ . '/../../config.php';
}

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
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
<nav class="navbar navbar-expand-lg shop-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-phone"></i> PhoneStore
        </a>
        
        <button class="btn d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch">
            <i class="bi bi-search text-white"></i>
        </button>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="d-none d-lg-flex flex-grow-1 mx-4">
            <form method="GET" action="products.php" class="d-flex w-100" style="max-width: 480px;">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-input-nav" 
                           placeholder="Tìm kiếm sản phẩm..." 
                           value="<?php echo htmlspecialchars($search_filter ?? ''); ?>">
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
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
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-grid"></i> Sản phẩm
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php" title="Yêu thích">
                        <i class="bi bi-heart"></i>
                        <span class="d-lg-none"> Yêu thích</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link position-relative" href="cart.php">
                        <i class="bi bi-cart3"></i> 
                        <span class="d-lg-none">Giỏ hàng</span>
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                <?php echo $cart_count > 99 ? '99+' : $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (!empty($_SESSION['avatar']) && strpos($_SESSION['avatar'], 'http') === 0): ?>
                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="" 
                                     style="width: 24px; height: 24px; border-radius: 50%; margin-right: 6px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle"></i>
                            <?php endif; ?>
                            <span class="d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person"></i> Thông tin cá nhân
                            </a></li>
                            <li><a class="dropdown-item" href="wishlist.php">
                                <i class="bi bi-heart"></i> Sản phẩm yêu thích
                            </a></li>
                            <li><a class="dropdown-item" href="orders.php">
                                <i class="bi bi-bag-check"></i> Đơn hàng của tôi
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($_SESSION['role_id'] != 5): ?>
                                <li><a class="dropdown-item" href="../index.php">
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> <span class="d-none d-lg-inline">Tài khoản</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../auth/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                            </a></li>
                            <li><a class="dropdown-item" href="../auth/register.php">
                                <i class="bi bi-person-plus"></i> Đăng ký
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
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
