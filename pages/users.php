<?php 
session_start();
require_once '../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$check_admin = $conn->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$check_admin->bind_param("i", $user_id);
$check_admin->execute();
$role_result = $check_admin->get_result()->fetch_assoc();
$is_admin = ($role_result && $role_result['name'] === 'admin');

if (!$is_admin) {
    header("Location: ../index.php");
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create':
            $role_id = intval($_POST['role_id']);
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $status = $_POST['status'];
            
            // Check duplicate username/email
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username hoặc Email đã tồn tại!']);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO users (role_id, username, password, full_name, email, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $role_id, $username, $password, $full_name, $email, $phone, $status);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
            }
            exit;
            
        case 'update':
            $id = intval($_POST['id']);
            $role_id = intval($_POST['role_id']);
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $status = $_POST['status'];
            
            // Check duplicate (exclude current user)
            $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check->bind_param("ssi", $username, $email, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username hoặc Email đã tồn tại!']);
                exit;
            }
            
            // Update with or without password
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET role_id=?, username=?, password=?, full_name=?, email=?, phone=?, status=? WHERE id=?");
                $stmt->bind_param("issssssi", $role_id, $username, $password, $full_name, $email, $phone, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET role_id=?, username=?, full_name=?, email=?, phone=?, status=? WHERE id=?");
                $stmt->bind_param("isssssi", $role_id, $username, $full_name, $email, $phone, $status, $id);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
            }
            exit;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            // Prevent self-delete
            if ($id == $user_id) {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình!']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
            }
            exit;
            
        case 'get':
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("SELECT id, role_id, username, full_name, email, phone, status FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            echo json_encode($user);
            exit;
            
        case 'check_duplicate':
            $field = $_POST['field']; // 'username' or 'email'
            $value = trim($_POST['value']);
            $exclude_id = isset($_POST['exclude_id']) ? intval($_POST['exclude_id']) : 0;
            
            if ($field === 'username') {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            }
            $stmt->bind_param("si", $value, $exclude_id);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            echo json_encode(['exists' => $exists]);
            exit;
    }
}

