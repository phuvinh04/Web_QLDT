<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';

$page_title = "Quản lý nhà cung cấp";
$current_page = "suppliers";
$base_url = "../";

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $tax_id = trim($_POST['tax_id'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name)) {
            $error = "Vui lòng nhập tên nhà cung cấp!";
        } else {
            $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, email, address, city, contact_person, tax_id, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $name, $phone, $email, $address, $city, $contact_person, $tax_id, $notes, $status);
            
            if ($stmt->execute()) {
                $message = "Thêm nhà cung cấp thành công!";
            } else {
                $error = "Lỗi khi thêm nhà cung cấp: " . $conn->error;
            }
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $tax_id = trim($_POST['tax_id'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name)) {
            $error = "Vui lòng nhập tên nhà cung cấp!";
        } else {
            $stmt = $conn->prepare("UPDATE suppliers SET name=?, phone=?, email=?, address=?, city=?, contact_person=?, tax_id=?, notes=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssssi", $name, $phone, $email, $address, $city, $contact_person, $tax_id, $notes, $status, $id);
            
            if ($stmt->execute()) {
                $message = "Cập nhật nhà cung cấp thành công!";
            } else {
                $error = "Lỗi khi cập nhật nhà cung cấp: " . $conn->error;
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        // Check if supplier has stock movements
        $check = $conn->query("SELECT COUNT(*) as count FROM stock_movements WHERE supplier_id = $id");
        $row = $check->fetch_assoc();
        
        if ($row['count'] > 0) {
            $error = "Không thể xóa nhà cung cấp này vì đã có giao dịch nhập/xuất kho!";
        } else {
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "Xóa nhà cung cấp thành công!";
            } else {
                $error = "Lỗi khi xóa nhà cung cấp: " . $conn->error;
            }
        }
    }
}

// Get filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_city = $_GET['city'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];
$types = '';

if (!empty($filter_status)) {
    $where[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($filter_city)) {
    $where[] = "city = ?";
    $params[] = $filter_city;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR contact_person LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$sql = "SELECT * FROM suppliers";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$suppliers = $stmt->get_result();

// Get all cities for filter
$cities = $conn->query("SELECT DISTINCT city FROM suppliers WHERE city IS NOT NULL AND city != '' ORDER BY city");

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
FROM suppliers")->fetch_assoc();
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
          <h1>Quản lý nhà cung cấp</h1>
          <div class="breadcrumb">Trang chủ / Nhà cung cấp</div>
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
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-building"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng nhà cung cấp</h4>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
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
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Ngừng hoạt động</h4>
                <div class="stat-value"><?php echo $stats['inactive'] ?? 0; ?></div>
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
                <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Thành phố</label>
              <select name="city" class="form-control">
                <option value="">Tất cả</option>
                <?php while ($city = $cities->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($city['city']); ?>" <?php echo $filter_city === $city['city'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($city['city']); ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="filter-group">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tên, SĐT, Email..." value="<?php echo htmlspecialchars($search); ?>">
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
                <i class="bi bi-plus-circle"></i> Thêm NCC
              </button>
            </div>
          </form>
        </div>

        <!-- Suppliers Table -->
        <div class="data-table">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên nhà cung cấp</th>
                <th>Liên hệ</th>
                <th>Địa chỉ</th>
                <th>Mã số thuế</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($suppliers->num_rows > 0): ?>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $supplier['id']; ?></td>
                  <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($supplier['name']); ?></div>
                    <?php if ($supplier['contact_person']): ?>
                    <small class="text-muted"><?php echo htmlspecialchars($supplier['contact_person']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($supplier['phone']): ?>
                    <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($supplier['phone']); ?></div>
                    <?php endif; ?>
                    <?php if ($supplier['email']): ?>
                    <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($supplier['email']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($supplier['address']): ?>
                    <div><?php echo htmlspecialchars($supplier['address']); ?></div>
                    <?php endif; ?>
                    <?php if ($supplier['city']): ?>
                    <small class="text-muted"><?php echo htmlspecialchars($supplier['city']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($supplier['tax_id'] ?? '-'); ?></td>
                  <td>
                    <span class="badge <?php echo $supplier['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                      <?php echo $supplier['status'] === 'active' ? 'Hoạt động' : 'Ngừng HĐ'; ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="btn btn-icon edit" onclick="editSupplier(<?php echo htmlspecialchars(json_encode($supplier)); ?>)" title="Sửa">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-icon delete" onclick="deleteSupplier(<?php echo $supplier['id']; ?>, '<?php echo htmlspecialchars($supplier['name']); ?>')" title="Xóa">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Không có nhà cung cấp nào</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Supplier Modal -->
  <div id="addSupplierModal" class="custom-modal-overlay">
    <div class="custom-modal-box modal-lg">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="custom-modal-header">
          <h5>Thêm nhà cung cấp mới</h5>
          <button type="button" class="custom-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Người liên hệ</label>
              <input type="text" name="contact_person" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Số điện thoại</label>
              <input type="tel" name="phone" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-8">
              <label class="form-label">Địa chỉ</label>
              <input type="text" name="address" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Thành phố</label>
              <input type="text" name="city" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mã số thuế</label>
              <input type="text" name="tax_id" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái</label>
              <select name="status" class="form-control">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
          <button type="submit" class="btn btn-primary">Thêm nhà cung cấp</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Supplier Modal -->
  <div id="editSupplierModal" class="custom-modal-overlay">
    <div class="custom-modal-box modal-lg">
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="custom-modal-header">
          <h5>Sửa nhà cung cấp</h5>
          <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Người liên hệ</label>
              <input type="text" name="contact_person" id="edit_contact_person" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Số điện thoại</label>
              <input type="tel" name="phone" id="edit_phone" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="edit_email" class="form-control">
            </div>
            <div class="col-md-8">
              <label class="form-label">Địa chỉ</label>
              <input type="text" name="address" id="edit_address" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Thành phố</label>
              <input type="text" name="city" id="edit_city" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mã số thuế</label>
              <input type="text" name="tax_id" id="edit_tax_id" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Trạng thái</label>
              <select name="status" id="edit_status" class="form-control">
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Ghi chú</label>
              <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
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
      document.getElementById('addSupplierModal').classList.add('show');
    }
    
    function closeAddModal() {
      document.getElementById('addSupplierModal').classList.remove('show');
    }
    
    function closeEditModal() {
      document.getElementById('editSupplierModal').classList.remove('show');
    }
    
    function editSupplier(supplier) {
      document.getElementById('edit_id').value = supplier.id;
      document.getElementById('edit_name').value = supplier.name || '';
      document.getElementById('edit_contact_person').value = supplier.contact_person || '';
      document.getElementById('edit_phone').value = supplier.phone || '';
      document.getElementById('edit_email').value = supplier.email || '';
      document.getElementById('edit_address').value = supplier.address || '';
      document.getElementById('edit_city').value = supplier.city || '';
      document.getElementById('edit_tax_id').value = supplier.tax_id || '';
      document.getElementById('edit_status').value = supplier.status || 'active';
      document.getElementById('edit_notes').value = supplier.notes || '';
      
      document.getElementById('editSupplierModal').classList.add('show');
    }
    
    function deleteSupplier(id, name) {
      if (confirm(`Bạn có chắc muốn xóa nhà cung cấp "${name}"?`)) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
      }
    }
    
    // Close modal when clicking outside
    document.getElementById('addSupplierModal').addEventListener('click', function(e) {
      if (e.target === this) closeAddModal();
    });
    document.getElementById('editSupplierModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditModal();
    });
  </script>
</body>
</html>
