<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Quản lý khuyến mãi";
$current_page = "promotions";
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
          <h1>Chương trình khuyến mãi</h1>
          <div class="breadcrumb">Trang chủ / Khuyến mãi</div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon green">
                <i class="bi bi-tag"></i>
              </div>
              <div class="stat-info">
                <h4>Đang hoạt động</h4>
                <div class="stat-value">8</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon orange">
                <i class="bi bi-clock"></i>
              </div>
              <div class="stat-info">
                <h4>Sắp diễn ra</h4>
                <div class="stat-value">3</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
              </div>
              <div class="stat-info">
                <h4>Đã kết thúc</h4>
                <div class="stat-value">15</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Trạng thái</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Đang hoạt động</option>
                <option>Sắp diễn ra</option>
                <option>Đã kết thúc</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Loại giảm giá</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Giảm theo %</option>
                <option>Giảm cố định</option>
              </select>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tạo khuyến mãi
              </button>
            </div>
          </div>
        </div>

        <!-- Promotions Grid -->
        <div class="row g-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <div class="promo-header">
                  <div>
                    <h4 class="fs-5 mb-1">Giảm giá cuối tuần</h4>
                    <span class="badge badge-success">Đang hoạt động</span>
                  </div>
                  <div class="text-end">
                    <div class="promo-discount-val">-5%</div>
                    <small class="text-secondary">Giảm theo %</small>
                  </div>
                </div>
                <p class="text-secondary mb-3">
                  Áp dụng cho tất cả sản phẩm có giá trị từ 20.000.000₫
                </p>
                <div class="promo-meta">
                  <div>
                    <i class="bi bi-calendar-event text-primary"></i>
                    <strong>Bắt đầu:</strong> 20/05/2024
                  </div>
                  <div>
                    <i class="bi bi-calendar-x text-danger"></i>
                    <strong>Kết thúc:</strong> 31/12/2025
                  </div>
                </div>
                <div style="display: flex; gap: 10px;">
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                  <button class="btn btn-secondary btn-sm flex-grow-1">
                    <i class="bi bi-pause-circle"></i> Tạm dừng
                  </button>
                </div>
              </div>
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
