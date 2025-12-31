<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$page_title = "Quản lý thương hiệu";
$current_page = "brands";
$base_url = "../";

$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
$canEdit = in_array($role_id, [1, 2]);

$db = getDB();

// Lấy danh sách thương hiệu
$stmt = $db->query("
    SELECT b.*, COUNT(p.id) as product_count 
    FROM brands b 
    LEFT JOIN products p ON b.id = p.brand_id 
    GROUP BY b.id 
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();

$totalBrands = count($brands);
$totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE brand_id IS NOT NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .brand-logo {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: bold;
      color: #fff;
    }
    .brand-card-header {
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
          <h1>Quản lý thương hiệu</h1>
          <div class="breadcrumb">Trang chủ / Thương hiệu</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-building"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng thương hiệu</h4>
                <div class="stat-value"><?php echo $totalBrands; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-box-seam"></i>
              </div>
              <div class="stat-info">
                <h4>Sản phẩm có thương hiệu</h4>
                <div class="stat-value"><?php echo $totalProducts; ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Tìm kiếm</label>
              <input type="text" class="form-control" placeholder="Tên thương hiệu..." id="searchInput" onkeyup="filterBrands()">
            </div>
            <?php if ($canEdit): ?>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Thêm thương hiệu
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Brands Grid -->
        <div class="row g-4" id="brandsGrid">
          <?php 
          $colors = ['#4f46e5', '#059669', '#ea580c', '#7c3aed', '#db2777', '#0891b2', '#65a30d', '#dc2626', '#0284c7', '#6366f1'];
          $i = 0;
          foreach ($brands as $brand): 
            $color = $colors[$i % count($colors)];
            $initial = strtoupper(substr($brand['name'], 0, 1));
            $i++;
          ?>
          <div class="col-md-6 col-lg-4 brand-item" data-name="<?php echo strtolower($brand['name']); ?>">
            <div class="card">
              <div class="card-body">
                <div class="brand-card-header">
                  <div class="brand-logo" style="background: <?php echo $color; ?>">
                    <?php echo $initial; ?>
                  </div>
                  <div class="flex-grow-1">
                    <h4 class="fw-bold mb-1 fs-5"><?php echo htmlspecialchars($brand['name']); ?></h4>
                    <small class="text-muted fw-semibold"><?php echo $brand['product_count']; ?> sản phẩm</small>
                  </div>
                </div>
                <p class="text-muted small mb-3">
                  <?php echo htmlspecialchars($brand['description'] ?? 'Chưa có mô tả'); ?>
                </p>
                <div style="display: flex; gap: 10px;">
                  <a href="products.php?brand_id=<?php echo $brand['id']; ?>" class="action-btn view" title="Xem sản phẩm">
                    <i class="bi bi-eye"></i>
                  </a>
                  <?php if ($canEdit): ?>
                  <button class="action-btn edit" onclick="openEditModal(<?php echo $brand['id']; ?>, '<?php echo htmlspecialchars(addslashes($brand['name'])); ?>', '<?php echo htmlspecialchars(addslashes($brand['description'] ?? '')); ?>')" title="Sửa">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="action-btn delete" onclick="deleteBrand(<?php echo $brand['id']; ?>, '<?php echo htmlspecialchars(addslashes($brand['name'])); ?>')" title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <!-- Modal Thêm/Sửa thương hiệu -->
  <div class="custom-modal-overlay" id="brandModal">
    <div class="custom-modal-box">
      <div class="custom-modal-header">
        <h5 id="modalTitle">Thêm thương hiệu</h5>
        <button type="button" class="custom-modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form id="brandForm">
        <div class="custom-modal-body">
          <input type="hidden" id="brandId" name="id">
          <div class="mb-3">
            <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="brandName" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea class="form-control" id="brandDescription" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
          <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
      </form>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
  <script>
    function filterBrands() {
      const search = document.getElementById('searchInput').value.toLowerCase();
      document.querySelectorAll('.brand-item').forEach(item => {
        const name = item.dataset.name;
        item.style.display = name.includes(search) ? '' : 'none';
      });
    }
    
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'Thêm thương hiệu';
      document.getElementById('brandId').value = '';
      document.getElementById('brandName').value = '';
      document.getElementById('brandDescription').value = '';
      document.getElementById('brandModal').classList.add('show');
    }
    
    function openEditModal(id, name, description) {
      document.getElementById('modalTitle').textContent = 'Sửa thương hiệu';
      document.getElementById('brandId').value = id;
      document.getElementById('brandName').value = name;
      document.getElementById('brandDescription').value = description;
      document.getElementById('brandModal').classList.add('show');
    }
    
    function closeModal() {
      document.getElementById('brandModal').classList.remove('show');
    }
    
    // Click outside to close
    document.getElementById('brandModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });
    
    document.getElementById('brandForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const id = document.getElementById('brandId').value;
      const name = document.getElementById('brandName').value;
      const description = document.getElementById('brandDescription').value;
      
      const method = id ? 'PUT' : 'POST';
      const data = { name, description };
      if (id) data.id = id;
      
      try {
        const response = await fetch('../api/brands.php', {
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
    
    async function deleteBrand(id, name) {
      if (!confirm(`Bạn có chắc muốn xóa thương hiệu "${name}"?`)) return;
      
      try {
        const response = await fetch('../api/brands.php?id=' + id, { method: 'DELETE' });
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
