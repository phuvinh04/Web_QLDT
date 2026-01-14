<?php 
session_start();

// Nếu chưa đăng nhập, chuyển đến trang shop (trang chủ người dùng)
if (!isset($_SESSION['user_id'])) {
    header("Location: shop/index.php");
    exit;
}

// Nếu là khách hàng (role_id = 5), chuyển đến trang shop
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 5) {
    header("Location: shop/index.php");
    exit;
}

require_once __DIR__ . '/config/database.php';

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

$db = getDB();

// Lấy thống kê
$today = date('Y-m-d');

// Doanh thu hôm nay
$revenueStmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as today_revenue FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
$revenueStmt->execute([$today]);
$todayRevenue = $revenueStmt->fetchColumn();

// Doanh thu hôm qua để tính % tăng trưởng
$yesterday = date('Y-m-d', strtotime('-1 day'));
$yesterdayRevenueStmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as yesterday_revenue FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
$yesterdayRevenueStmt->execute([$yesterday]);
$yesterdayRevenue = $yesterdayRevenueStmt->fetchColumn();
$revenueChange = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue * 100) : 0;

// Số đơn hàng hôm nay
$ordersStmt = $db->prepare("SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = ?");
$ordersStmt->execute([$today]);
$todayOrders = $ordersStmt->fetchColumn();

// Số đơn hàng hôm qua
$yesterdayOrdersStmt = $db->prepare("SELECT COUNT(*) as yesterday_orders FROM orders WHERE DATE(created_at) = ?");
$yesterdayOrdersStmt->execute([$yesterday]);
$yesterdayOrders = $yesterdayOrdersStmt->fetchColumn();
$ordersChange = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders * 100) : 0;

// Tổng số sản phẩm
$productsStmt = $db->query("SELECT COUNT(*) as total_products FROM products WHERE status = 'active'");
$totalProducts = $productsStmt->fetchColumn();

// Số sản phẩm cảnh báo tồn kho thấp
$lowStockStmt = $db->query("SELECT COUNT(*) as low_stock FROM products WHERE quantity <= min_quantity AND quantity > 0");
$lowStockCount = $lowStockStmt->fetchColumn();

// Số sản phẩm hết hàng
$outOfStockStmt = $db->query("SELECT COUNT(*) as out_of_stock FROM products WHERE quantity = 0");
$outOfStockCount = $outOfStockStmt->fetchColumn();

// Đơn hàng gần đây (5 đơn mới nhất)
$recentOrdersStmt = $db->query("
    SELECT o.*, c.name as customer_name, c.phone as customer_phone,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recentOrders = $recentOrdersStmt->fetchAll();

// Sản phẩm tồn kho thấp (top 5)
$lowStockProductsStmt = $db->query("
    SELECT id, sku, name, quantity, min_quantity 
    FROM products 
    WHERE quantity <= min_quantity 
    ORDER BY (quantity - min_quantity) ASC 
    LIMIT 5
");
$lowStockProducts = $lowStockProductsStmt->fetchAll();

$statusLabels = [
    'pending' => ['label' => 'Chờ xử lý', 'class' => 'badge-warning'],
    'completed' => ['label' => 'Hoàn thành', 'class' => 'badge-success'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'badge-danger'],
    'refunded' => ['label' => 'Hoàn tiền', 'class' => 'badge-secondary']
];
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
              <div class="stat-value"><?php echo number_format($todayRevenue / 1000000, 1); ?>M</div>
              <div class="stat-change <?php echo $revenueChange >= 0 ? 'up' : 'down'; ?>">
                <i class="bi bi-arrow-<?php echo $revenueChange >= 0 ? 'up' : 'down'; ?>"></i> 
                <?php echo $revenueChange >= 0 ? '+' : ''; ?><?php echo number_format($revenueChange, 1); ?>%
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (canViewDashboard('orders', $role_id, $dashboard_permissions)): ?>
          <div class="stat-card">
            <div class="stat-icon green">
              <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-info">
              <h4>Đơn hàng hôm nay</h4>
              <div class="stat-value"><?php echo $todayOrders; ?></div>
              <div class="stat-change <?php echo $ordersChange >= 0 ? 'up' : 'down'; ?>">
                <i class="bi bi-arrow-<?php echo $ordersChange >= 0 ? 'up' : 'down'; ?>"></i> 
                <?php echo $ordersChange >= 0 ? '+' : ''; ?><?php echo number_format($ordersChange, 1); ?>%
              </div>
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
              <div class="stat-value"><?php echo $totalProducts; ?></div>
              <div class="stat-change">Đang kinh doanh</div>
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
              <div class="stat-value"><?php echo $lowStockCount + $outOfStockCount; ?></div>
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
            <?php if (empty($recentOrders)): ?>
            <div class="empty-state" style="padding: 40px;">
              <i class="bi bi-receipt" style="font-size: 3rem; color: #cbd5e1;"></i>
              <p style="color: #94a3b8; margin-top: 12px;">Chưa có đơn hàng nào</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Số SP</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentOrders as $order): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                    <td>
                      <?php echo htmlspecialchars($order['customer_name'] ?? 'Khách lẻ'); ?>
                      <?php if ($order['customer_phone']): ?>
                      <br><small style="color: #64748b;"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?php echo $order['item_count']; ?> SP</td>
                    <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</strong></td>
                    <td>
                      <?php $st = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'badge-secondary']; ?>
                      <span class="badge <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
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
            <?php if ($lowStockCount + $outOfStockCount > 0): ?>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <span>Có <?php echo $lowStockCount + $outOfStockCount; ?> sản phẩm cần nhập hàng gấp!</span>
            </div>
            <?php endif; ?>
            <?php if (empty($lowStockProducts)): ?>
            <div class="empty-state" style="padding: 40px;">
              <i class="bi bi-check-circle" style="font-size: 3rem; color: #10b981;"></i>
              <p style="color: #10b981; margin-top: 12px;">Tồn kho ổn định</p>
            </div>
            <?php else: ?>
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
                  <?php foreach ($lowStockProducts as $product): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($product['sku']); ?></strong></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><strong><?php echo $product['quantity']; ?></strong></td>
                    <td><?php echo $product['min_quantity']; ?></td>
                    <td>
                      <?php if ($product['quantity'] == 0): ?>
                      <span class="badge badge-danger">Hết hàng</span>
                      <?php else: ?>
                      <span class="badge badge-warning">Sắp hết</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
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
