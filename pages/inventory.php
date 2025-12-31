<?php 
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';

$page_title = "Quản lý kho";
$current_page = "inventory";
$base_url = "../";

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'stock_in' || $action === 'stock_out') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
        $reference_number = trim($_POST['reference_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'];
        $type = ($action === 'stock_in') ? 'in' : 'out';
        
        if ($product_id <= 0) {
            $error = "Vui lòng chọn sản phẩm!";
        } elseif ($quantity <= 0) {
            $error = "Số lượng phải lớn hơn 0!";
        } else {
            // Check stock for out transaction
            if ($type === 'out') {
                $check = $conn->query("SELECT quantity FROM products WHERE id = $product_id");
                $product = $check->fetch_assoc();
                if ($product['quantity'] < $quantity) {
                    $error = "Số lượng xuất kho vượt quá tồn kho hiện tại ({$product['quantity']})!";
                }
            }
            
            if (empty($error)) {
                $conn->begin_transaction();
                
                try {
                    // Insert stock movement
                    $stmt = $conn->prepare("INSERT INTO stock_movements (product_id, user_id, supplier_id, type, quantity, reference_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiisiss", $product_id, $user_id, $supplier_id, $type, $quantity, $reference_number, $notes);
                    $stmt->execute();
                    
                    // Update product stock
                    if ($type === 'in') {
                        $conn->query("UPDATE products SET quantity = quantity + $quantity WHERE id = $product_id");
                    } else {
                        $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id = $product_id");
                    }
                    
                    $conn->commit();
                    $message = ($type === 'in') ? "Nhập kho thành công!" : "Xuất kho thành công!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Lỗi: " . $e->getMessage();
                }
            }
        }
    }
}

