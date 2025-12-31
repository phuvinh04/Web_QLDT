<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Quản lý sản phẩm";
$current_page = "products";
$base_url = "../";

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
$canEdit = in_array($role_id, [1, 2]);

$db = getDB();

$categoriesStmt = $db->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

// Lấy danh sách thương hiệu
$brandsStmt = $db->query("SELECT id, name FROM brands ORDER BY name");
$brands = $brandsStmt->fetchAll();

$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$categoryFilter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$brandFilter = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortFilter = isset($_GET['sort']) ? $_GET['sort'] : '';
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 12;

$where = ["1=1"];
$params = [];

if ($categoryFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($brandFilter > 0) {
    $where[] = "p.brand_id = ?";
    $params[] = $brandFilter;
}

if ($statusFilter === 'in_stock') {
    $where[] = "p.quantity > 0";
} elseif ($statusFilter === 'out_of_stock') {
    $where[] = "p.quantity = 0";
} elseif ($statusFilter === 'low_stock') {
    $where[] = "p.quantity <= p.min_quantity AND p.quantity > 0";
}

if (!empty($searchFilter)) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $search = '%' . $searchFilter . '%';
    $params[] = $search;
    $params[] = $search;
}

$whereClause = implode(' AND ', $where);

$orderBy = "p.created_at DESC";
switch ($sortFilter) {
    case 'price_asc': $orderBy = "p.price ASC"; break;
    case 'price_desc': $orderBy = "p.price DESC"; break;
    case 'name_asc': $orderBy = "p.name ASC"; break;
    case 'quantity_asc': $orderBy = "p.quantity ASC"; break;
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);
$offset = ($currentPage - 1) * $limit;

$sql = "SELECT p.*, c.name as category_name, b.name as brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE $whereClause ORDER BY $orderBy LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

function buildQueryString($params, $exclude = []) {
    $query = [];
    foreach ($params as $key => $value) {
        if (!in_array($key, $exclude) && $value !== '' && $value !== null) {
            $query[$key] = $value;
        }
    }
    return http_build_query($query);
}

