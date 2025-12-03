<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Quản lý khách hàng";
$current_page = "customers";
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
          <h1>Danh sách khách hàng</h1>
          <div class="breadcrumb">Trang chủ / Khách hàng</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-people"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng khách hàng</h4>
                <div class="stat-value">1,248</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +45 tháng này</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-star"></i>
              </div>
              <div class="stat-info">
                <h4>Khách hàng VIP</h4>
                <div class="stat-value">156</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +12 tháng này</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-gift"></i>
              </div>
              <div class="stat-info">
                <h4>Điểm thưởng</h4>
                <div class="stat-value">45,890</div>
                <div class="stat-change">Tổng điểm đã tích lũy</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Loại khách hàng</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Khách VIP</option>
                <option>Khách thường</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Thành phố</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>TP HCM</option>
                <option>Hà Nội</option>
                <option>Đà Nẵng</option>
              </select>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm khách hàng
              </button>
            </div>
          </div>
        </div>

        <!-- Customers Table -->
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã KH</th>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Địa chỉ</th>
                    <th>Tổng mua</th>
                    <th>Số đơn</th>
                    <th>Điểm</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>#KH001</strong></td>
                    <td>
                      <div class="text-dark-bold">Nguyễn Văn A</div>
                      <small class="text-secondary">Khách VIP</small>
                    </td>
                    <td>
                      <div><i class="bi bi-phone"></i> 0912345678</div>
                      <small class="text-secondary"><i class="bi bi-envelope"></i> nguyenvana@gmail.com</small>
                    </td>
                    <td>123 Nguyễn Trãi, Q5<br><small>TP HCM</small></td>
                    <td><strong class="text-primary">125.500.000₫</strong></td>
                    <td><strong>15</strong></td>
                    <td><span class="badge badge-warning">1,255 điểm</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="td-actions">
                      <button class="action-btn view"><i class="bi bi-eye"></i></button>
                      <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>#KH002</strong></td>
                    <td>
                      <div class="text-dark-bold">Trần Thị B</div>
                      <small class="text-muted">Khách thường</small>
                    </td>
                    <td>
                      <div><i class="bi bi-phone"></i> 0987654321</div>
                      <small class="text-muted"><i class="bi bi-envelope"></i> tranthib@gmail.com</small>
                    </td>
                    <td>456 Lê Lợi, Q1<br><small>TP HCM</small></td>
                    <td><strong class="text-primary">63.500.000₫</strong></td>
                    <td><strong>8</strong></td>
                    <td><span class="badge badge-warning">635 điểm</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="td-actions">
                      <button class="action-btn view"><i class="bi bi-eye"></i></button>
                      <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
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
          <button>3</button>
          <button><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
</body>
</html>
