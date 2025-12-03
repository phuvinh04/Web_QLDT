<!-- Sidebar -->
<?php
// Lấy role_id từ session
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;

// Định nghĩa quyền truy cập cho từng menu
// 1: admin, 2: manager, 3: sales, 4: warehouse
$menu_permissions = [
    'products'   => [1, 2, 3, 4],  // Tất cả đều xem được sản phẩm
    'orders'     => [1, 2, 3],      // Admin, Manager, Sales
    'customers'  => [1, 2, 3],      // Admin, Manager, Sales
    'inventory'  => [1, 2, 4],      // Admin, Manager, Warehouse
    'promotions' => [1, 2],         // Admin, Manager
    'users'      => [1],            // Chỉ Admin
    'suppliers'  => [1, 2, 4],      // Admin, Manager, Warehouse
    'categories' => [1, 2],         // Admin, Manager
    'reports'    => [1, 2],         // Admin, Manager
];

// Hàm kiểm tra quyền
function hasPermission($page, $role_id, $permissions) {
    if (!isset($permissions[$page])) return true;
    return in_array($role_id, $permissions[$page]);
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <h3><i class="bi bi-phone"></i> PhoneStore</h3>
    <p>Quản lý cửa hàng điện thoại</p>
  </div>
  <ul class="sidebar-menu">
    <li><a href="<?php echo $base_url; ?>index.php" class="<?php echo ($current_page == 'index') ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
    
    <?php if (hasPermission('products', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/products.php" class="<?php echo ($current_page == 'products') ? 'active' : ''; ?>"><i class="bi bi-box-seam"></i><span>Sản phẩm</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('orders', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/orders.php" class="<?php echo ($current_page == 'orders') ? 'active' : ''; ?>"><i class="bi bi-receipt"></i><span>Đơn hàng</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('customers', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/customers.php" class="<?php echo ($current_page == 'customers') ? 'active' : ''; ?>"><i class="bi bi-people"></i><span>Khách hàng</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('inventory', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/inventory.php" class="<?php echo ($current_page == 'inventory') ? 'active' : ''; ?>"><i class="bi bi-archive"></i><span>Quản lý kho</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('promotions', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/promotions.php" class="<?php echo ($current_page == 'promotions') ? 'active' : ''; ?>"><i class="bi bi-tag"></i><span>Khuyến mãi</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('users', $role_id, $menu_permissions)): ?>
    <li class="menu-divider"><a href="<?php echo $base_url; ?>pages/users.php" class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>"><i class="bi bi-person-gear"></i><span>Người dùng</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('suppliers', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/suppliers.php" class="<?php echo ($current_page == 'suppliers') ? 'active' : ''; ?>"><i class="bi bi-truck"></i><span>Nhà cung cấp</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('categories', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/categories.php" class="<?php echo ($current_page == 'categories') ? 'active' : ''; ?>"><i class="bi bi-grid"></i><span>Danh mục</span></a></li>
    <?php endif; ?>
    
    <?php if (hasPermission('reports', $role_id, $menu_permissions)): ?>
    <li><a href="<?php echo $base_url; ?>pages/reports.php" class="<?php echo ($current_page == 'reports') ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i><span>Báo cáo</span></a></li>
    <?php endif; ?>
    
    <li><a href="<?php echo $base_url; ?>auth/logout.php"><i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span></a></li>
  </ul>
</aside>
