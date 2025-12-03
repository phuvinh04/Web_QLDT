<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Quản lý sản phẩm";
$current_page = "products";
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
          <h1>Danh sách sản phẩm</h1>
          <div class="breadcrumb">Trang chủ / Sản phẩm</div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
          <div class="filter-row">
            <div class="filter-group">
              <label>Danh mục</label>
              <select class="form-control">
                <option>Tất cả danh mục</option>
                <option>iPhone</option>
                <option>Samsung</option>
                <option>Xiaomi</option>
                <option>OPPO</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Trạng thái</label>
              <select class="form-control">
                <option>Tất cả</option>
                <option>Còn hàng</option>
                <option>Hết hàng</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Sắp xếp</label>
              <select class="form-control">
                <option>Mới nhất</option>
                <option>Giá thấp đến cao</option>
                <option>Giá cao đến thấp</option>
                <option>Tên A-Z</option>
              </select>
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm sản phẩm
              </button>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4">
          <div class="col-md-6 col-lg-4">
            <div class="product-card">
              <div class="product-image">
                <i class="bi bi-phone"></i>
              </div>
              <div class="product-body">
                <div class="product-category">iPhone</div>
                <div class="product-name">iPhone 15 Pro Max 256GB</div>
                <div class="product-price">32.000.000₫</div>
                <div class="product-stock">
                  <i class="bi bi-box"></i> Tồn kho: <strong>50</strong> | SKU: <strong>IP15PM256</strong>
                </div>
                <div class="product-actions">
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                  <button class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-cart-plus"></i> Bán
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-lg-4">
            <div class="product-card">
              <div class="product-image bg-gradient-pink">
                <i class="bi bi-phone"></i>
              </div>
              <div class="product-body">
                <div class="product-category">Samsung</div>
                <div class="product-name">Samsung Galaxy S24 Ultra 512GB</div>
                <div class="product-price">31.500.000₫</div>
                <div class="product-stock">
                  <i class="bi bi-box"></i> Tồn kho: <strong>30</strong> | SKU: <strong>SS24U512</strong>
                </div>
                <div class="product-actions">
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                  <button class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-cart-plus"></i> Bán
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-lg-4">
            <div class="product-card">
              <div class="product-image bg-gradient-blue">
                <i class="bi bi-phone"></i>
              </div>
              <div class="product-body">
                <div class="product-category">Xiaomi</div>
                <div class="product-name">Xiaomi 14 256GB</div>
                <div class="product-price">15.990.000₫</div>
                <div class="product-stock">
                  <i class="bi bi-box"></i> Tồn kho: <strong>80</strong> | SKU: <strong>XI14256</strong>
                </div>
                <div class="product-actions">
                  <button class="action-btn view"><i class="bi bi-eye"></i></button>
                  <button class="action-btn edit"><i class="bi bi-pencil"></i></button>
                  <button class="action-btn delete"><i class="bi bi-trash"></i></button>
                  <button class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-cart-plus"></i> Bán
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
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
