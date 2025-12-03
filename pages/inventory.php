<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Quản lý kho";
$current_page = "inventory";
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
          <h1>Quản lý kho hàng</h1>
          <div class="breadcrumb">Trang chủ / Quản lý kho</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-box-seam"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng tồn kho</h4>
                <div class="stat-value">1,580</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-arrow-down-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Nhập hôm nay</h4>
                <div class="stat-value">45</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-arrow-up-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Xuất hôm nay</h4>
                <div class="stat-value">28</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <div class="stat-info">
                <h4>Cảnh báo</h4>
                <div class="stat-value">12</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Alert -->
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>Có 12 sản phẩm dưới mức tồn kho tối thiểu. Cần nhập hàng gấp!</span>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Loại giao dịch</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Nhập kho</option>
                <option>Xuất kho</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Từ ngày</label>
              <input type="date" class="form-control">
            </div>
            <div class="filter-group">
              <label>Đến ngày</label>
              <input type="date" class="form-control">
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-success">
                <i class="bi bi-box-arrow-in-down"></i> Nhập kho
              </button>
            </div>
          </div>
        </div>

        <!-- Stock Movements -->
        <div class="card">
          <div class="card-header">
            <h3>Lịch sử nhập/xuất kho</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Mã phiếu</th>
                    <th>Loại</th>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Nhà cung cấp</th>
                    <th>Nhân viên</th>
                    <th>Ghi chú</th>
                    <th>Ngày</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>NK20241112001</strong></td>
                    <td><span class="badge badge-success"><i class="bi bi-arrow-down"></i> Nhập</span></td>
                    <td>iPhone 15 Pro Max 256GB</td>
                    <td><strong class="text-success">+20</strong></td>
                    <td>FPT Trading</td>
                    <td>Cường Trần</td>
                    <td>Nhập hàng định kỳ</td>
                    <td>12/11/2024 09:00</td>
                  </tr>
                  <tr>
                    <td><strong>XK20241112001</strong></td>
                    <td><span class="badge badge-danger"><i class="bi bi-arrow-up"></i> Xuất</span></td>
                    <td>Samsung Galaxy S24 Ultra</td>
                    <td><strong class="text-danger">-1</strong></td>
                    <td>-</td>
                    <td>Bình Minh</td>
                    <td>Bán hàng - HD20241112002</td>
                    <td>12/11/2024 11:15</td>
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
