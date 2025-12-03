<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Báo cáo thống kê";
$current_page = "reports";
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
          <h1>Báo cáo & Thống kê</h1>
          <div class="breadcrumb">Trang chủ / Báo cáo</div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Loại báo cáo</label>
              <select class="form-control">
                <option>Doanh thu</option>
                <option>Sản phẩm bán chạy</option>
                <option>Tồn kho</option>
                <option>Khách hàng</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Từ ngày</label>
              <input type="date" class="form-control" value="2024-11-01">
            </div>
            <div class="filter-group">
              <label>Đến ngày</label>
              <input type="date" class="form-control" value="2024-11-12">
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary">
                <i class="bi bi-search"></i> Xem báo cáo
              </button>
            </div>
          </div>
        </div>

        <!-- Revenue Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon blue">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="stat-info">
                <h4>Doanh thu</h4>
                <div class="stat-value">2.5 tỷ</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +15.3%</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-graph-up"></i>
              </div>
              <div class="stat-info">
                <h4>Lợi nhuận</h4>
                <div class="stat-value">450M</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +12.8%</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-receipt"></i>
              </div>
              <div class="stat-info">
                <h4>Đơn hàng</h4>
                <div class="stat-value">1,248</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +8.5%</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-cart"></i>
              </div>
              <div class="stat-info">
                <h4>Giá trị TB</h4>
                <div class="stat-value">2.0M</div>
                <div class="stat-change up"><i class="bi bi-arrow-up"></i> +6.2%</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="card">
          <div class="card-header">
            <h3>Top 10 sản phẩm bán chạy</h3>
            <button class="btn btn-secondary btn-sm">
              <i class="bi bi-download"></i> Xuất Excel
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Xếp hạng</th>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Số lượng bán</th>
                    <th>Doanh thu</th>
                    <th>Lợi nhuận</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span class="badge badge-warning" style="font-size: 1rem;">🥇 1</span></td>
                    <td><strong>IP15PM256</strong></td>
                    <td>iPhone 15 Pro Max 256GB</td>
                    <td><span class="badge badge-primary">iPhone</span></td>
                    <td><strong>145</strong></td>
                    <td><strong class="text-primary">4.640.000.000₫</strong></td>
                    <td><strong class="text-success">507.500.000₫</strong></td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-secondary" style="font-size: 1rem;">🥈 2</span></td>
                    <td><strong>SS24U512</strong></td>
                    <td>Samsung Galaxy S24 Ultra 512GB</td>
                    <td><span class="badge badge-success">Samsung</span></td>
                    <td><strong>128</strong></td>
                    <td><strong class="text-primary">4.032.000.000₫</strong></td>
                    <td><strong class="text-success">576.000.000₫</strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <?php include '../components/footer.php'; ?>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
</body>
</html>
