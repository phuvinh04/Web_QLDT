<!-- Product Quick View Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="productModalBody">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cart3"></i> Giỏ hàng của bạn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cartModalBody">
                <!-- Cart content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tiếp tục mua sắm</button>
                <button type="button" class="btn btn-primary" onclick="proceedToCheckout()">
                    <i class="bi bi-credit-card"></i> Thanh toán
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Order Tracking Modal -->
<div class="modal fade" id="orderTrackingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-truck"></i> Tra cứu đơn hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="orderTrackingForm">
                    <div class="mb-3">
                        <label for="orderNumber" class="form-label">Mã đơn hàng</label>
                        <input type="text" class="form-control" id="orderNumber" 
                               placeholder="Nhập mã đơn hàng (VD: HD20241112001)">
                    </div>
                    <div class="mb-3">
                        <label for="orderPhone" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="orderPhone" 
                               placeholder="Nhập số điện thoại đặt hàng">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tra cứu đơn hàng
                    </button>
                </form>
                <div id="orderTrackingResult" class="mt-3" style="display: none;">
                    <!-- Order tracking result will be shown here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-lock"></i> Yêu cầu đăng nhập
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--primary);"></i>
                </div>
                <h5>Bạn cần đăng nhập để thực hiện chức năng này</h5>
                <p class="text-muted">Đăng nhập để có thể mua hàng, lưu sản phẩm yêu thích và theo dõi đơn hàng.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="auth/login.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                </a>
                <a href="auth/register.php" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus"></i> Đăng ký
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi bi-check-circle-fill text-success me-2"></i>
            <strong class="me-auto">Thành công</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="successToastBody">
            <!-- Success message will be inserted here -->
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="errorToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
            <strong class="me-auto">Lỗi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="errorToastBody">
            <!-- Error message will be inserted here -->
        </div>
    </div>
</div>

<style>
.modal-content {
    border-radius: var(--radius-lg);
    border: 2px solid var(--border);
}

.modal-header {
    background: var(--light);
    border-bottom: 2px solid var(--border);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.modal-title {
    font-weight: 700;
    color: var(--dark);
}

.modal-body {
    padding: 30px;
}

.modal-footer {
    background: var(--light);
    border-top: 2px solid var(--border);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

/* Product Modal Specific Styles */
.product-modal-image {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: var(--radius-md);
}

.product-modal-info h3 {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
}

.product-modal-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--danger);
    margin-bottom: 20px;
}

.product-modal-description {
    color: var(--secondary);
    line-height: 1.6;
    margin-bottom: 20px;
}

.product-modal-specs {
    background: var(--light);
    padding: 20px;
    border-radius: var(--radius-md);
    margin-bottom: 20px;
}

.product-modal-specs h6 {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-light);
}

.spec-item:last-child {
    border-bottom: none;
}

.spec-label {
    font-weight: 600;
    color: var(--secondary);
}

.spec-value {
    color: var(--dark);
    font-weight: 500;
}

/* Cart Modal Styles */
.cart-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-light);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--radius-md);
    margin-right: 15px;
}

.cart-item-info {
    flex-grow: 1;
}

.cart-item-name {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 5px;
}

.cart-item-price {
    color: var(--danger);
    font-weight: 700;
}

.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-control {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
}

.quantity-control button {
    border: none;
    background: none;
    padding: 5px 10px;
    cursor: pointer;
}

.quantity-control input {
    border: none;
    width: 50px;
    text-align: center;
    padding: 5px;
}

/* Toast Styles */
.toast {
    border-radius: var(--radius-md);
    border: 2px solid var(--border);
}

.toast-header {
    background: var(--light);
    border-bottom: 1px solid var(--border-light);
}

/* Responsive */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .cart-item-image {
        width: 60px;
        height: 60px;
        margin-right: 0;
    }
}
</style>