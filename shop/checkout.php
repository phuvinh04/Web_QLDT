<?php
// Checkout Page - Using LocalStorage Cart
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Thanh toán - PhoneStore";
$base_url = "../";

require_once '../config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch();

$error_message = '';

// Handle form submission (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    header('Content-Type: application/json');
    
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $cart_data = json_decode($_POST['cart_data'] ?? '{}', true);
    $promotion_id = !empty($_POST['promotion_id']) ? intval($_POST['promotion_id']) : null;
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    
    if (empty($full_name) || empty($phone) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin giao hàng']);
        exit;
    }
    
    if (empty($cart_data)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }
    
    try {
        // Get product details and validate
        $product_ids = array_keys($cart_data);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        $products_stmt = $pdo->prepare("SELECT id, name, price, quantity FROM products WHERE id IN ($placeholders) AND status = 'active'");
        $products_stmt->execute($product_ids);
        $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($products)) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
            exit;
        }
        
        // Calculate totals
        $subtotal = 0;
        $order_items = [];
        
        foreach ($products as $product) {
            $qty = (int)($cart_data[$product['id']] ?? 0);
            if ($qty <= 0) continue;
            
            if ($qty > $product['quantity']) {
                echo json_encode(['success' => false, 'message' => "Sản phẩm '{$product['name']}' không đủ số lượng"]);
                exit;
            }
            
            $subtotal += $product['price'] * $qty;
            $order_items[] = [
                'product_id' => $product['id'],
                'quantity' => $qty,
                'price' => $product['price']
            ];
        }
        
        // Validate promotion if applied
        $final_discount = 0;
        if ($promotion_id && $discount_amount > 0) {
            $today = date('Y-m-d');
            $promo_stmt = $pdo->prepare("
                SELECT * FROM promotions 
                WHERE id = ? AND active = 1 
                AND (start_date IS NULL OR start_date <= ?)
                AND (end_date IS NULL OR end_date >= ?)
            ");
            $promo_stmt->execute([$promotion_id, $today, $today]);
            $promotion = $promo_stmt->fetch();
            
            if ($promotion) {
                // Verify min_amount
                if ($promotion['min_amount'] <= 0 || $subtotal >= $promotion['min_amount']) {
                    $final_discount = min($discount_amount, $subtotal); // Không giảm quá subtotal
                }
            }
        }
        
        $after_discount = $subtotal - $final_discount;
        $shipping = $after_discount >= 500000 ? 0 : 30000;
        $tax = $after_discount * 0.1;
        $total = $after_discount + $shipping + $tax;
        
        $pdo->beginTransaction();
        
        // Tìm hoặc tạo customer_id dựa trên email của user
        $customer_id = null;
        if (!empty($user['email'])) {
            // Tìm customer theo email
            $customer_stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $customer_stmt->execute([$user['email']]);
            $customer = $customer_stmt->fetch();
            
            if ($customer) {
                $customer_id = $customer['id'];
            } else {
                // Tạo customer mới nếu chưa có
                $create_customer = $pdo->prepare("
                    INSERT INTO customers (name, phone, email, address, status) 
                    VALUES (?, ?, ?, ?, 'active')
                ");
                $create_customer->execute([
                    $full_name,
                    $phone,
                    $user['email'],
                    $address
                ]);
                $customer_id = $pdo->lastInsertId();
            }
        }
        
        // Create order
        $order_code = 'HD' . date('YmdHis') . rand(100, 999);
        
        $order_stmt = $pdo->prepare("
            INSERT INTO orders (order_number, customer_id, user_id, subtotal, discount, tax, total_amount, payment_method, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'cod', ?, 'pending')
        ");
        $order_stmt->execute([
            $order_code, $customer_id, $user_id, $subtotal, $final_discount, $tax, $total,
            "Tên: $full_name\nSĐT: $phone\nĐịa chỉ: $address\nGhi chú: $note"
        ]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        
        foreach ($order_items as $item) {
            $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product stock
            $update_stock = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $update_stock->execute([$item['quantity'], $item['product_id']]);
        }
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'order_code' => $order_code]);
        exit;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/shop.css">
</head>
<body>
    <?php include 'components/shop_header.php'; ?>

    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house"></i> Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Giỏ hàng</a></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <div class="page-header mb-4">
            <h1 class="page-title"><i class="bi bi-credit-card"></i> Thanh toán</h1>
        </div>

        <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>

        <form id="checkoutForm">
            <input type="hidden" name="action" value="place_order">
            <div class="row">
                <div class="col-lg-7">
                    <!-- Shipping Info -->
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Thông tin giao hàng</h5>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Họ và tên *</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại *</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ giao hàng *</label>
                                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú cho đơn hàng..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-wallet2 me-2"></i>Phương thức thanh toán</h5>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-coin text-success me-3" style="font-size: 2rem;"></i>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng (COD)</strong>
                                        <p class="text-muted mb-0 small">Bạn sẽ thanh toán bằng tiền mặt khi nhận được hàng</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <!-- Order Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Đơn hàng của bạn</h5>
                        </div>
                        <div class="card-body">
                            <div id="orderItems" class="mb-3">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <span class="ms-2">Đang tải...</span>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span id="orderSubtotal">0₫</span>
                            </div>
                            
                            <!-- Phần áp dụng khuyến mãi -->
                            <div class="promotion-section mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-tag text-success"></i> Khuyến mãi:</span>
                                    <span id="discountAmount" class="text-success">0₫</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <select id="promotionSelect" class="form-select form-select-sm">
                                        <option value="">-- Chọn khuyến mãi --</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="applyPromotionBtn">
                                        Áp dụng
                                    </button>
                                </div>
                                <div id="promotionInfo" class="small text-muted mt-1" style="display: none;"></div>
                                <div id="promotionError" class="small text-danger mt-1" style="display: none;"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <span id="orderShipping">Tính sau</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Thuế (10%):</span>
                                <span id="orderTax">0₫</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng cộng:</strong>
                                <strong id="orderTotal" class="text-danger">0₫</strong>
                            </div>
                            
                            <input type="hidden" name="promotion_id" id="appliedPromotionId" value="">
                            <input type="hidden" name="discount_amount" id="appliedDiscountAmount" value="0">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="placeOrderBtn">
                                    <i class="bi bi-check-circle me-2"></i>Đặt hàng
                                </button>
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại giỏ hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'components/shop_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/shop.js?v=2"></script>
    <script>
        let currentSubtotal = 0;
        let currentDiscount = 0;
        let cartProducts = [];
        let cartData = {};
        
        document.addEventListener('DOMContentLoaded', async function() {
            cartData = JSON.parse(localStorage.getItem('shop_cart') || '{}');
            const productIds = Object.keys(cartData).filter(id => cartData[id] > 0);
            
            if (productIds.length === 0) {
                window.location.href = 'cart.php';
                return;
            }
            
            // Fetch product details
            try {
                const response = await fetch(`api/cart_items.php?ids=${productIds.join(',')}`);
                const data = await response.json();
                
                if (data.success) {
                    cartProducts = data.products;
                    renderOrderItems(data.products, cartData);
                    // Load available promotions
                    loadPromotions();
                }
            } catch (error) {
                console.error('Error:', error);
            }
            
            // Event listener for apply promotion button
            document.getElementById('applyPromotionBtn').addEventListener('click', applyPromotion);
            document.getElementById('promotionSelect').addEventListener('change', function() {
                // Reset khi thay đổi selection
                document.getElementById('promotionError').style.display = 'none';
                document.getElementById('promotionInfo').style.display = 'none';
            });
        });
        
        async function loadPromotions() {
            try {
                // Gửi cart_data để API lọc khuyến mãi phù hợp
                const cartData = JSON.parse(localStorage.getItem('cart') || '{}');
                
                const response = await fetch('api/promotions.php?action=get_available', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_data: cartData
                    })
                });
                const data = await response.json();
                
                if (data.success && data.promotions.length > 0) {
                    const select = document.getElementById('promotionSelect');
                    data.promotions.forEach(promo => {
                        const option = document.createElement('option');
                        option.value = promo.id;
                        
                        let discountText = promo.discount_type === 'percent' 
                            ? `-${promo.discount_value}%` 
                            : `-${formatPrice(promo.discount_value)}đ`;
                        
                        let conditionText = '';
                        if (promo.min_amount > 0) {
                            conditionText = ` (Đơn từ ${formatPrice(promo.min_amount)}đ)`;
                        }
                        if (promo.product_name) {
                            conditionText = ` (Áp dụng: ${promo.product_name})`;
                        }
                        
                        option.textContent = `${promo.name} ${discountText}${conditionText}`;
                        option.dataset.promo = JSON.stringify(promo);
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading promotions:', error);
            }
        }
        
        async function applyPromotion() {
            const select = document.getElementById('promotionSelect');
            const promotionId = select.value;
            const errorDiv = document.getElementById('promotionError');
            const infoDiv = document.getElementById('promotionInfo');
            
            errorDiv.style.display = 'none';
            infoDiv.style.display = 'none';
            
            if (!promotionId) {
                // Remove promotion
                currentDiscount = 0;
                document.getElementById('appliedPromotionId').value = '';
                document.getElementById('appliedDiscountAmount').value = '0';
                updateTotals();
                return;
            }
            
            try {
                const response = await fetch('api/promotions.php?action=apply', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        promotion_id: promotionId,
                        cart_data: cartData
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    currentDiscount = result.discount;
                    document.getElementById('appliedPromotionId').value = promotionId;
                    document.getElementById('appliedDiscountAmount').value = result.discount;
                    
                    if (result.discount > 0) {
                        infoDiv.innerHTML = `<i class="bi bi-check-circle text-success"></i> Đã áp dụng: Giảm ${formatPrice(result.discount)}đ`;
                        infoDiv.style.display = 'block';
                    }
                    
                    updateTotals();
                } else {
                    errorDiv.textContent = result.message;
                    errorDiv.style.display = 'block';
                    currentDiscount = 0;
                    document.getElementById('appliedPromotionId').value = '';
                    document.getElementById('appliedDiscountAmount').value = '0';
                    updateTotals();
                }
            } catch (error) {
                errorDiv.textContent = 'Lỗi khi áp dụng khuyến mãi';
                errorDiv.style.display = 'block';
            }
        }
        
        function renderOrderItems(products, cart) {
            const container = document.getElementById('orderItems');
            let html = '';
            currentSubtotal = 0;
            
            products.forEach(product => {
                const qty = cart[product.id] || 0;
                if (qty <= 0) return;
                
                const itemTotal = product.price * qty;
                currentSubtotal += itemTotal;
                
                html += `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span class="fw-medium">${product.name}</span>
                            <small class="text-muted d-block">x${qty}</small>
                        </div>
                        <span>${formatPrice(itemTotal)}₫</span>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            updateTotals();
        }
        
        function updateTotals() {
            const subtotal = currentSubtotal;
            const discount = currentDiscount;
            const afterDiscount = subtotal - discount;
            const shipping = afterDiscount >= 500000 ? 0 : 30000;
            const tax = afterDiscount * 0.1;
            const total = afterDiscount + shipping + tax;
            
            document.getElementById('orderSubtotal').textContent = formatPrice(subtotal) + '₫';
            document.getElementById('discountAmount').textContent = discount > 0 ? `-${formatPrice(discount)}₫` : '0₫';
            document.getElementById('orderShipping').innerHTML = shipping === 0 
                ? '<span class="text-success">Miễn phí</span>' 
                : formatPrice(shipping) + '₫';
            document.getElementById('orderTax').textContent = formatPrice(Math.round(tax)) + '₫';
            document.getElementById('orderTotal').textContent = formatPrice(Math.round(total)) + '₫';
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price);
        }
        
        // Handle form submission
        document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('placeOrderBtn');
            const errorAlert = document.getElementById('errorAlert');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            errorAlert.style.display = 'none';
            
            const formData = new FormData(this);
            formData.append('cart_data', localStorage.getItem('shop_cart') || '{}');
            formData.append('promotion_id', document.getElementById('appliedPromotionId').value || '');
            formData.append('discount_amount', document.getElementById('appliedDiscountAmount').value || '0');
            
            try {
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear cart
                    localStorage.removeItem('shop_cart');
                    // Redirect to success page
                    window.location.href = 'order_success.php?order=' + result.order_code;
                } else {
                    errorAlert.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>' + result.message;
                    errorAlert.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Đặt hàng';
                }
            } catch (error) {
                errorAlert.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Lỗi kết nối server';
                errorAlert.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Đặt hàng';
            }
        });
    </script>
</body>
</html>
