<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Báo cáo thống kê";
$current_page = "reports";
$base_url = "../";

$db = getDB();

// Lấy filter từ GET
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); // Đầu tháng
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Hôm nay
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'revenue';

// Thống kê doanh thu trong khoảng thời gian
$revenueStmt = $db->prepare("
    SELECT 
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COALESCE(SUM(total_amount - discount), 0) as net_revenue,
        COUNT(*) as total_orders,
        COALESCE(AVG(total_amount), 0) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? 
    AND status = 'completed'
");
$revenueStmt->execute([$dateFrom, $dateTo]);
$revenueStats = $revenueStmt->fetch();

// Tính lợi nhuận (doanh thu - giá vốn)
$profitStmt = $db->prepare("
    SELECT COALESCE(SUM((oi.unit_price - p.cost) * oi.quantity), 0) as total_profit
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.status = 'completed'
    AND p.cost IS NOT NULL
");
$profitStmt->execute([$dateFrom, $dateTo]);
$totalProfit = $profitStmt->fetchColumn();

// Top 10 sản phẩm bán chạy
$topProductsStmt = $db->prepare("
    SELECT 
        p.id, p.sku, p.name, p.price, p.cost,
        c.name as category_name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.subtotal) as total_revenue,
        SUM((oi.unit_price - COALESCE(p.cost, 0)) * oi.quantity) as total_profit
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.status = 'completed'
    GROUP BY p.id, p.sku, p.name, p.price, p.cost, c.name
    ORDER BY total_sold DESC
    LIMIT 10
");
$topProductsStmt->execute([$dateFrom, $dateTo]);
$topProducts = $topProductsStmt->fetchAll();

// Tính % tăng trưởng so với kỳ trước
$daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1;
$prevDateFrom = date('Y-m-d', strtotime($dateFrom . " -$daysDiff days"));
$prevDateTo = date('Y-m-d', strtotime($dateTo . " -$daysDiff days"));

$prevRevenueStmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'");
$prevRevenueStmt->execute([$prevDateFrom, $prevDateTo]);
$prevRevenue = $prevRevenueStmt->fetchColumn();
$revenueGrowth = $prevRevenue > 0 ? (($revenueStats['total_revenue'] - $prevRevenue) / $prevRevenue * 100) : 0;

$prevOrdersStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'");
$prevOrdersStmt->execute([$prevDateFrom, $prevDateTo]);
$prevOrders = $prevOrdersStmt->fetchColumn();
$ordersGrowth = $prevOrders > 0 ? (($revenueStats['total_orders'] - $prevOrders) / $prevOrders * 100) : 0;

$prevProfitStmt = $db->prepare("
    SELECT COALESCE(SUM((oi.unit_price - p.cost) * oi.quantity), 0)
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.status = 'completed'
    AND p.cost IS NOT NULL
");
$prevProfitStmt->execute([$prevDateFrom, $prevDateTo]);
$prevProfit = $prevProfitStmt->fetchColumn();
$profitGrowth = $prevProfit > 0 ? (($totalProfit - $prevProfit) / $prevProfit * 100) : 0;

$prevAvgStmt = $db->prepare("SELECT COALESCE(AVG(total_amount), 0) FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'");
$prevAvgStmt->execute([$prevDateFrom, $prevDateTo]);
$prevAvg = $prevAvgStmt->fetchColumn();
$avgGrowth = $prevAvg > 0 ? (($revenueStats['avg_order_value'] - $prevAvg) / $prevAvg * 100) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
</head>
<body>
  <div class="wrapper">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-content">
      <?php include '../components/header.php'; ?>

      <div class="content">
        <div class="page-title">
          <h1>Báo cáo & Thống kê</h1>
          <div class="breadcrumb">Trang chủ / Báo cáo</div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group">
              <label>Từ ngày</label>
              <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="filter-group">
              <label>Đến ngày</label>
              <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Xem báo cáo
              </button>
            </div>
          </form>
        </div>

        <!-- Revenue Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="stat-info">
                <h4>Doanh thu</h4>
                <div class="stat-value"><?php echo number_format($revenueStats['total_revenue'] / 1000000, 1); ?>M</div>
                <div class="stat-change <?php echo $revenueGrowth >= 0 ? 'up' : 'down'; ?>">
                  <i class="bi bi-arrow-<?php echo $revenueGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                  <?php echo $revenueGrowth >= 0 ? '+' : ''; ?><?php echo number_format($revenueGrowth, 1); ?>%
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-graph-up"></i>
              </div>
              <div class="stat-info">
                <h4>Lợi nhuận</h4>
                <div class="stat-value"><?php echo number_format($totalProfit / 1000000, 1); ?>M</div>
                <div class="stat-change <?php echo $profitGrowth >= 0 ? 'up' : 'down'; ?>">
                  <i class="bi bi-arrow-<?php echo $profitGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                  <?php echo $profitGrowth >= 0 ? '+' : ''; ?><?php echo number_format($profitGrowth, 1); ?>%
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-receipt"></i>
              </div>
              <div class="stat-info">
                <h4>Đơn hàng</h4>
                <div class="stat-value"><?php echo number_format($revenueStats['total_orders']); ?></div>
                <div class="stat-change <?php echo $ordersGrowth >= 0 ? 'up' : 'down'; ?>">
                  <i class="bi bi-arrow-<?php echo $ordersGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                  <?php echo $ordersGrowth >= 0 ? '+' : ''; ?><?php echo number_format($ordersGrowth, 1); ?>%
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-cart"></i>
              </div>
              <div class="stat-info">
                <h4>Giá trị TB</h4>
                <div class="stat-value"><?php echo number_format($revenueStats['avg_order_value'] / 1000000, 1); ?>M</div>
                <div class="stat-change <?php echo $avgGrowth >= 0 ? 'up' : 'down'; ?>">
                  <i class="bi bi-arrow-<?php echo $avgGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                  <?php echo $avgGrowth >= 0 ? '+' : ''; ?><?php echo number_format($avgGrowth, 1); ?>%
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="card">
          <div class="card-header">
            <h3>Top 10 sản phẩm bán chạy</h3>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">
              <i class="bi bi-printer"></i> In báo cáo
            </button>
          </div>
          <div class="card-body">
            <?php if (empty($topProducts)): ?>
            <div class="empty-state" style="padding: 60px;">
              <i class="bi bi-box-seam" style="font-size: 3rem; color: #cbd5e1;"></i>
              <p style="color: #94a3b8; margin-top: 12px;">Không có dữ liệu trong khoảng thời gian này</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Xếp hạng</th>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Số lượng bán</th>
                    <th>Doanh thu</th>
                    <th>Lợi nhuận</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $rank = 1;
                  $medals = ['🥇', '🥈', '🥉'];
                  foreach ($topProducts as $product): 
                  ?>
                  <tr>
                    <td>
                      <span class="badge <?php echo $rank <= 3 ? 'badge-warning' : 'badge-secondary'; ?>" style="font-size: 1rem;">
                        <?php echo $rank <= 3 ? $medals[$rank - 1] : ''; ?> <?php echo $rank; ?>
                      </span>
                    </td>
                    <td><strong><?php echo htmlspecialchars($product['sku']); ?></strong></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>
                      <?php if ($product['category_name']): ?>
                      <span class="badge badge-primary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                      <?php else: ?>
                      <span style="color: #94a3b8;">-</span>
                      <?php endif; ?>
                    </td>
                    <td><strong><?php echo number_format($product['total_sold']); ?></strong></td>
                    <td><strong class="text-primary"><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>₫</strong></td>
                    <td><strong class="text-success"><?php echo number_format($product['total_profit'], 0, ',', '.'); ?>₫</strong></td>
                  </tr>
                  <?php 
                  $rank++;
                  endforeach; 
                  ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
</body>
</html>
