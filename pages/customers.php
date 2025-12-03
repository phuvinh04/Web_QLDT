<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Quản lý khách hàng";
$current_page = "customers";
$base_url = "../";

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;

// Kiểm tra quyền: Admin, Manager, Sales
if (!in_array($role_id, [1, 2, 3])) {
    header("Location: ../index.php");
    exit;
}

$canEdit = in_array($role_id, [1, 2, 3]);
$canDelete = in_array($role_id, [1, 2]);

$db = getDB();

// Lấy danh sách thành phố để filter
$citiesStmt = $db->query("SELECT DISTINCT city FROM customers WHERE city IS NOT NULL AND city != '' ORDER BY city");
$cities = $citiesStmt->fetchAll(PDO::FETCH_COLUMN);

// Params filter
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$cityFilter = isset($_GET['city']) ? $_GET['city'] : '';
$sortFilter = isset($_GET['sort']) ? $_GET['sort'] : '';
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 15;

$where = ["1=1"];
$params = [];

if ($statusFilter !== '') {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if ($cityFilter !== '') {
    $where[] = "city = ?";
    $params[] = $cityFilter;
}

if (!empty($searchFilter)) {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search = '%' . $searchFilter . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$whereClause = implode(' AND ', $where);

$orderBy = "created_at DESC";
switch ($sortFilter) {
    case 'name_asc': $orderBy = "name ASC"; break;
    case 'name_desc': $orderBy = "name DESC"; break;
    case 'purchases_desc': $orderBy = "total_purchases DESC"; break;
    case 'orders_desc': $orderBy = "purchase_count DESC"; break;
    case 'points_desc': $orderBy = "loyalty_points DESC"; break;
    case 'oldest': $orderBy = "created_at ASC"; break;
}

// Đếm tổng
$countStmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE $whereClause");
$countStmt->execute($params);
$totalCustomers = $countStmt->fetchColumn();
$totalPages = ceil($totalCustomers / $limit);
$offset = ($currentPage - 1) * $limit;

// Lấy danh sách
$sql = "SELECT * FROM customers WHERE $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Thống kê
$statsStmt = $db->query("
    SELECT 
        COUNT(*) as total_customers,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_customers,
        COALESCE(SUM(total_purchases), 0) as total_revenue,
        COALESCE(SUM(purchase_count), 0) as total_orders
    FROM customers
");
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

$filterParams = ['status' => $statusFilter, 'city' => $cityFilter, 'sort' => $sortFilter, 'search' => $searchFilter];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px; }
    .stat-card .stat-icon.blue { background: #dbeafe; color: #2563eb; }
    .stat-card .stat-icon.green { background: #d1fae5; color: #059669; }
    .stat-card .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
    .stat-card .stat-icon.orange { background: #ffedd5; color: #ea580c; }
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
    .stat-card .stat-label { color: #64748b; font-size: 0.875rem; margin-top: 4px; }
    
    .customer-table { width: 100%; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .customer-table th, .customer-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .customer-table th { background: #f8fafc; font-weight: 600; color: #475569; font-size: 0.875rem; }
    .customer-table tr:hover { background: #f8fafc; }
    .customer-table tr:last-child td { border-bottom: none; }
    
    .customer-name { font-weight: 600; color: #1e293b; }
    .customer-contact { font-size: 0.875rem; color: #64748b; }
    .customer-contact i { margin-right: 4px; }
    
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .badge-success { background: #d1fae5; color: #059669; }
    .badge-danger { background: #fee2e2; color: #dc2626; }
    .badge-warning { background: #fef3c7; color: #d97706; }
    .badge-info { background: #dbeafe; color: #2563eb; }
    
    .loyalty-points { display: inline-flex; align-items: center; gap: 4px; background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 0.875rem; font-weight: 600; }
    .loyalty-points i { font-size: 0.75rem; }
    
    .action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .action-btn.view { background: #dbeafe; color: #2563eb; }
    .action-btn.edit { background: #d1fae5; color: #059669; }
    .action-btn.delete { background: #fee2e2; color: #dc2626; }
    .action-btn:hover { transform: scale(1.1); }
    
    .search-box { position: relative; }
    .search-box input { padding-right: 40px; }
    .search-box button { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; }
    
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #d1fae5; color: #047857; }
    .alert-danger { background: #fecaca; color: #b91c1c; }
    
    .btn-outline { background: transparent; border: 2px solid var(--border); color: var(--dark); }
    .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
    
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .modal-overlay.show { display: flex !important; align-items: center; justify-content: center; }
    .modal-overlay .modal { display: block !important; background: #fff; border-radius: 12px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; margin: auto; }
    .modal-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 1.1rem; }
    .modal-body { padding: 20px; }
    .modal-footer { padding: 16px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }
    
    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }
    
    .empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; }
    .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 16px; }
    .empty-state h3 { color: #475569; margin-bottom: 8px; }
    .empty-state p { color: #94a3b8; }
    
    .order-history { margin-top: 16px; }
    .order-history h4 { font-size: 0.9rem; color: #475569; margin-bottom: 12px; }
    .order-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px; }
    .order-item:last-child { margin-bottom: 0; }
    .order-number { font-weight: 600; color: #2563eb; }
    .order-amount { font-weight: 600; color: #059669; }
    
    @media (max-width: 1024px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .stats-grid { grid-template-columns: 1fr; }
      .customer-table { display: block; overflow-x: auto; }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php include '../components/sidebar.php'; ?>
    <div class="main-content">
      <?php include '../components/header.php'; ?>
      <div class="content">
        <div class="page-title">
          <h1>Quản lý khách hàng</h1>
          <div class="breadcrumb">Trang chủ / Khách hàng</div>
        </div>

        <div id="alertContainer"></div>

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div class="stat-value"><?php echo number_format($stats['total_customers']); ?></div>
            <div class="stat-label">Tổng khách hàng</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-person-check"></i></div>
            <div class="stat-value"><?php echo number_format($stats['active_customers']); ?></div>
            <div class="stat-label">Đang hoạt động</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-cart-check"></i></div>
            <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
            <div class="stat-label">Tổng đơn hàng</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>₫</div>
            <div class="stat-label">Tổng doanh thu</div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group search-box">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tên, SĐT, Email..." value="<?php echo htmlspecialchars($searchFilter); ?>">
              <button type="submit"><i class="bi bi-search"></i></button>
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Thành phố</label>
              <select name="city" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <?php foreach ($cities as $city): ?>
                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $cityFilter === $city ? 'selected' : ''; ?>><?php echo htmlspecialchars($city); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <label>Sắp xếp</label>
              <select name="sort" class="form-control" onchange="this.form.submit()">
                <option value="">Mới nhất</option>
                <option value="name_asc" <?php echo $sortFilter === 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                <option value="purchases_desc" <?php echo $sortFilter === 'purchases_desc' ? 'selected' : ''; ?>>Doanh thu cao nhất</option>
                <option value="orders_desc" <?php echo $sortFilter === 'orders_desc' ? 'selected' : ''; ?>>Đơn hàng nhiều nhất</option>
                <option value="points_desc" <?php echo $sortFilter === 'points_desc' ? 'selected' : ''; ?>>Điểm tích lũy cao</option>
              </select>
            </div>
            <?php if ($canEdit): ?>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Thêm khách hàng
              </button>
            </div>
            <?php endif; ?>
          </form>
        </div>

        <div style="margin-bottom: 16px; color: var(--text-muted);">
          Hiển thị <?php echo count($customers); ?> / <?php echo $totalCustomers; ?> khách hàng
        </div>

        <?php if (empty($customers)): ?>
        <div class="empty-state">
          <i class="bi bi-people"></i>
          <h3>Không có khách hàng nào</h3>
          <p>Thử thay đổi bộ lọc hoặc thêm khách hàng mới</p>
        </div>
        <?php else: ?>
        <table class="customer-table">
          <thead>
            <tr>
              <th>Khách hàng</th>
              <th>Liên hệ</th>
              <th>Địa chỉ</th>
              <th>Đơn hàng</th>
              <th>Tổng mua</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
              <td>
                <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                <div class="customer-contact"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></div>
              </td>
              <td>
                <?php if ($customer['phone']): ?>
                <div class="customer-contact"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($customer['phone']); ?></div>
                <?php endif; ?>
                <?php if ($customer['email']): ?>
                <div class="customer-contact"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($customer['address'] || $customer['city']): ?>
                <div class="customer-contact">
                  <?php echo htmlspecialchars($customer['address'] ?? ''); ?>
                  <?php if ($customer['city']): ?><br><?php echo htmlspecialchars($customer['city']); ?><?php endif; ?>
                </div>
                <?php else: ?>
                <span style="color: #94a3b8;">-</span>
                <?php endif; ?>
              </td>
              <td><strong><?php echo $customer['purchase_count']; ?></strong></td>
              <td><strong style="color: #059669;"><?php echo number_format($customer['total_purchases'], 0, ',', '.'); ?>₫</strong></td>
              <td>
                <span class="badge <?php echo $customer['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                  <?php echo $customer['status'] === 'active' ? 'Hoạt động' : 'Ngừng HĐ'; ?>
                </span>
              </td>
              <td>
                <button class="action-btn view" onclick="viewCustomer(<?php echo $customer['id']; ?>)" title="Xem chi tiết"><i class="bi bi-eye"></i></button>
                <?php if ($canEdit): ?>
                <button class="action-btn edit" onclick="editCustomer(<?php echo $customer['id']; ?>)" title="Sửa"><i class="bi bi-pencil"></i></button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <button class="action-btn delete" onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars(addslashes($customer['name'])); ?>')" title="Xóa"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>

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

  <!-- Modal Thêm/Sửa -->
  <div class="modal-overlay" id="customerModal">
    <div class="modal" style="max-width: 600px; width: 90%;">
      <div class="modal-header">
        <h3 id="modalTitle">Thêm khách hàng mới</h3>
        <button class="action-btn" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <form id="customerForm">
          <input type="hidden" id="customerId" name="id">
          <div class="form-group">
            <label>Họ tên <span style="color:red">*</span></label>
            <input type="text" id="customerName" name="name" class="form-control" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Số điện thoại</label>
              <input type="text" id="customerPhone" name="phone" class="form-control" placeholder="0912345678">
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" id="customerEmail" name="email" class="form-control" placeholder="email@example.com">
            </div>
          </div>
          <div class="form-group">
            <label>Địa chỉ</label>
            <input type="text" id="customerAddress" name="address" class="form-control" placeholder="Số nhà, đường, phường/xã...">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Thành phố</label>
              <input type="text" id="customerCity" name="city" class="form-control" placeholder="TP. Hồ Chí Minh">
            </div>
            <div class="form-group">
              <label>Trạng thái</label>
              <select id="customerStatus" name="status" class="form-control">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
              </select>
            </div>
          </div>
          <div class="form-group" id="pointsGroup" style="display: none;">
            <label>Điểm tích lũy</label>
            <input type="number" id="customerPoints" name="loyalty_points" class="form-control" min="0">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="saveCustomer()"><i class="bi bi-check-lg"></i> Lưu</button>
      </div>
    </div>
  </div>

  <!-- Modal Xem Chi tiết -->
  <div class="modal-overlay" id="viewModal">
    <div class="modal" style="max-width: 600px; width: 90%;">
      <div class="modal-header">
        <h3>Chi tiết khách hàng</h3>
        <button class="action-btn" onclick="closeViewModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body" id="viewModalContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeViewModal()">Đóng</button>
        <?php if ($canEdit): ?>
        <button type="button" class="btn btn-primary" id="editFromViewBtn"><i class="bi bi-pencil"></i> Sửa</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal Xóa -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width: 400px; width: 90%;">
      <div class="modal-header">
        <h3>Xác nhận xóa</h3>
        <button class="action-btn" onclick="closeDeleteModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <p>Bạn có chắc muốn xóa khách hàng <strong id="deleteCustomerName"></strong>?</p>
        <p style="color: var(--danger); font-size: 0.9rem;">Hành động này không thể hoàn tác!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i class="bi bi-trash"></i> Xóa</button>
      </div>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>

  <script>
    const API_URL = '../api/customers.php';
    const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
    const canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;
    let currentCustomerId = null;
    let deleteCustomerId = null;

    function showAlert(message, type = 'success') {
      const container = document.getElementById('alertContainer');
      if (!container) return;
      const alert = document.createElement('div');
      alert.className = `alert alert-${type}`;
      alert.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
      container.appendChild(alert);
      setTimeout(() => alert.remove(), 5000);
    }

    function openAddModal() {
      const modal = document.getElementById('customerModal');
      if (!modal) return;
      document.getElementById('modalTitle').textContent = 'Thêm khách hàng mới';
      document.getElementById('customerForm').reset();
      document.getElementById('customerId').value = '';
      document.getElementById('pointsGroup').style.display = 'none';
      modal.classList.add('show');
    }

    function closeModal() {
      document.getElementById('customerModal')?.classList.remove('show');
    }

    function closeViewModal() {
      document.getElementById('viewModal')?.classList.remove('show');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal')?.classList.remove('show');
      deleteCustomerId = null;
    }

    async function viewCustomer(id) {
      try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        if (result.success) {
          const c = result.data;
          let ordersHtml = '';
          if (c.recent_orders && c.recent_orders.length > 0) {
            ordersHtml = `
              <div class="order-history">
                <h4><i class="bi bi-receipt"></i> Đơn hàng gần đây</h4>
                ${c.recent_orders.map(o => `
                  <div class="order-item">
                    <div>
                      <span class="order-number">#${o.order_number}</span>
                      <span style="color: #64748b; font-size: 0.8rem; margin-left: 8px;">${new Date(o.created_at).toLocaleDateString('vi-VN')}</span>
                    </div>
                    <div>
                      <span class="order-amount">${Number(o.total_amount).toLocaleString('vi-VN')}₫</span>
                      <span class="badge ${o.status === 'completed' ? 'badge-success' : o.status === 'pending' ? 'badge-warning' : 'badge-danger'}" style="margin-left: 8px;">
                        ${o.status === 'completed' ? 'Hoàn thành' : o.status === 'pending' ? 'Chờ xử lý' : o.status === 'cancelled' ? 'Đã hủy' : 'Hoàn tiền'}
                      </span>
                    </div>
                  </div>
                `).join('')}
              </div>
            `;
          }
          
          document.getElementById('viewModalContent').innerHTML = `
            <table style="width: 100%;">
              <tr><td style="padding: 8px 0; color: #64748b; width: 140px;">Họ tên</td><td style="padding: 8px 0; font-weight: 600;">${c.name}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Số điện thoại</td><td style="padding: 8px 0;">${c.phone || '<span style="color:#94a3b8">Chưa có</span>'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Email</td><td style="padding: 8px 0;">${c.email || '<span style="color:#94a3b8">Chưa có</span>'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Địa chỉ</td><td style="padding: 8px 0;">${c.address || '<span style="color:#94a3b8">Chưa có</span>'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Thành phố</td><td style="padding: 8px 0;">${c.city || '<span style="color:#94a3b8">Chưa có</span>'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Số đơn hàng</td><td style="padding: 8px 0; font-weight: 600;">${c.purchase_count}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Tổng mua hàng</td><td style="padding: 8px 0; font-weight: 700; color: #059669;">${Number(c.total_purchases).toLocaleString('vi-VN')}₫</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Điểm tích lũy</td><td style="padding: 8px 0;"><span class="loyalty-points"><i class="bi bi-star-fill"></i> ${Number(c.loyalty_points).toLocaleString()}</span></td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Trạng thái</td><td style="padding: 8px 0;"><span class="badge ${c.status === 'active' ? 'badge-success' : 'badge-danger'}">${c.status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động'}</span></td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Ngày tạo</td><td style="padding: 8px 0;">${new Date(c.created_at).toLocaleDateString('vi-VN')}</td></tr>
            </table>
            ${ordersHtml}
          `;
          currentCustomerId = id;
          document.getElementById('viewModal').classList.add('show');
        } else {
          showAlert(result.message, 'danger');
        }
      } catch (error) {
        showAlert('Lỗi kết nối server', 'danger');
      }
    }

    async function editCustomer(id) {
      try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        if (result.success) {
          const c = result.data;
          document.getElementById('modalTitle').textContent = 'Sửa thông tin khách hàng';
          document.getElementById('customerId').value = c.id;
          document.getElementById('customerName').value = c.name;
          document.getElementById('customerPhone').value = c.phone || '';
          document.getElementById('customerEmail').value = c.email || '';
          document.getElementById('customerAddress').value = c.address || '';
          document.getElementById('customerCity').value = c.city || '';
          document.getElementById('customerStatus').value = c.status;
          document.getElementById('customerPoints').value = c.loyalty_points || 0;
          document.getElementById('pointsGroup').style.display = 'block';
          document.getElementById('customerModal').classList.add('show');
        } else {
          showAlert(result.message, 'danger');
        }
      } catch (error) {
        showAlert('Lỗi kết nối server', 'danger');
      }
    }

    async function saveCustomer() {
      const id = document.getElementById('customerId').value;
      const data = {
        name: document.getElementById('customerName').value.trim(),
        phone: document.getElementById('customerPhone').value.trim(),
        email: document.getElementById('customerEmail').value.trim(),
        address: document.getElementById('customerAddress').value.trim(),
        city: document.getElementById('customerCity').value.trim(),
        status: document.getElementById('customerStatus').value
      };

      if (!data.name) {
        showAlert('Vui lòng nhập tên khách hàng', 'danger');
        return;
      }

      // Nếu đang sửa, thêm điểm tích lũy
      if (id) {
        data.loyalty_points = parseInt(document.getElementById('customerPoints').value) || 0;
      }

      try {
        const method = id ? 'PUT' : 'POST';
        if (id) data.id = id;
        
        const response = await fetch(API_URL, {
          method: method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
          showAlert(result.message, 'success');
          closeModal();
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert(result.message, 'danger');
        }
      } catch (error) {
        showAlert('Lỗi kết nối server', 'danger');
      }
    }

    function deleteCustomer(id, name) {
      deleteCustomerId = id;
      document.getElementById('deleteCustomerName').textContent = name;
      document.getElementById('deleteModal').classList.add('show');
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const confirmBtn = document.getElementById('confirmDeleteBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
          if (!deleteCustomerId) return;
          try {
            const response = await fetch(`${API_URL}?id=${deleteCustomerId}`, { method: 'DELETE' });
            const result = await response.json();
            if (result.success) {
              showAlert(result.message, 'success');
              closeDeleteModal();
              setTimeout(() => location.reload(), 1000);
            } else {
              showAlert(result.message, 'danger');
            }
          } catch (error) {
            showAlert('Lỗi kết nối server', 'danger');
          }
        });
      }

      const editFromViewBtn = document.getElementById('editFromViewBtn');
      if (editFromViewBtn) {
        editFromViewBtn.addEventListener('click', () => {
          closeViewModal();
          if (currentCustomerId) editCustomer(currentCustomerId);
        });
      }

      // Click outside modal to close
      document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
          if (e.target === this) this.classList.remove('show');
        });
      });
    });
  </script>
</body>
</html>
