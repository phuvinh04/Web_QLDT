<?php 
session_start();

// Check login - nếu chưa đăng nhập thì chuyển đến shop
if (!isset($_SESSION['user_id'])) {
    header("Location: shop/index.php");
    exit;
}

$page_title = "Dashboard";
$current_page = "index";
$base_url = "./";

// Lấy role_id từ session
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;

// Định nghĩa quyền xem các phần trên dashboard
// 1: admin, 2: manager, 3: sales, 4: warehouse
$dashboard_permissions = [
    'revenue'    => [1, 2],         // Doanh thu: Admin, Manager
    'orders'     => [1, 2, 3],      // Đơn hàng: Admin, Manager, Sales
    'products'   => [1, 2, 3, 4],   // Sản phẩm: Tất cả
    'inventory'  => [1, 2, 4],      // Cảnh báo tồn kho: Admin, Manager, Warehouse
];

// Hàm kiểm tra quyền dashboard
function canViewDashboard($section, $role_id, $permissions) {
    if (!isset($permissions[$section])) return true;
    return in_array($role_id, $permissions[$section]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include 'components/head.php'; ?>
</head>
<body>
  <div class="wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
      <?php include 'components/header.php'; ?>

      <div class="content">
        <div class="page-title">
          <h1>Tổng quan</h1>
          <div class="breadcrumb">Trang chủ / Dashboard</div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
          <?php if (canViewDashboard('revenue', $role_id, $dashboard_permissions)): ?>
          <div class="stat-card">
            <div class="stat-icon blue">
              <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-info">
              <h4>Doanh thu hôm nay</h4>
              <div class="stat-value">125.5M</div>
              <div class="stat-change up"><i class="bi bi-arrow-up"></i> +12.5%</div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (canViewDashboard('orders', $role_id, $dashboard_permissions)): ?>
          <div class="stat-card">
            <div class="stat-icon green">
              <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-info">
              <h4>Đơn hàng</h4>
              <div class="stat-value">48</div>
              <div class="stat-change up"><i class="bi bi-arrow-up"></i> +8.2%</div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (canViewDashboard('products', $role_id, $dashboard_permissions)): ?>
          <div class="stat-card">
            <div class="stat-icon orange">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-info">
              <h4>Sản phẩm</h4>
              <div class="stat-value">160</div>
              <div class="stat-change down"><i class="bi bi-arrow-down"></i> -3 sản phẩm</div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (canViewDashboard('inventory', $role_id, $dashboard_permissions)): ?>
          <div class="stat-card">
            <div class="stat-icon red">
              <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
              <h4>Cảnh báo tồn kho</h4>
              <div class="stat-value">12</div>
              <div class="stat-change down"><i class="bi bi-arrow-down"></i> Cần nhập hàng</div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Recent Orders - Chỉ hiển thị cho Admin, Manager, Sales -->
        <?php if (canViewDashboard('orders', $role_id, $dashboard_permissions)): ?>
        <div class="card">
          <div class="card-header">
            <h3>Đơn hàng gần đây</h3>
            <a href="pages/orders.php" class="btn btn-primary btn-sm">Xem tất cả</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>HD20241112001</strong></td>
                    <td>Nguyễn Văn A</td>
                    <td>iPhone 15 Pro Max 256GB</td>
                    <td><strong>32.000.000₫</strong></td>
                    <td><span class="badge badge-success">Hoàn thành</span></td>
                    <td>12/11/2024 10:30</td>
                  </tr>
                  <tr>
                    <td><strong>HD20241112002</strong></td>
                    <td>Trần Thị B</td>
                    <td>Samsung Galaxy S24 Ultra</td>
                    <td><strong>31.500.000₫</strong></td>
                    <td><span class="badge badge-warning">Đang xử lý</span></td>
                    <td>12/11/2024 11:15</td>
                  </tr>
                  <tr>
                    <td><strong>HD20241112003</strong></td>
                    <td>Lê Văn C</td>
                    <td>Xiaomi 14 256GB</td>
                    <td><strong>15.990.000₫</strong></td>
                    <td><span class="badge badge-success">Hoàn thành</span></td>
                    <td>12/11/2024 14:20</td>
                  </tr>
                  <tr>
                    <td><strong>HD20241112004</strong></td>
                    <td>Phạm Thị D</td>
                    <td>iPhone 15 Pro Max 256GB</td>
                    <td><strong>32.000.000₫</strong></td>
                    <td><span class="badge badge-primary">Chờ thanh toán</span></td>
                    <td>12/11/2024 15:45</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Low Stock Alert - Chỉ hiển thị cho Admin, Manager, Warehouse -->
        <?php if (canViewDashboard('inventory', $role_id, $dashboard_permissions)): ?>
        <div class="card">
          <div class="card-header">
            <h3>Cảnh báo tồn kho thấp</h3>
            <a href="pages/inventory.php" class="btn btn-danger btn-sm">Xem chi tiết</a>
          </div>
          <div class="card-body">
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <span>Có 12 sản phẩm cần nhập hàng gấp!</span>
            </div>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Tồn kho</th>
                    <th>Mức tối thiểu</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>IP15PM256</strong></td>
                    <td>iPhone 15 Pro Max 256GB</td>
                    <td><strong>3</strong></td>
                    <td>5</td>
                    <td><span class="badge badge-danger">Rất thấp</span></td>
                  </tr>
                  <tr>
                    <td><strong>SS24U512</strong></td>
                    <td>Samsung Galaxy S24 Ultra 512GB</td>
                    <td><strong>4</strong></td>
                    <td>5</td>
                    <td><span class="badge badge-warning">Thấp</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <?php include 'components/footer.php'; ?>
    </div>
  </div>

  <?php include 'components/scripts.php'; ?>
</body>
</html>