// Get filter values
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($filter_role) {
    $where .= " AND r.name = ?";
    $params[] = $filter_role;
    $types .= "s";
}
if ($filter_status) {
    $where .= " AND u.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if ($search) {
    $where .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users u JOIN roles r ON u.role_id = r.id $where";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id $where ORDER BY u.id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result();

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
FROM users")->fetch_assoc();

$admin_count = $conn->query("SELECT COUNT(*) as cnt FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'admin'")->fetch_assoc()['cnt'];

// Get all roles for dropdown
$roles = $conn->query("SELECT * FROM roles ORDER BY id");

$page_title = "Quản lý người dùng";
$current_page = "users";
$base_url = "../";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/head.php'; ?>
  <style>
    .user-modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .user-modal-overlay.show { display: flex !important; }
    .user-modal-box { background: #fff; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; margin: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    .user-modal-header { padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .user-modal-header h5 { margin: 0; font-size: 18px; font-weight: 600; }
    .user-modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: #666; line-height: 1; }
    .user-modal-close:hover { color: #333; }
    .user-modal-body { padding: 20px; }
    .user-modal-footer { padding: 16px 20px; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
    .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #4f46e5; }
    .btn-cancel { background: #f3f4f6; color: #374151; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .btn-cancel:hover { background: #e5e7eb; }
    .btn-save { background: #4f46e5; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .btn-save:hover { background: #4338ca; }
    .badge-admin { background: #dc2626; color: #fff; }
    .badge-manager { background: #f59e0b; color: #fff; }
    .badge-sales { background: #3b82f6; color: #fff; }
    .badge-warehouse { background: #8b5cf6; color: #fff; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error { background: #fee2e2; color: #991b1b; }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-content">
      <?php include '../components/header.php'; ?>

      <div class="content">
        <div class="page-title">
          <h1>Quản lý người dùng</h1>
          <div class="breadcrumb">Trang chủ / Người dùng</div>
        </div>

        <div id="alertBox"></div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue"><i class="bi bi-people"></i></div>
              <div class="stat-info">
                <h4>Tổng người dùng</h4>
                <div class="stat-value"><?= $stats['total'] ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green"><i class="bi bi-person-check"></i></div>
              <div class="stat-info">
                <h4>Đang hoạt động</h4>
                <div class="stat-value"><?= $stats['active'] ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange"><i class="bi bi-shield-check"></i></div>
              <div class="stat-info">
                <h4>Quản trị viên</h4>
                <div class="stat-value"><?= $admin_count ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red"><i class="bi bi-person-x"></i></div>
              <div class="stat-info">
                <h4>Không hoạt động</h4>
                <div class="stat-value"><?= $stats['inactive'] ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group">
              <label>Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tên, email, username..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
              <label>Vai trò</label>
              <select name="role" class="form-control">
                <option value="">Tất cả</option>
                <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="manager" <?= $filter_role == 'manager' ? 'selected' : '' ?>>Manager</option>
                <option value="sales" <?= $filter_role == 'sales' ? 'selected' : '' ?>>Sales</option>
                <option value="warehouse" <?= $filter_role == 'warehouse' ? 'selected' : '' ?>>Warehouse</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select name="status" class="form-control">
                <option value="">Tất cả</option>
                <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $filter_status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Lọc</button>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" onclick="openModal('add')">
                <i class="bi bi-plus-circle"></i> Thêm người dùng
              </button>
            </div>
          </form>
        </div>

        <!-- Users Table -->
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($users->num_rows == 0): ?>
                  <tr><td colspan="9" style="text-align:center;padding:40px;">Không có dữ liệu</td></tr>
                  <?php else: ?>
                  <?php while ($row = $users->fetch_assoc()): ?>
                  <tr>
                    <td><strong>#<?= $row['id'] ?></strong></td>
                    <td><strong><?= htmlspecialchars($row['username'] ?? 'Google User') ?></strong></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                    <td><span class="badge badge-<?= $row['role_name'] ?>"><?= ucfirst($row['role_name']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] == 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    <td class="td-actions">
                      <button class="action-btn view" onclick="viewUser(<?= $row['id'] ?>)" title="Xem"><i class="bi bi-eye"></i></button>
                      <button class="action-btn edit" onclick="editUser(<?= $row['id'] ?>)" title="Sửa"><i class="bi bi-pencil"></i></button>
                      <?php if ($row['id'] != $user_id): ?>
                      <button class="action-btn delete" onclick="deleteUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['full_name']) ?>')" title="Xóa"><i class="bi bi-trash"></i></button>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>"><button><i class="bi bi-chevron-left"></i></button></a>
          <?php endif; ?>
          
          <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
          <a href="?page=<?= $i ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>">
            <button class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></button>
          </a>
          <?php endfor; ?>
          
          <?php if ($page < $total_pages): ?>
          <a href="?page=<?= $page+1 ?>&role=<?= $filter_role ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>"><button><i class="bi bi-chevron-right"></i></button></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div id="userModal" class="user-modal-overlay">
    <div class="user-modal-box">
      <div class="user-modal-header">
        <h5 id="modalTitle">Thêm người dùng</h5>
        <button class="user-modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form id="userForm">
        <div class="user-modal-body">
          <div id="modalAlertBox"></div>
          <input type="hidden" name="action" id="formAction" value="create">
          <input type="hidden" name="id" id="userId">
          
          <div class="form-group">
            <label>Vai trò <span style="color:red">*</span></label>
            <select name="role_id" id="roleId" required>
              <?php $roles->data_seek(0); while ($role = $roles->fetch_assoc()): ?>
              <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?> - <?= $role['description'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label>Tên đăng nhập <span style="color:red">*</span></label>
            <input type="text" name="username" id="username" required minlength="3">
            <small id="usernameError" class="field-error" style="color:#dc2626;display:none;margin-top:4px;"></small>
          </div>
          
          <div class="form-group">
            <label>Mật khẩu <span id="pwdRequired" style="color:red">*</span></label>
            <input type="password" name="password" id="password" minlength="6">
            <small id="pwdHint" style="color:#666;display:none;">Để trống nếu không muốn đổi mật khẩu</small>
          </div>
          
          <div class="form-group">
            <label>Họ tên <span style="color:red">*</span></label>
            <input type="text" name="full_name" id="fullName" required>
          </div>
          
          <div class="form-group">
            <label>Email <span style="color:red">*</span></label>
            <input type="email" name="email" id="email" required>
            <small id="emailError" class="field-error" style="color:#dc2626;display:none;margin-top:4px;"></small>
          </div>
          
          <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="phone" id="phone">
          </div>
          
          <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" id="status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="user-modal-footer">
          <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
          <button type="submit" class="btn-save">Lưu</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Modal -->
  <div id="viewModal" class="user-modal-overlay">
    <div class="user-modal-box">
      <div class="user-modal-header">
        <h5>Chi tiết người dùng</h5>
        <button class="user-modal-close" onclick="closeViewModal()">&times;</button>
      </div>
      <div class="user-modal-body" id="viewContent"></div>
      <div class="user-modal-footer">
        <button class="btn-cancel" onclick="closeViewModal()">Đóng</button>
      </div>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
  <script>
    function openModal(mode) {
      document.getElementById('userModal').classList.add('show');
      document.getElementById('userForm').reset();
      
      if (mode === 'add') {
        document.getElementById('modalTitle').textContent = 'Thêm người dùng';
        document.getElementById('formAction').value = 'create';
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('pwdRequired').style.display = 'inline';
        document.getElementById('pwdHint').style.display = 'none';
        // Reset error states
        resetFieldErrors();
      }
    }
    
    function resetFieldErrors() {
      document.getElementById('usernameError').style.display = 'none';
      document.getElementById('emailError').style.display = 'none';
      document.getElementById('username').style.borderColor = '#ddd';
      document.getElementById('email').style.borderColor = '#ddd';
    }
    
    function closeModal() {
      document.getElementById('userModal').classList.remove('show');
      resetFieldErrors();
    }
    
    function closeViewModal() {
      document.getElementById('viewModal').classList.remove('show');
    }
    
    function showAlert(message, type) {
      const alertBox = document.getElementById('alertBox');
      alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
      setTimeout(() => alertBox.innerHTML = '', 3000);
    }
    
    function showModalAlert(message, type) {
      const modalAlertBox = document.getElementById('modalAlertBox');
      modalAlertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
      setTimeout(() => modalAlertBox.innerHTML = '', 4000);
    }
    
    function editUser(id) {
      fetch('users.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get&id=' + id
      })
      .then(res => res.json())
      .then(data => {
        document.getElementById('modalTitle').textContent = 'Sửa người dùng';
        document.getElementById('formAction').value = 'update';
        document.getElementById('userId').value = data.id;
        document.getElementById('roleId').value = data.role_id;
        document.getElementById('username').value = data.username || '';
        document.getElementById('fullName').value = data.full_name;
        document.getElementById('email').value = data.email;
        document.getElementById('phone').value = data.phone || '';
        document.getElementById('status').value = data.status;
        document.getElementById('password').value = '';
        document.getElementById('password').required = false;
        document.getElementById('pwdRequired').style.display = 'none';
        document.getElementById('pwdHint').style.display = 'block';
        document.getElementById('userModal').classList.add('show');
      });
    }
    
    function viewUser(id) {
      fetch('users.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get&id=' + id
      })
      .then(res => res.json())
      .then(data => {
        const roles = {1: 'Admin', 2: 'Manager', 3: 'Sales', 4: 'Warehouse'};
        document.getElementById('viewContent').innerHTML = `
          <p><strong>ID:</strong> #${data.id}</p>
          <p><strong>Tên đăng nhập:</strong> ${data.username || 'Google User'}</p>
          <p><strong>Họ tên:</strong> ${data.full_name}</p>
          <p><strong>Email:</strong> ${data.email}</p>
          <p><strong>Số điện thoại:</strong> ${data.phone || '-'}</p>
          <p><strong>Vai trò:</strong> ${roles[data.role_id] || data.role_id}</p>
          <p><strong>Trạng thái:</strong> ${data.status}</p>
        `;
        document.getElementById('viewModal').classList.add('show');
      });
    }
    
    function deleteUser(id, name) {
      if (!confirm(`Bạn có chắc muốn xóa người dùng "${name}"?`)) return;
      
      fetch('users.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&id=' + id
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showAlert(data.message, 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showAlert(data.message, 'error');
        }
      });
    }
    
    document.getElementById('userForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch('users.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeModal();
          showAlert(data.message, 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showModalAlert(data.message, 'error');
        }
      });
    });
    
    // Check duplicate username
    let usernameTimeout;
    document.getElementById('username').addEventListener('input', function() {
      clearTimeout(usernameTimeout);
      const value = this.value.trim();
      const errorEl = document.getElementById('usernameError');
      const excludeId = document.getElementById('userId').value || 0;
      
      if (value.length < 3) {
        errorEl.style.display = 'none';
        this.style.borderColor = '#ddd';
        return;
      }
      
      usernameTimeout = setTimeout(() => {
        fetch('users.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `action=check_duplicate&field=username&value=${encodeURIComponent(value)}&exclude_id=${excludeId}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.exists) {
            errorEl.textContent = '⚠ Tên đăng nhập đã tồn tại!';
            errorEl.style.display = 'block';
            this.style.borderColor = '#dc2626';
          } else {
            errorEl.style.display = 'none';
            this.style.borderColor = '#22c55e';
          }
        });
      }, 300);
    });
    
    // Check duplicate email
    let emailTimeout;
    document.getElementById('email').addEventListener('input', function() {
      clearTimeout(emailTimeout);
      const value = this.value.trim();
      const errorEl = document.getElementById('emailError');
      const excludeId = document.getElementById('userId').value || 0;
      
      if (!value || !value.includes('@')) {
        errorEl.style.display = 'none';
        this.style.borderColor = '#ddd';
        return;
      }
      
      emailTimeout = setTimeout(() => {
        fetch('users.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `action=check_duplicate&field=email&value=${encodeURIComponent(value)}&exclude_id=${excludeId}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.exists) {
            errorEl.textContent = '⚠ Email đã tồn tại!';
            errorEl.style.display = 'block';
            this.style.borderColor = '#dc2626';
          } else {
            errorEl.style.display = 'none';
            this.style.borderColor = '#22c55e';
          }
        });
      }, 300);
    });
  </script>
</body>
</html>
