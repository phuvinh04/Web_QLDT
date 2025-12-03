<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Quản lý nhà cung cấp";
$current_page = "suppliers";
$base_url = "../";
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
          <h1>Danh sách nhà cung cấp</h1>
          <div class="breadcrumb">Trang chủ / Nhà cung cấp</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-truck"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng NCC</h4>
                <div class="stat-value">15</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Đang hợp tác</h4>
                <div class="stat-value">12</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Ngừng hợp tác</h4>
                <div class="stat-value">3</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Thành phố</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Hà Nội</option>
                <option>TP HCM</option>
                <option>Đà Nẵng</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Active</option>
                <option>Inactive</option>
              </select>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm nhà cung cấp
              </button>
            </div>
          </div>
        </div>

        <!-- Suppliers Table -->
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã NCC</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Liên hệ</th>
                    <th>Địa chỉ</th>
                    <th>Người liên hệ</th>
                    <th>Mã số thuế</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>#NCC001</strong></td>
                    <td>
                      <div class="text-dark-bold">FPT Trading</div>
                      <small class="text-muted">Nhà phân phối chính hãng</small>
                    </td>
                    <td>
                      <div><i class="bi bi-phone"></i> 02873000911</div>
                      <small class="text-muted"><i class="bi bi-envelope"></i> info@fpt.com.vn</small>
                    </td>
                    <td>Số 10, Phạm Văn Bạch<br><small>Hà Nội</small></td>
                    <td>Nguyễn Văn A</td>
                    <td><small>0100109106</small></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="td-actions">
                      <button class="action-btn view"><i class="bi bi-eye"></i></button>
                      <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                      <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>#NCC002</strong></td>
                    <td>
                      <div class="text-dark-bold">Digiworld</div>
                      <small class="text-muted">Nhà phân phối điện thoại</small>
                    </td>
                    <td>
                      <div><i class="bi bi-phone"></i> 02839268888</div>
                      <small class="text-muted"><i class="bi bi-envelope"></i> contact@dgw.com.vn</small>
                    </td>
                    <td>195-197 Nguyễn Thái Bình<br><small>TP HCM</small></td>
                    <td>Trần Thị B</td>
                    <td><small>0301234567</small></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="td-actions">
                      <button class="action-btn view"><i class="bi bi-eye"></i></button>
                      <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                      <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="pagination">
          <button><i class="bi bi-chevron-left"></i></button>
          <button class="active">1</button>
          <button>2</button>
          <button><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
</body>
</html>
