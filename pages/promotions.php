<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';

$page_title = "Quản lý khuyến mãi";
$current_page = "promotions";
$base_url = "../";

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percent';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $min_amount = floatval($_POST['min_amount'] ?? 0);
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $active = isset($_POST['active']) ? 1 : 0;
        $priority = intval($_POST['priority'] ?? 0);
        
        if (empty($name)) {
            $error = "Vui lòng nhập tên khuyến mãi!";
        } elseif ($discount_value <= 0) {
            $error = "Giá trị giảm giá phải lớn hơn 0!";
        } else {
            $stmt = $conn->prepare("INSERT INTO promotions (name, description, discount_type, discount_value, product_id, min_amount, start_date, end_date, active, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiissii", $name, $description, $discount_type, $discount_value, $product_id, $min_amount, $start_date, $end_date, $active, $priority);
            
            if ($stmt->execute()) {
                $message = "Thêm khuyến mãi thành công!";
            } else {
                $error = "Lỗi khi thêm khuyến mãi: " . $conn->error;
            }
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percent';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $min_amount = floatval($_POST['min_amount'] ?? 0);
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $active = isset($_POST['active']) ? 1 : 0;
        $priority = intval($_POST['priority'] ?? 0);
        
        if (empty($name)) {
            $error = "Vui lòng nhập tên khuyến mãi!";
        } elseif ($discount_value <= 0) {
            $error = "Giá trị giảm giá phải lớn hơn 0!";
        } else {
            $stmt = $conn->prepare("UPDATE promotions SET name=?, description=?, discount_type=?, discount_value=?, product_id=?, min_amount=?, start_date=?, end_date=?, active=?, priority=? WHERE id=?");
            $stmt->bind_param("sssdiissiii", $name, $description, $discount_type, $discount_value, $product_id, $min_amount, $start_date, $end_date, $active, $priority, $id);
            
            if ($stmt->execute()) {
                $message = "Cập nhật khuyến mãi thành công!";
            } else {
                $error = "Lỗi khi cập nhật khuyến mãi: " . $conn->error;
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare("DELETE FROM promotions WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Xóa khuyến mãi thành công!";
        } else {
            $error = "Lỗi khi xóa khuyến mãi: " . $conn->error;
        }
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id']);
        $active = intval($_POST['active']);
        
        $stmt = $conn->prepare("UPDATE promotions SET active=? WHERE id=?");
        $stmt->bind_param("ii", $active, $id);
        
        if ($stmt->execute()) {
            $message = $active ? "Đã kích hoạt khuyến mãi!" : "Đã tắt khuyến mãi!";
        }
    }
}

// Get filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$today = date('Y-m-d');
$where = [];
$params = [];
$types = '';

if ($filter_status === 'active') {
    $where[] = "active = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?)";
    $params[] = $today;
    $params[] = $today;
    $types .= 'ss';
} elseif ($filter_status === 'upcoming') {
    $where[] = "start_date > ?";
    $params[] = $today;
    $types .= 's';
} elseif ($filter_status === 'expired') {
    $where[] = "end_date < ?";
    $params[] = $today;
    $types .= 's';
} elseif ($filter_status === 'inactive') {
    $where[] = "active = 0";
}

if (!empty($filter_type)) {
    $where[] = "discount_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm]);
    $types .= 'ss';
}

$sql = "SELECT p.*, pr.name as product_name 
        FROM promotions p 
        LEFT JOIN products pr ON p.product_id = pr.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY p.priority DESC, p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$promotions = $stmt->get_result();

// Get all products for select
$products = $conn->query("SELECT id, name FROM products ORDER BY name");

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN active = 1 AND (start_date IS NULL OR start_date <= '$today') AND (end_date IS NULL OR end_date >= '$today') THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN start_date > '$today' THEN 1 ELSE 0 END) as upcoming,
    SUM(CASE WHEN end_date < '$today' THEN 1 ELSE 0 END) as expired
