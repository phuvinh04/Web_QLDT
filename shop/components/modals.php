<?php
// Modals Component - Flat Design
?>

<!-- Product Quick View Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye"></i> Chi tiết sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="productModalBody">
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
                <h5 class="modal-title"><i class="bi bi-cart3"></i> Giỏ hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cartModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tiếp tục mua</button>
                <button type="button" class="btn btn-primary" onclick="proceedToCheckout()">
                    <i class="bi bi-credit-card"></i> Thanh toán
                </button>
            </div>
        </div>
    </div>
</div>




<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-lock"></i> Yêu cầu đăng nhập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--primary);"></i>
                </div>
                <h5>Vui lòng đăng nhập</h5>
                <p class="text-muted">Đăng nhập để mua hàng và theo dõi đơn hàng.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="../auth/login.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                </a>
                <a href="../auth/register.php" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus"></i> Đăng ký
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div class="toast-container">
    <div id="successToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi bi-check-circle-fill text-success me-2"></i>
            <strong class="me-auto">Thành công</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="successToastBody"></div>
    </div>
    
    <div id="errorToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
            <strong class="me-auto">Lỗi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="errorToastBody"></div>
    </div>
</div>