// Get filter parameters
$filter_type = $_GET['type'] ?? '';
$filter_product = $_GET['product'] ?? '';
$filter_supplier = $_GET['supplier'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query for movements
$where = [];
$params = [];
$types = '';

if (!empty($filter_type)) {
    $where[] = "sm.type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

if (!empty($filter_product)) {
    $where[] = "sm.product_id = ?";
    $params[] = intval($filter_product);
    $types .= 'i';
}

if (!empty($filter_supplier)) {
    $where[] = "sm.supplier_id = ?";
    $params[] = intval($filter_supplier);
    $types .= 'i';
}

if (!empty($date_from)) {
    $where[] = "DATE(sm.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where[] = "DATE(sm.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$sql = "SELECT sm.*, p.name as product_name, p.image as product_image, s.name as supplier_name, u.username as user_name
        FROM stock_movements sm
        LEFT JOIN products p ON sm.product_id = p.id
        LEFT JOIN suppliers s ON sm.supplier_id = s.id
        LEFT JOIN users u ON sm.user_id = u.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY sm.created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$movements = $stmt->get_result();

// Get all products with stock
$productsResult = $conn->query("SELECT id, name, quantity, image FROM products ORDER BY name");

// Get suppliers for select
$suppliers = $conn->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");

// Get stats
$today = date('Y-m-d');
$statsQuery = $conn->query("SELECT 
    (SELECT COALESCE(SUM(quantity), 0) FROM products) as total_stock,
    (SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'in' AND DATE(created_at) = '$today') as today_in,
    (SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'out' AND DATE(created_at) = '$today') as today_out,
    (SELECT COUNT(*) FROM products WHERE quantity <= 10) as low_stock
");
$stats = $statsQuery ? $statsQuery->fetch_assoc() : ['total_stock' => 0, 'today_in' => 0, 'today_out' => 0, 'low_stock' => 0];

// Get low stock products
$lowStockProducts = $conn->query("SELECT id, name, quantity, image FROM products WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 10");
$hasLowStock = $lowStockProducts && $lowStockProducts->num_rows > 0;
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
                <i class="bi bi-box-seam"></i>
              </div>
              <div class="stat-info">
                <h4>Tổng tồn kho</h4>
                <div class="stat-value"><?php echo number_format($stats['total_stock'] ?? 0); ?></div>
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
                <div class="stat-value"><?php echo number_format($stats['today_in'] ?? 0); ?></div>
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
                <div class="stat-value"><?php echo number_format($stats['today_out'] ?? 0); ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="stat-card">
              <div class="stat-icon red">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <div class="stat-info">
                <h4>Sắp hết hàng</h4>
                <div class="stat-value"><?php echo $stats['low_stock'] ?? 0; ?></div>
              </div>
            </div>
          </div>
        </div>

        <?php if ($hasLowStock): ?>
        <!-- Low Stock Alert -->
        <div class="alert alert-warning mb-4">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Cảnh báo!</strong> Có <?php echo $stats['low_stock']; ?> sản phẩm sắp hết hàng (tồn kho ≤ 10)
            </div>
            <button class="btn btn-sm btn-warning" data-bs-toggle="collapse" data-bs-target="#lowStockList">
              Xem chi tiết
            </button>
          </div>
          <div class="collapse mt-3" id="lowStockList">
            <div class="row g-2">
              <?php while ($lowProduct = $lowStockProducts->fetch_assoc()): ?>
              <div class="col-md-4 col-lg-3">
                <div class="d-flex align-items-center bg-white rounded p-2">
                  <img src="../assets/images/products/<?php echo $lowProduct['image'] ?: 'default.jpg'; ?>" 
                       alt="<?php echo htmlspecialchars($lowProduct['name']); ?>" 
                       class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                  <div class="flex-grow-1">
                    <div class="small fw-bold text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($lowProduct['name']); ?></div>
                    <small class="text-danger">Còn <?php echo $lowProduct['quantity']; ?> SP</small>
                  </div>
                  <button class="btn btn-sm btn-success" onclick="openStockInModal(<?php echo $lowProduct['id']; ?>)">
                    <i class="bi bi-plus"></i>
                  </button>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="mb-4">
          <button class="btn btn-success me-2" onclick="openStockInModal()">
            <i class="bi bi-arrow-down-circle"></i> Nhập kho
          </button>
          <button class="btn btn-warning" onclick="openStockOutModal()">
            <i class="bi bi-arrow-up-circle"></i> Xuất kho
          </button>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
          <form method="GET" class="filter-row">
            <div class="filter-group">
              <label>Loại giao dịch</label>
              <select name="type" class="form-control">
                <option value="">Tất cả</option>
                <option value="in" <?php echo $filter_type === 'in' ? 'selected' : ''; ?>>Nhập kho</option>
                <option value="out" <?php echo $filter_type === 'out' ? 'selected' : ''; ?>>Xuất kho</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Sản phẩm</label>
              <select name="product" class="form-control">
                <option value="">Tất cả</option>
                <?php 
                $productsResult->data_seek(0);
                while ($p = $productsResult->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $filter_product == $p['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($p['name']); ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="filter-group">
              <label>Từ ngày</label>
              <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            <div class="filter-group">
              <label>Đến ngày</label>
              <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
            <div class="filter-group action">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-secondary">
                <i class="bi bi-search"></i> Lọc
              </button>
            </div>
          </form>
        </div>

        <!-- Stock Movements Table -->
        <div class="data-table">
          <table class="table">
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Sản phẩm</th>
                <th>Loại</th>
                <th>Số lượng</th>
                <th>Nhà cung cấp</th>
                <th>Mã tham chiếu</th>
                <th>Người thực hiện</th>
                <th>Ghi chú</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($movements->num_rows > 0): ?>
                <?php while ($movement = $movements->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div><?php echo date('d/m/Y', strtotime($movement['created_at'])); ?></div>
                    <small class="text-muted"><?php echo date('H:i', strtotime($movement['created_at'])); ?></small>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="../assets/images/products/<?php echo $movement['product_image'] ?: 'default.jpg'; ?>" 
                           alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                      <span><?php echo htmlspecialchars($movement['product_name']); ?></span>
                    </div>
                  </td>
                  <td>
                    <?php if ($movement['type'] === 'in'): ?>
                    <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Nhập</span>
                    <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up"></i> Xuất</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="fw-bold <?php echo $movement['type'] === 'in' ? 'text-success' : 'text-warning'; ?>">
                      <?php echo $movement['type'] === 'in' ? '+' : '-'; ?><?php echo number_format($movement['quantity']); ?>
                    </span>
                  </td>
                  <td><?php echo htmlspecialchars($movement['supplier_name'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($movement['reference_number'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($movement['user_name']); ?></td>
                  <td>
                    <?php if ($movement['notes']): ?>
                    <span class="text-muted small" title="<?php echo htmlspecialchars($movement['notes']); ?>">
                      <?php echo htmlspecialchars(mb_substr($movement['notes'], 0, 30)); ?>...
                    </span>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Chưa có giao dịch kho nào</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Current Stock Section -->
        <h5 class="mt-5 mb-3"><i class="bi bi-box-seam"></i> Tồn kho hiện tại</h5>
        <div class="row g-3">
          <?php 
          $productsResult->data_seek(0);
          while ($product = $productsResult->fetch_assoc()): 
            $stockClass = $product['quantity'] <= 10 ? 'bg-danger' : ($product['quantity'] <= 30 ? 'bg-warning' : 'bg-success');
          ?>
          <div class="col-md-4 col-lg-3">
            <div class="card">
              <div class="card-body d-flex align-items-center">
                <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                     alt="" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                  <div class="fw-bold text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php echo htmlspecialchars($product['name']); ?>
                  </div>
                  <span class="badge <?php echo $stockClass; ?>"><?php echo number_format($product['quantity']); ?> SP</span>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock In Modal -->
  <div id="stockInModal" class="custom-modal-overlay">
    <div class="custom-modal-box">
      <form method="POST">
        <input type="hidden" name="action" value="stock_in">
        <div class="custom-modal-header" style="background: #059669; color: white;">
          <h5><i class="bi bi-arrow-down-circle"></i> Nhập kho</h5>
          <button type="button" class="custom-modal-close" style="color: white;" onclick="closeStockInModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="mb-3">
            <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
            <select name="product_id" id="stockin_product_id" class="form-control" required>
              <option value="">-- Chọn sản phẩm --</option>
              <?php 
              $productsResult->data_seek(0);
              while ($p = $productsResult->fetch_assoc()): ?>
              <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (Tồn: <?php echo $p['quantity']; ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Số lượng nhập <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nhà cung cấp</label>
            <select name="supplier_id" class="form-control">
              <option value="">-- Chọn nhà cung cấp --</option>
              <?php 
              if ($suppliers) { $suppliers->data_seek(0); }
              while ($suppliers && $s = $suppliers->fetch_assoc()): ?>
              <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Mã tham chiếu (Hóa đơn/PO)</label>
            <input type="text" name="reference_number" class="form-control" placeholder="VD: HD001, PO-2024-001">
          </div>
          <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeStockInModal()">Hủy</button>
          <button type="submit" class="btn btn-success">Nhập kho</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Stock Out Modal -->
  <div id="stockOutModal" class="custom-modal-overlay">
    <div class="custom-modal-box">
      <form method="POST">
        <input type="hidden" name="action" value="stock_out">
        <div class="custom-modal-header" style="background: #d97706; color: white;">
          <h5><i class="bi bi-arrow-up-circle"></i> Xuất kho</h5>
          <button type="button" class="custom-modal-close" style="color: white;" onclick="closeStockOutModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
          <div class="mb-3">
            <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
            <select name="product_id" class="form-control" required>
              <option value="">-- Chọn sản phẩm --</option>
              <?php 
              $productsResult->data_seek(0);
              while ($p = $productsResult->fetch_assoc()): ?>
              <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (Tồn: <?php echo $p['quantity']; ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Số lượng xuất <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mã tham chiếu (Đơn hàng)</label>
            <input type="text" name="reference_number" class="form-control" placeholder="VD: DH001, ORDER-2024-001">
          </div>
          <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeStockOutModal()">Hủy</button>
          <button type="submit" class="btn btn-warning">Xuất kho</button>
        </div>
      </form>
    </div>
  </div>

  <?php include '../components/scripts.php'; ?>
  
  <script>
    function openStockInModal(productId) {
      if (productId) {
        document.getElementById('stockin_product_id').value = productId;
      }
      document.getElementById('stockInModal').classList.add('show');
    }
    
    function closeStockInModal() {
      document.getElementById('stockInModal').classList.remove('show');
    }
    
    function openStockOutModal() {
      document.getElementById('stockOutModal').classList.add('show');
    }
    
    function closeStockOutModal() {
      document.getElementById('stockOutModal').classList.remove('show');
    }
    
    // Close modal when clicking outside
    document.getElementById('stockInModal').addEventListener('click', function(e) {
      if (e.target === this) closeStockInModal();
    });
    document.getElementById('stockOutModal').addEventListener('click', function(e) {
      if (e.target === this) closeStockOutModal();
    });
  </script>
</body>
</html>