FROM promotions")->fetch_assoc();
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
          <h1>Quản lý khuyến mãi</h1>
          <div class="breadcrumb">Trang chủ / Khuyến mãi</div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle-fill"></i> <?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-tag"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng khuyến mãi</h4>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Đang hoạt động</h4>
                <div class="stat-value"><?php echo $stats['active'] ?? 0; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-clock"></i>
              </div>
              <div class="stat-info">
                <h4>Sắp diễn ra</h4>
                <div class="stat-value"><?php echo $stats['upcoming'] ?? 0; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Đã hết hạn</h4>
                <div class="stat-value"><?php echo $stats['expired'] ?? 0; ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control">
                <option value="">Tất cả</option>
                <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                <option value="upcoming" <?php echo $filter_status === 'upcoming' ? 'selected' : ''; ?>>Sắp diễn ra</option>
                <option value="expired" <?php echo $filter_status === 'expired' ? 'selected' : ''; ?>>Đã hết hạn</option>
                <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Đã tắt</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Loại giảm giá</label>
              <select name="type" class="form-control">
                <option value="">Tất cả</option>
                <option value="percent" <?php echo $filter_type === 'percent' ? 'selected' : ''; ?>>Giảm theo %</option>
                <option value="fixed" <?php echo $filter_type === 'fixed' ? 'selected' : ''; ?>>Giảm cố định</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tên, mô tả..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-secondary">
                <i class="bi bi-search"></i> Tìm
              </button>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Tạo khuyến mãi
              </button>
            </div>
          </form>
        </div>

        <!-- Promotions Grid -->
        <div class="row g-4">
          <?php if ($promotions->num_rows > 0): ?>
            <?php while ($promo = $promotions->fetch_assoc()): 
              // Determine status
              $isActive = $promo['active'];
              $isUpcoming = $promo['start_date'] && $promo['start_date'] > $today;
              $isExpired = $promo['end_date'] && $promo['end_date'] < $today;
              $isRunning = $isActive && !$isUpcoming && !$isExpired;
              
              if ($isExpired) {
                $statusClass = 'bg-secondary';
                $statusText = 'Đã hết hạn';
              } elseif (!$isActive) {
                $statusClass = 'bg-danger';
                $statusText = 'Đã tắt';
              } elseif ($isUpcoming) {
                $statusClass = 'bg-warning text-dark';
                $statusText = 'Sắp diễn ra';
              } else {
                $statusClass = 'bg-success';
                $statusText = 'Đang hoạt động';
              }
            ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    <div class="dropdown">
                      <button class="btn btn-link p-0" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" href="#" onclick="editPromotion(<?php echo htmlspecialchars(json_encode($promo)); ?>)">
                            <i class="bi bi-pencil"></i> Sửa
                          </a>
                        </li>
                        <li>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
                            <input type="hidden" name="active" value="<?php echo $isActive ? 0 : 1; ?>">
                            <button type="submit" class="dropdown-item">
                              <i class="bi bi-power"></i> <?php echo $isActive ? 'Tắt' : 'Bật'; ?>
                            </button>
                          </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <a class="dropdown-item text-danger" href="#" onclick="deletePromotion(<?php echo $promo['id']; ?>, '<?php echo htmlspecialchars($promo['name']); ?>')">
                            <i class="bi bi-trash"></i> Xóa
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  
                  <h5 class="card-title"><?php echo htmlspecialchars($promo['name']); ?></h5>
                  
                  <div class="h3 text-primary mb-3">
                    <?php if ($promo['discount_type'] === 'percent'): ?>
                      -<?php echo number_format($promo['discount_value']); ?>%
                    <?php else: ?>
                      -<?php echo number_format($promo['discount_value']); ?>đ
                    <?php endif; ?>
                  </div>
                  
                  <?php if ($promo['description']): ?>
                  <p class="card-text text-muted small"><?php echo htmlspecialchars($promo['description']); ?></p>
                  <?php endif; ?>
                  
                  <ul class="list-unstyled small text-muted mb-0">
                    <?php if ($promo['product_name']): ?>
                    <li><i class="bi bi-phone"></i> Áp dụng: <?php echo htmlspecialchars($promo['product_name']); ?></li>
                    <?php else: ?>
                    <li><i class="bi bi-cart"></i> Áp dụng: Tất cả sản phẩm</li>
                    <?php endif; ?>
                    
                    <?php if ($promo['min_amount'] > 0): ?>
                    <li><i class="bi bi-cash"></i> Đơn tối thiểu: <?php echo number_format($promo['min_amount']); ?>đ</li>
                    <?php endif; ?>
                    
                    <li>
                      <i class="bi bi-calendar"></i> 
                      <?php 
                      if ($promo['start_date'] && $promo['end_date']) {
                        echo date('d/m/Y', strtotime($promo['start_date'])) . ' - ' . date('d/m/Y', strtotime($promo['end_date']));
                      } elseif ($promo['start_date']) {
                        echo 'Từ ' . date('d/m/Y', strtotime($promo['start_date']));
                      } elseif ($promo['end_date']) {
                        echo 'Đến ' . date('d/m/Y', strtotime($promo['end_date']));
                      } else {
                        echo 'Không giới hạn';
                      }
                      ?>
                    </li>
                    
                    <li><i class="bi bi-sort-numeric-up"></i> Ưu tiên: <?php echo $promo['priority']; ?></li>
                  </ul>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <i class="bi bi-tag fs-1 text-muted"></i>
              <p class="text-muted mt-2">Không có khuyến mãi nào</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Promotion Modal -->
  <div id="addPromotionModal" class="custom-modal-overlay">
    <div class="custom-modal-box modal-lg">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="custom-modal-header">
          <h5>Tạo khuyến mãi mới</h5>
          <button type="button" class="custom-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Loại giảm giá</label>
              <select name="discount_type" class="form-control">
                <option value="percent">Giảm theo %</option>
                <option value="fixed">Giảm cố định (VNĐ)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
              <input type="number" name="discount_value" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Áp dụng cho sản phẩm</label>
              <select name="product_id" class="form-control">
                <option value="">Tất cả sản phẩm</option>
                <?php 
                $products->data_seek(0);
                while ($product = $products->fetch_assoc()): ?>
                <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Đơn hàng tối thiểu</label>
              <input type="number" name="min_amount" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ngày bắt đầu</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ngày kết thúc</label>
              <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Độ ưu tiên</label>
              <input type="number" name="priority" class="form-control" value="0" min="0">
              <small class="text-muted">Số cao hơn = ưu tiên cao hơn</small>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input type="checkbox" name="active" class="form-check-input" id="add_active" checked>
                <label class="form-check-label" for="add_active">Kích hoạt ngay</label>
              </div>
            </div>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
          <button type="submit" class="btn btn-primary">Tạo khuyến mãi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Promotion Modal -->
  <div id="editPromotionModal" class="custom-modal-overlay">
    <div class="custom-modal-box modal-lg">
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="custom-modal-header">
          <h5>Sửa khuyến mãi</h5>
          <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Mô tả</label>
              <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Loại giảm giá</label>
              <select name="discount_type" id="edit_discount_type" class="form-control">
                <option value="percent">Giảm theo %</option>
                <option value="fixed">Giảm cố định (VNĐ)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
              <input type="number" name="discount_value" id="edit_discount_value" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Áp dụng cho sản phẩm</label>
              <select name="product_id" id="edit_product_id" class="form-control">
                <option value="">Tất cả sản phẩm</option>
                <?php 
                $products->data_seek(0);
                while ($product = $products->fetch_assoc()): ?>
                <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Đơn hàng tối thiểu</label>
              <input type="number" name="min_amount" id="edit_min_amount" class="form-control" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ngày bắt đầu</label>
              <input type="date" name="start_date" id="edit_start_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ngày kết thúc</label>
              <input type="date" name="end_date" id="edit_end_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Độ ưu tiên</label>
              <input type="number" name="priority" id="edit_priority" class="form-control" min="0">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input type="checkbox" name="active" class="form-check-input" id="edit_active">
                <label class="form-check-label" for="edit_active">Kích hoạt</label>
              </div>
            </div>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Form -->
  <form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
  </form>

  <?php include '../components/scripts.php'; ?>
  
  <script>
    function openAddModal() {
      document.getElementById('addPromotionModal').classList.add('show');
    }
    
    function closeAddModal() {
      document.getElementById('addPromotionModal').classList.remove('show');
    }
    
    function closeEditModal() {
      document.getElementById('editPromotionModal').classList.remove('show');
    }
    
    function editPromotion(promo) {
      document.getElementById('edit_id').value = promo.id;
      document.getElementById('edit_name').value = promo.name || '';
      document.getElementById('edit_description').value = promo.description || '';
      document.getElementById('edit_discount_type').value = promo.discount_type || 'percent';
      document.getElementById('edit_discount_value').value = promo.discount_value || 0;
      document.getElementById('edit_product_id').value = promo.product_id || '';
      document.getElementById('edit_min_amount').value = promo.min_amount || 0;
      document.getElementById('edit_start_date').value = promo.start_date || '';
      document.getElementById('edit_end_date').value = promo.end_date || '';
      document.getElementById('edit_priority').value = promo.priority || 0;
      document.getElementById('edit_active').checked = promo.active == 1;
      
      document.getElementById('editPromotionModal').classList.add('show');
    }
    
    function deletePromotion(id, name) {
      if (confirm(`Bạn có chắc muốn xóa khuyến mãi "${name}"?`)) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
      }
    }
    
    // Close modal when clicking outside
    document.getElementById('addPromotionModal').addEventListener('click', function(e) {
      if (e.target === this) closeAddModal();
    });
    document.getElementById('editPromotionModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditModal();
    });
  </script>
</body>
</html>
