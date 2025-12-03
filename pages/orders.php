<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Quản lý đơn hàng";
$current_page = "orders";
$base_url = "../";

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
$canEdit = in_array($role_id, [1, 2, 3]); // Admin, Manager, Sales

$db = getDB();

// Lấy danh sách khách hàng cho dropdown
$customersStmt = $db->query("SELECT id, name, phone FROM customers WHERE status = 'active' ORDER BY name");
$customers = $customersStmt->fetchAll();

// Lấy danh sách sản phẩm cho dropdown
$productsStmt = $db->query("SELECT id, name, price, quantity, sku FROM products WHERE status = 'active' AND quantity > 0 ORDER BY name");
$products = $productsStmt->fetchAll();

// Pagination và filters
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$paymentFilter = isset($_GET['payment']) ? $_GET['payment'] : '';
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$limit = 10;

$where = ["1=1"];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($paymentFilter)) {
    $where[] = "o.payment_method = ?";
    $params[] = $paymentFilter;
}

if (!empty($searchFilter)) {
    $where[] = "(o.order_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
    $search = '%' . $searchFilter . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($dateFrom)) {
    $where[] = "DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "DATE(o.created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = implode(' AND ', $where);

// Đếm tổng
$countStmt = $db->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $whereClause");
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);
$offset = ($currentPage - 1) * $limit;

// Lấy danh sách đơn hàng
$sql = "SELECT o.*, c.name as customer_name, c.phone as customer_phone, u.full_name as user_name 
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE $whereClause 
        ORDER BY o.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Thống kê
$statsStmt = $db->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_orders
    FROM orders");
$stats = $statsStmt->fetch();

function buildQueryString($params, $exclude = []) {
    $query = [];
    foreach ($params as $key => $value) {
        if (!in_array($key, $exclude) && $value !== '' && $value !== null) {
            $query[$key] = $value;
        }
    }
    return http_build_query($query);
}

$filterParams = ['status' => $statusFilter, 'payment' => $paymentFilter, 'search' => $searchFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo];

// Status labels và colors
$statusLabels = [
    'pending' => ['label' => 'Chờ xử lý', 'class' => 'badge-warning'],
    'completed' => ['label' => 'Hoàn thành', 'class' => 'badge-success'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'badge-danger'],
    'refunded' => ['label' => 'Hoàn tiền', 'class' => 'badge-secondary']
];

$paymentLabels = [
    'cash' => 'Tiền mặt',
    'transfer' => 'Chuyển khoản',
    'cod' => 'COD'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .order-number { font-weight: 600; color: var(--primary); }
    .customer-info { line-height: 1.4; }
    .customer-info .name { font-weight: 600; color: var(--dark); }
    .customer-info .phone { font-size: 0.85em; color: var(--text-muted); }
    .amount { font-weight: 700; color: var(--primary); }
    .td-actions { white-space: nowrap; }
    
    /* Nút hoàn tiền */
    .action-btn.refund { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
    .action-btn.refund:hover { background: #d97706; color: white; border-color: #d97706; }
    
    /* Modal CSS - căn giữa hoàn toàn */
    .modal-overlay {
      position: fixed;
      top: 0; 
      left: 0; 
      right: 0; 
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: none !important;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    .modal-overlay.show { 
      display: flex !important; 
    }
    .modal-overlay .modal {
      display: block !important;
      background: #fff;
      border-radius: 16px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0,0,0,0.25);
      width: 90%;
      max-width: 600px;
      margin: 0 auto;
      position: relative;
      animation: modalSlideIn 0.3s ease;
    }
    @keyframes modalSlideIn {
      from { opacity: 0; transform: translateY(-20px) scale(0.95); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px 16px 0 0;
      color: white;
    }
    .modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }
    .modal-header .action-btn { background: rgba(255,255,255,0.2); border: none; color: white; }
    .modal-header .action-btn:hover { background: rgba(255,255,255,0.3); }
    .modal-body { padding: 24px; }
    .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: center;
      gap: 12px;
      background: #f8fafc;
      border-radius: 0 0 16px 16px;
    }
    
    /* Order items */
    .order-items-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
    .order-items-table th, .order-items-table td { 
      padding: 12px 14px; 
      border: 1px solid #e2e8f0; 
      text-align: left;
    }
    .order-items-table th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-weight: 600; color: white; }
    .order-items-table .text-right { text-align: right; }
    .order-items-table tbody tr:hover { background: #f8fafc; }
    
    .product-row {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 10px;
      padding: 12px;
      background: #f8fafc;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
    }
    .product-row select, .product-row input { flex: 1; }
    .product-row .qty-input { max-width: 80px; }
    .product-row .price-display { min-width: 120px; text-align: right; font-weight: 600; color: var(--primary); }
    .product-row .btn-remove { 
      background: #fee2e2; 
      color: #dc2626; 
      border: none; 
      width: 32px; 
      height: 32px; 
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .product-row .btn-remove:hover { background: #dc2626; color: white; }
    
    #orderItemsContainer { max-height: 300px; overflow-y: auto; }
    .order-summary { 
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); 
      padding: 20px; 
      border-radius: 12px; 
      margin-top: 20px;
      border: 1px solid #bae6fd;
    }
    .order-summary-row { 
      display: flex; 
      justify-content: space-between; 
      padding: 8px 0;
      font-size: 0.95rem;
    }
    .order-summary-row.total { 
      font-size: 1.3em; 
      font-weight: 700; 
      color: #1d4ed8;
      border-top: 2px solid #1d4ed8;
      margin-top: 12px;
      padding-top: 12px;
    }
    
    /* View modal - Chi tiết đơn hàng */
    .order-detail-section { 
      margin-bottom: 24px; 
      background: #f8fafc;
      border-radius: 12px;
      padding: 16px;
    }
    .order-detail-section h4 { 
      font-size: 0.9rem; 
      color: #64748b; 
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 2px solid #e2e8f0;
      display: flex;
      align-items: center;
      gap: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .order-detail-section h4 i { color: #667eea; }
    .detail-row { 
      display: flex; 
      justify-content: space-between; 
      padding: 10px 0;
      border-bottom: 1px dashed #e2e8f0;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { color: #64748b; font-weight: 500; }
    .detail-row .value { font-weight: 600; color: #1e293b; }

    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }
    
    .alert { padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #d1fae5; color: #047857; }
    .alert-danger { background: #fecaca; color: #b91c1c; }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-content">
      <?php include '../components/header.php'; ?>

      <div class="content">
        <div class="page-title">
          <h1>Quản lý đơn hàng</h1>
          <div class="breadcrumb">Trang chủ / Đơn hàng</div>
        </div>

        <div id="alertContainer"></div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-receipt"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng đơn hàng</h4>
                <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-change"><i class="bi bi-calendar-day"></i> <?php echo $stats['today_orders']; ?> đơn hôm nay</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-hourglass-split"></i>
              </div>
              <div class="stat-info">
                <h4>Chờ xử lý</h4>
                <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="stat-change">Cần xử lý</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Hoàn thành</h4>
                <div class="stat-value"><?php echo number_format($stats['completed_orders']); ?></div>
                <div class="stat-change up"><i class="bi bi-graph-up"></i> Đã giao</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon purple">
                <i class="bi bi-cash-stack"></i>
              </div>
              <div class="stat-info">
                <h4>Doanh thu</h4>
                <div class="stat-value"><?php echo number_format($stats['total_revenue'] / 1000000, 1); ?>M</div>
                <div class="stat-change">Từ đơn hoàn thành</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Mã đơn, tên KH, SĐT..." value="<?php echo htmlspecialchars($searchFilter); ?>">
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Hoàn tiền</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Thanh toán</label>
              <select name="payment" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="cash" <?php echo $paymentFilter === 'cash' ? 'selected' : ''; ?>>Tiền mặt</option>
                <option value="transfer" <?php echo $paymentFilter === 'transfer' ? 'selected' : ''; ?>>Chuyển khoản</option>
                <option value="cod" <?php echo $paymentFilter === 'cod' ? 'selected' : ''; ?>>COD</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Từ ngày</label>
              <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>" onchange="this.form.submit()">
            </div>
            <div class="filter-group">
              <label>Đến ngày</label>
              <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>" onchange="this.form.submit()">
            </div>
            <?php if ($canEdit): ?>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Tạo đơn hàng
              </button>
            </div>
            <?php endif; ?>
          </form>
        </div>

        <div style="margin-bottom: 16px; color: var(--text-muted);">
          Hiển thị <?php echo count($orders); ?> / <?php echo $totalOrders; ?> đơn hàng
        </div>

        <!-- Orders Table -->
        <?php if (empty($orders)): ?>
        <div class="empty-state">
          <i class="bi bi-receipt"></i>
          <h3>Không có đơn hàng nào</h3>
          <p>Thử thay đổi bộ lọc hoặc tạo đơn hàng mới</p>
        </div>
        <?php else: ?>
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order): 
                    // Lấy số lượng sản phẩm trong đơn
                    $itemsStmt = $db->prepare("SELECT COUNT(*) as count, SUM(quantity) as total_qty FROM order_items WHERE order_id = ?");
                    $itemsStmt->execute([$order['id']]);
                    $itemsInfo = $itemsStmt->fetch();
                  ?>
                  <tr>
                    <td>
                      <span class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </td>
                    <td>
                      <div class="customer-info">
                        <div class="name"><?php echo htmlspecialchars($order['customer_name'] ?? 'Khách lẻ'); ?></div>
                        <?php if ($order['customer_phone']): ?>
                        <div class="phone"><i class="bi bi-phone"></i> <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <span><?php echo $itemsInfo['count']; ?> sản phẩm</span>
                      <small class="d-block text-muted">(<?php echo $itemsInfo['total_qty']; ?> items)</small>
                    </td>
                    <td>
                      <span class="amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span>
                      <?php if ($order['discount'] > 0): ?>
                      <small class="d-block text-success">-<?php echo number_format($order['discount'], 0, ',', '.'); ?>₫</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge badge-info"><?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?></span>
                    </td>
                    <td>
                      <?php $st = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'badge-secondary']; ?>
                      <span class="badge <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span>
                    </td>
                    <td>
                      <div><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                      <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                    </td>
                    <td class="td-actions">
                      <button class="action-btn view" onclick="viewOrder(<?php echo $order['id']; ?>)" title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                      </button>
                      <?php if ($canEdit && $order['status'] === 'pending'): ?>
                      <button class="action-btn edit" onclick="editOrder(<?php echo $order['id']; ?>)" title="Sửa">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="action-btn success" onclick="updateStatus(<?php echo $order['id']; ?>, 'completed')" title="Hoàn thành">
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button class="action-btn delete" onclick="updateStatus(<?php echo $order['id']; ?>, 'cancelled')" title="Hủy đơn">
                        <i class="bi bi-x-lg"></i>
                      </button>
                      <?php endif; ?>
                      <?php if ($canEdit && $order['status'] === 'completed'): ?>
                      <button class="action-btn refund" onclick="refundOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')" title="Hoàn tiền">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php $queryBase = buildQueryString($filterParams); $queryBase = $queryBase ? '&' . $queryBase : ''; ?>
          <?php if ($currentPage > 1): ?>
          <a href="?page=<?php echo $currentPage - 1; ?><?php echo $queryBase; ?>"><button><i class="bi bi-chevron-left"></i></button></a>
          <?php endif; ?>
          <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
          <a href="?page=<?php echo $i; ?><?php echo $queryBase; ?>"><button class="<?php echo $i == $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></button></a>
          <?php endfor; ?>
          <?php if ($currentPage < $totalPages): ?>
          <a href="?page=<?php echo $currentPage + 1; ?><?php echo $queryBase; ?>"><button><i class="bi bi-chevron-right"></i></button></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <!-- Modal Tạo/Sửa Đơn hàng -->
  <div class="modal-overlay" id="orderModal">
    <div class="modal" style="max-width: 750px;">
      <div class="modal-header">
        <h3 id="modalTitle"><i class="bi bi-cart-plus me-2"></i>Tạo đơn hàng mới</h3>
        <button class="action-btn" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <form id="orderForm">
          <input type="hidden" id="orderId" name="id">
          
          <div class="form-row">
            <div class="form-group">
              <label>Khách hàng</label>
              <select id="orderCustomer" name="customer_id" class="form-control">
                <option value="">-- Khách lẻ --</option>
                <?php foreach ($customers as $cus): ?>
                <option value="<?php echo $cus['id']; ?>"><?php echo htmlspecialchars($cus['name'] . ' - ' . $cus['phone']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Phương thức thanh toán</label>
              <select id="orderPayment" name="payment_method" class="form-control">
                <option value="cash">Tiền mặt</option>
                <option value="transfer">Chuyển khoản</option>
                <option value="cod">COD (Ship COD)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Sản phẩm <span style="color:red">*</span></label>
            <div id="orderItemsContainer">
              <!-- Product rows will be added here -->
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addProductRow()">
              <i class="bi bi-plus"></i> Thêm sản phẩm
            </button>
          </div>

          <div class="order-summary">
            <div class="order-summary-row">
              <span>Tạm tính:</span>
              <span id="subtotalDisplay">0₫</span>
            </div>
            <div class="order-summary-row">
              <span>Giảm giá:</span>
              <input type="number" id="orderDiscount" name="discount" class="form-control" style="width: 150px; text-align: right;" value="0" min="0" onchange="calculateTotal()">
            </div>
            <div class="order-summary-row total">
              <span>Tổng cộng:</span>
              <span id="totalDisplay">0₫</span>
            </div>
          </div>

          <div class="form-group mt-3">
            <label>Ghi chú</label>
            <textarea id="orderNotes" name="notes" class="form-control" rows="2" placeholder="Ghi chú cho đơn hàng..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="saveOrder()">
          <i class="bi bi-check-lg"></i> Lưu đơn hàng
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Xem Chi tiết -->
  <div class="modal-overlay" id="viewModal">
    <div class="modal" style="max-width: 650px;">
      <div class="modal-header">
        <h3><i class="bi bi-receipt me-2"></i>Chi tiết đơn hàng <span id="viewOrderNumber"></span></h3>
        <button class="action-btn" onclick="closeViewModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <!-- Content loaded via JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeViewModal()">
          <i class="bi bi-x-circle"></i> Đóng
        </button>
        <button type="button" class="btn btn-primary" onclick="printOrder()">
          <i class="bi bi-printer"></i> In đơn
        </button>
      </div>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
  
  <script>
    // Products data for JS
    const productsData = <?php echo json_encode($products); ?>;
    
    function formatMoney(amount) {
      return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    }

    function showAlert(type, message) {
      const alertHtml = `<div class="alert alert-${type}"><i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}</div>`;
      document.getElementById('alertContainer').innerHTML = alertHtml;
      setTimeout(() => { document.getElementById('alertContainer').innerHTML = ''; }, 3000);
    }

    // Modal functions
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'Tạo đơn hàng mới';
      document.getElementById('orderForm').reset();
      document.getElementById('orderId').value = '';
      document.getElementById('orderItemsContainer').innerHTML = '';
      addProductRow();
      calculateTotal();
      document.getElementById('orderModal').classList.add('show');
    }

    function closeModal() {
      document.getElementById('orderModal').classList.remove('show');
    }

    function closeViewModal() {
      document.getElementById('viewModal').classList.remove('show');
    }

    // Product row management
    let rowIndex = 0;
    function addProductRow(productId = '', quantity = 1) {
      const container = document.getElementById('orderItemsContainer');
      const row = document.createElement('div');
      row.className = 'product-row';
      row.id = 'productRow' + rowIndex;
      
      let options = '<option value="">-- Chọn sản phẩm --</option>';
      productsData.forEach(p => {
        const selected = p.id == productId ? 'selected' : '';
        options += `<option value="${p.id}" data-price="${p.price}" data-stock="${p.quantity}" ${selected}>${p.name} (${p.sku}) - ${formatMoney(p.price)} - Còn: ${p.quantity}</option>`;
      });
      
      row.innerHTML = `
        <select class="form-control product-select" onchange="updateRowPrice(${rowIndex})">
          ${options}
        </select>
        <input type="number" class="form-control qty-input" value="${quantity}" min="1" onchange="updateRowPrice(${rowIndex})" placeholder="SL">
        <div class="price-display" id="priceDisplay${rowIndex}">0₫</div>
        <button type="button" class="btn-remove" onclick="removeProductRow(${rowIndex})"><i class="bi bi-trash"></i></button>
      `;
      
      container.appendChild(row);
      rowIndex++;
      updateRowPrice(rowIndex - 1);
    }

    function removeProductRow(idx) {
      const row = document.getElementById('productRow' + idx);
      if (row) {
        row.remove();
        calculateTotal();
      }
    }

    function updateRowPrice(idx) {
      const row = document.getElementById('productRow' + idx);
      if (!row) return;
      
      const select = row.querySelector('.product-select');
      const qtyInput = row.querySelector('.qty-input');
      const priceDisplay = row.querySelector('.price-display');
      
      const option = select.options[select.selectedIndex];
      const price = parseFloat(option.dataset.price) || 0;
      const qty = parseInt(qtyInput.value) || 0;
      
      priceDisplay.textContent = formatMoney(price * qty);
      calculateTotal();
    }

    function calculateTotal() {
      let subtotal = 0;
      document.querySelectorAll('.product-row').forEach(row => {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.dataset.price) || 0;
        const qty = parseInt(qtyInput.value) || 0;
        subtotal += price * qty;
      });
      
      const discount = parseFloat(document.getElementById('orderDiscount').value) || 0;
      const total = subtotal - discount;
      
      document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
      document.getElementById('totalDisplay').textContent = formatMoney(total > 0 ? total : 0);
    }

    // AJAX functions
    async function saveOrder() {
      const items = [];
      let valid = true;
      
      document.querySelectorAll('.product-row').forEach(row => {
        const select = row.querySelector('.product-select');
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        const productId = select.value;
        
        if (productId && qty > 0) {
          const option = select.options[select.selectedIndex];
          items.push({
            product_id: productId,
            quantity: qty,
            unit_price: parseFloat(option.dataset.price) || 0
          });
        }
      });

      if (items.length === 0) {
        showAlert('danger', 'Vui lòng chọn ít nhất 1 sản phẩm');
        return;
      }

      const formData = {
        id: document.getElementById('orderId').value,
        customer_id: document.getElementById('orderCustomer').value,
        payment_method: document.getElementById('orderPayment').value,
        discount: document.getElementById('orderDiscount').value || 0,
        notes: document.getElementById('orderNotes').value,
        items: items
      };

      try {
        const response = await fetch('../api/orders.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
          showAlert('success', result.message);
          closeModal();
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert('danger', result.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        showAlert('danger', 'Lỗi kết nối server');
      }
    }

    async function viewOrder(id) {
      try {
        const response = await fetch(`../api/orders.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
          const order = result.data;
          document.getElementById('viewOrderNumber').textContent = order.order_number;
          
          let itemsHtml = '';
          order.items.forEach((item, idx) => {
            itemsHtml += `
              <tr>
                <td>${idx + 1}</td>
                <td>${item.product_name}<br><small class="text-muted">${item.sku}</small></td>
                <td class="text-right">${formatMoney(item.unit_price)}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-right">${formatMoney(item.subtotal)}</td>
              </tr>
            `;
          });

          const statusLabels = {
            pending: '<span class="badge badge-warning">Chờ xử lý</span>',
            completed: '<span class="badge badge-success">Hoàn thành</span>',
            cancelled: '<span class="badge badge-danger">Đã hủy</span>',
            refunded: '<span class="badge badge-secondary">Hoàn tiền</span>'
          };

          const paymentLabels = {
            cash: 'Tiền mặt',
            card: 'Thẻ',
            transfer: 'Chuyển khoản',
            cod: 'COD'
          };

          document.getElementById('viewModalBody').innerHTML = `
            <div class="order-detail-section">
              <h4><i class="bi bi-info-circle"></i> Thông tin đơn hàng</h4>
              <div class="detail-row">
                <span class="label">Mã đơn:</span>
                <span class="value">${order.order_number}</span>
              </div>
              <div class="detail-row">
                <span class="label">Ngày tạo:</span>
                <span class="value">${new Date(order.created_at).toLocaleString('vi-VN')}</span>
              </div>
              <div class="detail-row">
                <span class="label">Trạng thái:</span>
                <span class="value">${statusLabels[order.status] || order.status}</span>
              </div>
              <div class="detail-row">
                <span class="label">Thanh toán:</span>
                <span class="value">${paymentLabels[order.payment_method] || order.payment_method}</span>
              </div>
            </div>

            <div class="order-detail-section">
              <h4><i class="bi bi-person"></i> Khách hàng</h4>
              <div class="detail-row">
                <span class="label">Tên:</span>
                <span class="value">${order.customer_name || 'Khách lẻ'}</span>
              </div>
              ${order.customer_phone ? `<div class="detail-row"><span class="label">SĐT:</span><span class="value">${order.customer_phone}</span></div>` : ''}
            </div>

            <div class="order-detail-section">
              <h4><i class="bi bi-box"></i> Sản phẩm</h4>
              <table class="order-items-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th class="text-right">Đơn giá</th>
                    <th class="text-center">SL</th>
                    <th class="text-right">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  ${itemsHtml}
                </tbody>
              </table>
            </div>

            <div class="order-summary">
              <div class="order-summary-row">
                <span>Tạm tính:</span>
                <span>${formatMoney(order.subtotal)}</span>
              </div>
              <div class="order-summary-row">
                <span>Giảm giá:</span>
                <span>-${formatMoney(order.discount)}</span>
              </div>
              <div class="order-summary-row total">
                <span>Tổng cộng:</span>
                <span>${formatMoney(order.total_amount)}</span>
              </div>
            </div>

            ${order.notes ? `<div class="mt-3"><strong>Ghi chú:</strong> ${order.notes}</div>` : ''}
          `;

          document.getElementById('viewModal').classList.add('show');
        }
      } catch (error) {
        showAlert('danger', 'Không thể tải thông tin đơn hàng');
      }
    }

    async function editOrder(id) {
      try {
        const response = await fetch(`../api/orders.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
          const order = result.data;
          
          document.getElementById('modalTitle').textContent = 'Sửa đơn hàng #' + order.order_number;
          document.getElementById('orderId').value = order.id;
          document.getElementById('orderCustomer').value = order.customer_id || '';
          document.getElementById('orderPayment').value = order.payment_method;
          document.getElementById('orderDiscount').value = order.discount;
          document.getElementById('orderNotes').value = order.notes || '';
          
          // Load items
          document.getElementById('orderItemsContainer').innerHTML = '';
          rowIndex = 0;
          order.items.forEach(item => {
            addProductRow(item.product_id, item.quantity);
          });
          
          calculateTotal();
          document.getElementById('orderModal').classList.add('show');
        }
      } catch (error) {
        showAlert('danger', 'Không thể tải thông tin đơn hàng');
      }
    }

    async function updateStatus(id, status) {
      const statusText = {
        completed: 'hoàn thành',
        cancelled: 'hủy'
      };
      
      if (!confirm(`Bạn có chắc muốn ${statusText[status]} đơn hàng này?`)) return;

      try {
        const response = await fetch('../api/orders.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, status: status })
        });
        
        const result = await response.json();
        
        if (result.success) {
          showAlert('success', result.message);
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert('danger', result.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        showAlert('danger', 'Lỗi kết nối server');
      }
    }

    function printOrder() {
      window.print();
    }

    // Hàm hoàn tiền đơn hàng
    async function refundOrder(id, orderNumber) {
      if (!confirm(`Bạn có chắc muốn hoàn tiền đơn hàng ${orderNumber}?\n\nThao tác này sẽ:\n- Chuyển trạng thái sang "Hoàn tiền"\n- Hoàn lại số lượng tồn kho\n- Trừ tổng mua của khách hàng`)) {
        return;
      }

      try {
        const response = await fetch('../api/orders.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, status: 'refunded' })
        });
        
        const result = await response.json();
        
        if (result.success) {
          showAlert('success', result.message);
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert('danger', result.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        showAlert('danger', 'Lỗi kết nối server');
      }
    }
  </script>
</body>
</html>
