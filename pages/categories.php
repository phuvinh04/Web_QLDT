<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Quản lý danh mục";
$current_page = "categories";
$base_url = "../";

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
$canEdit = in_array($role_id, [1, 2]);

$db = getDB();

// Lấy danh sách danh mục với số lượng sản phẩm
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$orderBy = "c.name ASC";
switch ($sortBy) {
    case 'name_desc': $orderBy = "c.name DESC"; break;
    case 'products_desc': $orderBy = "product_count DESC"; break;
    case 'products_asc': $orderBy = "product_count ASC"; break;
}

$stmt = $db->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY $orderBy
");
$categories = $stmt->fetchAll();

// Thống kê
$totalCategories = count($categories);
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Danh mục phổ biến nhất
$popularCategory = $db->query("
    SELECT c.name, COUNT(p.id) as cnt 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY cnt DESC 
    LIMIT 1
")->fetch();
$popularName = $popularCategory ? $popularCategory['name'] : 'N/A';

// Màu sắc và icon cho danh mục
$categoryStyles = [
    'Điện thoại cao cấp' => ['icon' => 'bi-phone-fill', 'color' => 'blue'],
    'Điện thoại tầm trung' => ['icon' => 'bi-phone', 'color' => 'green'],
    'Điện thoại giá rẻ' => ['icon' => 'bi-phone-vibrate', 'color' => 'orange'],
    'Máy tính bảng' => ['icon' => 'bi-tablet', 'color' => 'purple'],
    'Phụ kiện' => ['icon' => 'bi-headphones', 'color' => 'pink'],
];

function getCategoryStyle($name) {
    global $categoryStyles;
    if (isset($categoryStyles[$name])) {
        return $categoryStyles[$name];
    }
    $colors = ['blue', 'green', 'orange', 'purple', 'pink', 'cyan'];
    $icons = ['bi-grid', 'bi-box', 'bi-collection', 'bi-stack', 'bi-archive'];
    return [
        'icon' => $icons[array_rand($icons)],
        'color' => $colors[array_rand($colors)]
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .cat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }
    .cat-icon.blue { background: #e0e7ff; color: #4f46e5; }
    .cat-icon.green { background: #d1fae5; color: #059669; }
    .cat-icon.orange { background: #ffedd5; color: #ea580c; }
    .cat-icon.purple { background: #ede9fe; color: #7c3aed; }
    .cat-icon.pink { background: #fce7f3; color: #db2777; }
    .cat-icon.cyan { background: #cffafe; color: #0891b2; }
    .cat-card-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 12px;
    }
    .action-btn {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
      background: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .action-btn:hover { background: #f3f4f6; }
    .action-btn.delete:hover { background: #fee2e2; color: #dc2626; }
    .action-btn.edit:hover { background: #dbeafe; color: #2563eb; }
    .action-btn.view:hover { background: #d1fae5; color: #059669; }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-content">
      <?php include '../components/header.php'; ?>

      <div class="content">
        <div class="page-title">
          <h1>Quản lý danh mục sản phẩm</h1>
          <div class="breadcrumb">Trang chủ / Danh mục</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-grid"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng danh mục</h4>
                <div class="stat-value"><?php echo $totalCategories; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-box-seam"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng sản phẩm</h4>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-star"></i>
              </div>
              <div class="stat-info">
                <h4>Phổ biến nhất</h4>
                <div class="stat-value"><?php echo htmlspecialchars($popularName); ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Sắp xếp</label>
              <select class="form-control" id="sortSelect" onchange="changeSort()">
                <option value="name_asc" <?php echo $sortBy == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                <option value="name_desc" <?php echo $sortBy == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                <option value="products_desc" <?php echo $sortBy == 'products_desc' ? 'selected' : ''; ?>>Nhiều sản phẩm nhất</option>
                <option value="products_asc" <?php echo $sortBy == 'products_asc' ? 'selected' : ''; ?>>Ít sản phẩm nhất</option>
              </select>
            </div>
            <?php if ($canEdit): ?>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Thêm danh mục
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Categories Grid -->
        <div class="row g-4">
          <?php foreach ($categories as $category): 
            $style = getCategoryStyle($category['name']);
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="card">
              <div class="card-body">
                <div class="cat-card-header">
                  <div class="cat-icon <?php echo $style['color']; ?>">
                    <i class="bi <?php echo $style['icon']; ?>"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h4 class="fw-bold mb-1 fs-5"><?php echo htmlspecialchars($category['name']); ?></h4>
                    <small class="text-muted fw-semibold"><?php echo $category['product_count']; ?> sản phẩm</small>
                  </div>
                </div>
                <p class="text-muted small mb-3">
                  <?php echo htmlspecialchars($category['description'] ?? 'Chưa có mô tả'); ?>
                </p>
                <div style="display: flex; gap: 10px;">
                  <a href="products.php?category_id=<?php echo $category['id']; ?>" class="action-btn view" title="Xem sản phẩm">
                    <i class="bi bi-eye"></i>
                  </a>
                  <?php if ($canEdit): ?>
                  <button class="action-btn edit" onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>', '<?php echo htmlspecialchars(addslashes($category['description'] ?? '')); ?>')" title="Sửa">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="action-btn delete" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')" title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          
          <?php if (empty($categories)): ?>
          <div class="col-12">
            <div class="alert alert-info text-center">
              <i class="bi bi-info-circle"></i> Chưa có danh mục nào. Hãy thêm danh mục mới!
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <!-- Modal Thêm/Sửa danh mục -->
  <div class="custom-modal-overlay" id="categoryModal">
    <div class="custom-modal-box">
      <div class="custom-modal-header">
        <h5 id="modalTitle">Thêm danh mục</h5>
        <button type="button" class="custom-modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form id="categoryForm">
        <div class="custom-modal-body">
          <input type="hidden" id="categoryId" name="id">
          <div class="mb-3">
            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="categoryName" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
          <button type="submit" class="btn btn-primary" id="saveBtn">Lưu</button>
        </div>
      </form>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
  <script>
    function changeSort() {
      const sort = document.getElementById('sortSelect').value;
      window.location.href = 'categories.php?sort=' + sort;
    }
    
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'Thêm danh mục';
      document.getElementById('categoryId').value = '';
      document.getElementById('categoryName').value = '';
      document.getElementById('categoryDescription').value = '';
      document.getElementById('categoryModal').classList.add('show');
    }
    
    function openEditModal(id, name, description) {
      document.getElementById('modalTitle').textContent = 'Sửa danh mục';
      document.getElementById('categoryId').value = id;
      document.getElementById('categoryName').value = name;
      document.getElementById('categoryDescription').value = description;
      document.getElementById('categoryModal').classList.add('show');
    }
    
    function closeModal() {
      document.getElementById('categoryModal').classList.remove('show');
    }
    
    // Click outside to close
    document.getElementById('categoryModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });
    
    document.getElementById('categoryForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const id = document.getElementById('categoryId').value;
      const name = document.getElementById('categoryName').value;
      const description = document.getElementById('categoryDescription').value;
      
      const method = id ? 'PUT' : 'POST';
      const data = { name, description };
      if (id) data.id = id;
      
      try {
        const response = await fetch('../api/categories.php', {
          method: method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
          closeModal();
          location.reload();
        } else {
          alert(result.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        alert('Có lỗi xảy ra: ' + error.message);
      }
    });
    
    async function deleteCategory(id, name) {
      if (!confirm(`Bạn có chắc muốn xóa danh mục "${name}"?\nLưu ý: Không thể xóa danh mục đang có sản phẩm.`)) {
        return;
      }
      
      try {
        const response = await fetch('../api/categories.php?id=' + id, {
          method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
          location.reload();
        } else {
          alert(result.message || 'Có lỗi xảy ra');
        }
      } catch (error) {
        alert('Có lỗi xảy ra: ' + error.message);
      }
    }
  </script>
</body>
</html>