$filterParams = ['category_id' => $categoryFilter, 'brand_id' => $brandFilter, 'status' => $statusFilter, 'sort' => $sortFilter, 'search' => $searchFilter];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }
    .product-badge { position: absolute; top: 10px; right: 10px; z-index: 10; }
    .search-box { position: relative; }
    .search-box input { padding-right: 40px; }
    .search-box button { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; }
    .alert { padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #d1fae5; color: #047857; }
    .alert-danger { background: #fecaca; color: #b91c1c; }
    .btn-outline { background: transparent; border: 2px solid var(--border); color: var(--dark); }
    .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
    .image-preview { width: 100%; height: 150px; border: 2px dashed var(--border); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-top: 8px; overflow: hidden; background: var(--light); }
    .image-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .image-preview i { font-size: 2rem; color: var(--text-muted); }
    
    /* Modal CSS - Custom modal đẹp hơn */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      display: none !important;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 20px;
    }
    .modal-overlay.show {
      display: flex !important;
    }
    .modal-overlay .modal {
      display: block !important;
      opacity: 1 !important;
      background: #fff;
      border-radius: 20px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.05);
      position: relative;
      width: 100%;
      max-width: 700px;
      animation: modalSlideIn 0.3s ease-out;
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
      background: linear-gradient(to right, #f8fafc, #fff);
      border-radius: 20px 20px 0 0;
    }
    .modal-header h3 {
      margin: 0;
      font-size: 1.35rem;
      font-weight: 700;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .modal-header .action-btn {
      background: #f1f5f9;
      border: none;
      font-size: 1.25rem;
      cursor: pointer;
      color: #64748b;
      padding: 8px 12px;
      line-height: 1;
      border-radius: 10px;
      transition: all 0.2s;
    }
    .modal-header .action-btn:hover {
      background: #e2e8f0;
      color: #1e293b;
    }
    .modal-body {
      padding: 28px;
      background: #fff;
    }
    .modal-body .form-label {
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
      display: block;
      font-size: 0.85rem;
    }
    .modal-body .form-label .text-danger {
      color: #ef4444;
    }
    .modal-body .form-control,
    .modal-body .form-select {
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 0.95rem;
      transition: all 0.2s;
      background: #fafafa;
    }
    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
      outline: none;
      background: #fff;
    }
    .modal-body .form-control::placeholder {
      color: #9ca3af;
    }
    .modal-body .row {
      margin: 0 -8px;
    }
    .modal-body .row > [class*="col"] {
      padding: 0 8px;
      margin-bottom: 16px;
    }
    .modal-body .form-group {
      margin-bottom: 16px;
    }
    .modal-footer {
      padding: 20px 24px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      background: linear-gradient(to right, #f8fafc, #fff);
      border-radius: 0 0 20px 20px;
    }
    .modal-footer .btn {
      padding: 12px 24px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.2s;
    }
    .modal-footer .btn-secondary {
      background: #f1f5f9;
      color: #475569;
      border: none;
    }
    .modal-footer .btn-secondary:hover {
      background: #e2e8f0;
    }
    .modal-footer .btn-primary {
      background: linear-gradient(135deg, var(--primary), #7c3aed);
      border: none;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .modal-footer .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }
    .modal-footer .btn-danger {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      border: none;
      color: #fff;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .modal-footer .btn-danger:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }
    .image-preview {
      border: 2px dashed #d1d5db;
      border-radius: 12px;
      background: #fafafa;
      transition: all 0.2s;
    }
    .image-preview:hover {
      border-color: var(--primary);
      background: #f0f4ff;
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
          <h1>Danh sách sản phẩm</h1>
          <div class="breadcrumb">Trang chủ / Sản phẩm</div>
        </div>

        <div id="alertContainer"></div>

        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group search-box">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tên hoặc mã SKU..." value="<?php echo htmlspecialchars($searchFilter); ?>">
              <button type="submit"><i class="bi bi-search"></i></button>
            </div>
            <div class="filter-group">
              <label>Danh mục</label>
              <select name="category_id" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <label>Thương hiệu</label>
              <select name="brand_id" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả hãng</option>
                <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand['id']; ?>" <?php echo $brandFilter == $brand['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="in_stock" <?php echo $statusFilter === 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                <option value="out_of_stock" <?php echo $statusFilter === 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                <option value="low_stock" <?php echo $statusFilter === 'low_stock' ? 'selected' : ''; ?>>Sắp hết</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Sắp xếp</label>
              <select name="sort" class="form-control" onchange="this.form.submit()">
                <option value="">Mới nhất</option>
                <option value="price_asc" <?php echo $sortFilter === 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                <option value="price_desc" <?php echo $sortFilter === 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                <option value="name_asc" <?php echo $sortFilter === 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
              </select>
            </div>
            <?php if ($canEdit): ?>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Thêm sản phẩm
              </button>
            </div>
            <?php endif; ?>
          </form>
        </div>

        <div style="margin-bottom: 16px; color: var(--text-muted);">
          Hiển thị <?php echo count($products); ?> / <?php echo $totalProducts; ?> sản phẩm
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state">
          <i class="bi bi-box-seam"></i>
          <h3>Không có sản phẩm nào</h3>
          <p>Thử thay đổi bộ lọc hoặc thêm sản phẩm mới</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $product): ?>
          <div class="col-md-6 col-lg-4">
            <div class="product-card">
              <?php if ($product['quantity'] <= $product['min_quantity']): ?>
              <span class="product-badge badge <?php echo $product['quantity'] == 0 ? 'badge-danger' : 'badge-warning'; ?>">
                <?php echo $product['quantity'] == 0 ? 'Hết hàng' : 'Sắp hết'; ?>
              </span>
              <?php endif; ?>
              <div class="product-image">
                <?php if (!empty($product['image'])): ?>
                <img src="<?php echo $base_url . 'assets/images/products/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                <div class="no-image"><i class="bi bi-phone"></i></div>
                <?php endif; ?>
              </div>
              <div class="product-body">
                <div class="product-category">
                  <?php echo htmlspecialchars($product['category_name'] ?? 'Chưa phân loại'); ?>
                  <?php if (!empty($product['brand_name'])): ?>
                  <span class="product-brand">• <?php echo htmlspecialchars($product['brand_name']); ?></span>
                  <?php endif; ?>
                </div>
                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</div>
                <div class="product-stock">
                  <i class="bi bi-box"></i> Tồn: <strong><?php echo $product['quantity']; ?></strong> | SKU: <strong><?php echo htmlspecialchars($product['sku']); ?></strong>
                </div>
                <div class="product-actions">
                  <button class="action-btn view" onclick="viewProduct(<?php echo $product['id']; ?>)"><i class="bi bi-eye"></i></button>
                  <?php if ($canEdit): ?>
                  <button class="action-btn edit" onclick="editProduct(<?php echo $product['id']; ?>)"><i class="bi bi-pencil"></i></button>
                  <button class="action-btn delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')"><i class="bi bi-trash"></i></button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
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

  <!-- Modal Thêm/Sửa - Luôn render để JS hoạt động -->
  <div class="modal-overlay" id="productModal">
    <div class="modal">
      <div class="modal-header">
        <h3 id="modalTitle">Thêm sản phẩm mới</h3>
        <button class="action-btn" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body">
        <form id="productForm">
          <input type="hidden" id="productId" name="id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
              <input type="text" id="productName" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mã SKU <span class="text-danger">*</span></label>
              <input type="text" id="productSku" name="sku" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Danh mục <span class="text-danger">*</span></label>
              <select id="productCategory" name="category_id" class="form-control" required>
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Thương hiệu</label>
              <select id="productBrand" name="brand_id" class="form-control">
                <option value="">-- Chọn thương hiệu --</option>
                <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái</label>
              <select id="productStatus" name="status" class="form-control">
                <option value="active">Đang bán</option>
                <option value="inactive">Ngừng bán</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Giá bán <span class="text-danger">*</span></label>
              <input type="number" id="productPrice" name="price" class="form-control" min="0" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Giá nhập</label>
              <input type="number" id="productCost" name="cost" class="form-control" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Số lượng tồn</label>
              <input type="number" id="productQuantity" name="quantity" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Số lượng tối thiểu</label>
              <input type="number" id="productMinQuantity" name="min_quantity" class="form-control" min="0" value="10">
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea id="productDescription" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Hình ảnh</label>
              <input type="file" id="productImageFile" accept="image/*" class="form-control">
              <input type="hidden" id="productImage" name="image">
              <div class="image-preview" id="imagePreview"><i class="bi bi-image"></i></div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="saveProduct()"><i class="bi bi-check-lg"></i> Lưu sản phẩm</button>
      </div>
    </div>
  </div>

  <!-- Modal Xem Chi tiết -->
  <div class="modal-overlay" id="viewModal">
    <div class="modal">
      <div class="modal-header">
        <h3>Chi tiết sản phẩm</h3>
        <button class="action-btn" onclick="closeViewModal()">&times;</button>
      </div>
      <div class="modal-body" id="viewModalContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Đóng</button>
        <?php if ($canEdit): ?>
        <button type="button" class="btn btn-primary" id="editFromViewBtn"><i class="bi bi-pencil"></i> Sửa</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal Xóa -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width: 400px;">
      <div class="modal-header">
        <h3>Xác nhận xóa</h3>
        <button class="action-btn" onclick="closeDeleteModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <p>Bạn có chắc muốn xóa sản phẩm <strong id="deleteProductName"></strong>?</p>
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
    const API_URL = '../api/products.php';
    const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
    let currentProductId = null;
    let deleteProductId = null;

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
      console.log('openAddModal called'); // Debug
      const modal = document.getElementById('productModal');
      console.log('modal:', modal); // Debug
      if (!modal) { 
        alert('Modal không tìm thấy!'); 
        return; 
      }
      const title = document.getElementById('modalTitle');
      const form = document.getElementById('productForm');
      if (title) title.textContent = 'Thêm sản phẩm mới';
      if (form) form.reset();
      const productId = document.getElementById('productId');
      if (productId) productId.value = '';
      const preview = document.getElementById('imagePreview');
      if (preview) preview.innerHTML = '<i class="bi bi-image"></i>';
      modal.classList.add('show');
      console.log('Modal should be visible now'); // Debug
    }

    function closeModal() {
      document.getElementById('productModal')?.classList.remove('show');
    }

    function closeViewModal() {
      document.getElementById('viewModal')?.classList.remove('show');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal')?.classList.remove('show');
      deleteProductId = null;
    }

    async function viewProduct(id) {
      try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        if (result.success) {
          const p = result.data;
          document.getElementById('viewModalContent').innerHTML = `
            <table style="width: 100%;">
              <tr><td style="padding: 8px 0; color: #64748b;">Tên</td><td style="padding: 8px 0; font-weight: 600;">${p.name}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">SKU</td><td style="padding: 8px 0;">${p.sku}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Danh mục</td><td style="padding: 8px 0;">${p.category_name || '-'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Thương hiệu</td><td style="padding: 8px 0;">${p.brand_name || '-'}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Giá bán</td><td style="padding: 8px 0; font-weight: 700; color: #1d4ed8;">${Number(p.price).toLocaleString('vi-VN')}₫</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Tồn kho</td><td style="padding: 8px 0;">${p.quantity}</td></tr>
              <tr><td style="padding: 8px 0; color: #64748b;">Mô tả</td><td style="padding: 8px 0;">${p.description || '-'}</td></tr>
            </table>`;
          currentProductId = id;
          document.getElementById('viewModal').classList.add('show');
        } else {
          showAlert(result.message, 'danger');
        }
      } catch (error) {
        showAlert('Lỗi kết nối server', 'danger');
      }
    }

    async function editProduct(id) {
      try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        if (result.success) {
          const p = result.data;
          document.getElementById('modalTitle').textContent = 'Sửa sản phẩm';
          document.getElementById('productId').value = p.id;
          document.getElementById('productName').value = p.name;
          document.getElementById('productSku').value = p.sku;
          document.getElementById('productCategory').value = p.category_id;
          document.getElementById('productBrand').value = p.brand_id || '';
          document.getElementById('productStatus').value = p.status;
          document.getElementById('productPrice').value = p.price;
          document.getElementById('productCost').value = p.cost || '';
          document.getElementById('productQuantity').value = p.quantity;
          document.getElementById('productMinQuantity').value = p.min_quantity;
          document.getElementById('productDescription').value = p.description || '';
          document.getElementById('productImage').value = p.image || '';
          document.getElementById('imagePreview').innerHTML = p.image ? `<img src="../assets/images/products/${p.image}">` : '<i class="bi bi-image"></i>';
          document.getElementById('productModal').classList.add('show');
        } else {
          showAlert(result.message, 'danger');
        }
      } catch (error) {
        showAlert('Lỗi kết nối server', 'danger');
      }
    }

    async function saveProduct() {
      const id = document.getElementById('productId').value;
      const data = {
        name: document.getElementById('productName').value.trim(),
        sku: document.getElementById('productSku').value.trim(),
        category_id: parseInt(document.getElementById('productCategory').value) || null,
        brand_id: parseInt(document.getElementById('productBrand').value) || null,
        status: document.getElementById('productStatus').value,
        price: parseFloat(document.getElementById('productPrice').value) || null,
        cost: parseFloat(document.getElementById('productCost').value) || null,
        quantity: parseInt(document.getElementById('productQuantity').value) || 0,
        min_quantity: parseInt(document.getElementById('productMinQuantity').value) || 10,
        description: document.getElementById('productDescription').value.trim(),
        image: document.getElementById('productImage').value.trim() || null
      };

      if (!data.name) { showAlert('Vui lòng nhập tên sản phẩm', 'danger'); return; }
      if (!data.sku) { showAlert('Vui lòng nhập mã SKU', 'danger'); return; }
      if (!data.category_id) { showAlert('Vui lòng chọn danh mục', 'danger'); return; }
      if (!data.price) { showAlert('Vui lòng nhập giá bán', 'danger'); return; }

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

    function deleteProduct(id, name) {
      deleteProductId = id;
      document.getElementById('deleteProductName').textContent = name;
      document.getElementById('deleteModal').classList.add('show');
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const confirmBtn = document.getElementById('confirmDeleteBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
          if (!deleteProductId) return;
          try {
            const response = await fetch(`${API_URL}?id=${deleteProductId}`, { method: 'DELETE' });
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

      const imageInput = document.getElementById('productImageFile');
      if (imageInput) {
        imageInput.addEventListener('change', async function() {
          const file = this.files[0];
          if (!file) return;
          const preview = document.getElementById('imagePreview');
          const reader = new FileReader();
          reader.onload = (e) => { preview.innerHTML = `<img src="${e.target.result}">`; };
          reader.readAsDataURL(file);
          
          const formData = new FormData();
          formData.append('image', file);
          try {
            const response = await fetch('../api/upload.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
              document.getElementById('productImage').value = result.data.filename;
              showAlert('Upload ảnh thành công', 'success');
            } else {
              showAlert(result.message, 'danger');
            }
          } catch (error) {
            showAlert('Lỗi upload ảnh', 'danger');
          }
        });
      }

      const editFromViewBtn = document.getElementById('editFromViewBtn');
      if (editFromViewBtn) {
        editFromViewBtn.addEventListener('click', () => {
          closeViewModal();
          if (currentProductId) editProduct(currentProductId);
        });
      }

      document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
          if (e.target === this) this.classList.remove('show');
        });
      });
    });
  </script>
</body>
</html>
