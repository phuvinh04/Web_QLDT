// Shop JavaScript Functions
class ShopManager {
    constructor() {
        this.cart = {}; // We'll use database instead of localStorage
        this.init();
    }

    init() {
        this.loadCartCount();
        this.bindEvents();
    }

    async loadCartCount() {
        if (this.isLoggedIn()) {
            try {
                const response = await fetch('api/get_cart_count.php');
                const data = await response.json();
                this.updateCartBadgeFromServer(data.cart_count);
            } catch (error) {
                console.error('Error loading cart count:', error);
            }
        }
    }

    bindEvents() {
        // Order tracking form
        const orderTrackingForm = document.getElementById('orderTrackingForm');
        if (orderTrackingForm) {
            orderTrackingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.trackOrder();
            });
        }

        // View toggle
        const viewButtons = document.querySelectorAll('input[name="view"]');
        viewButtons.forEach(btn => {
            btn.addEventListener('change', (e) => {
                this.toggleView(e.target.value);
            });
        });
    }

    // Cart Management
    async addToCart(productId, quantity = 1) {
        // Find the button that was clicked
        const button = document.querySelector(`[onclick="addToCart(${productId})"]`);
        const productCard = button?.closest('.product-card');
        
        try {
            // Show loading state
            if (button) {
                button.classList.add('loading');
                button.disabled = true;
            }
            if (productCard) {
                productCard.classList.add('loading');
            }

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            const response = await fetch('api/add_to_cart.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', data.message);
                this.updateCartBadgeFromServer(data.cart_count);
                
                // Animate cart badge
                const badges = document.querySelectorAll('.nav-link .badge');
                badges.forEach(badge => {
                    badge.classList.add('animate');
                    setTimeout(() => badge.classList.remove('animate'), 500);
                });
            } else {
                if (data.redirect) {
                    // User not logged in
                    this.showLoginRequired();
                } else {
                    this.showToast('error', data.message);
                }
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showToast('error', 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng');
        } finally {
            // Remove loading state
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
            if (productCard) {
                productCard.classList.remove('loading');
            }
        }
    }

    removeFromCart(productId) {
        delete this.cart[productId];
        this.saveCart();
        this.updateCartBadge();
        this.loadCartModal();
    }

    updateCartQuantity(productId, quantity) {
        if (quantity <= 0) {
            this.removeFromCart(productId);
        } else {
            this.cart[productId] = quantity;
            this.saveCart();
            this.updateCartBadge();
        }
    }

    saveCart() {
        localStorage.setItem('shop_cart', JSON.stringify(this.cart));
    }

    updateCartBadge() {
        const totalItems = Object.values(this.cart).reduce((sum, qty) => sum + qty, 0);
        this.updateCartBadgeFromServer(totalItems);
    }

    updateCartBadgeFromServer(count) {
        const badges = document.querySelectorAll('.nav-link .badge');
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
    }



    // Modal Management
    async showProductModal(productId) {
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        const modalBody = document.getElementById('productModalBody');
        
        // Show loading
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        `;
        
        modal.show();

        try {
            const response = await fetch(`api/product_detail.php?id=${productId}`);
            const data = await response.json();
            
            if (data.success) {
                modalBody.innerHTML = this.renderProductModalContent(data.product);
            } else {
                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Không thể tải thông tin sản phẩm</h5>
                    </div>
                `;
            }
        } catch (error) {
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-wifi-off text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Lỗi kết nối</h5>
                </div>
            `;
        }
    }

    renderProductModalContent(product) {
        return `
            <div class="row">
                <div class="col-md-6">
                    ${product.image ? 
                        `<img src="assets/uploads/${product.image}" alt="${product.name}" class="product-modal-image">` :
                        `<div class="product-modal-image no-image d-flex align-items-center justify-content-center">
                            <i class="bi bi-phone" style="font-size: 4rem; color: var(--text-muted);"></i>
                        </div>`
                    }
                </div>
                <div class="col-md-6">
                    <div class="product-modal-info">
                        <span class="badge bg-primary mb-2">${product.category_name || 'Điện thoại'}</span>
                        <h3>${product.name}</h3>
                        <div class="product-modal-price">${this.formatPrice(product.price)}₫</div>
                        
                        ${product.description ? 
                            `<div class="product-modal-description">${product.description}</div>` : ''
                        }
                        
                        <div class="product-modal-specs">
                            <h6>Thông số kỹ thuật</h6>
                            <div class="spec-item">
                                <span class="spec-label">Mã sản phẩm:</span>
                                <span class="spec-value">${product.sku}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Tình trạng:</span>
                                <span class="spec-value ${product.quantity > 0 ? 'text-success' : 'text-danger'}">
                                    ${product.quantity > 0 ? `Còn ${product.quantity} sản phẩm` : 'Hết hàng'}
                                </span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Danh mục:</span>
                                <span class="spec-value">${product.category_name || 'Điện thoại'}</span>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            ${product.quantity > 0 ? 
                                `<button class="btn btn-primary flex-fill" onclick="shopManager.addToCart(${product.id})">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </button>
                                <button class="btn btn-success" onclick="shopManager.buyNow(${product.id})">
                                    <i class="bi bi-lightning"></i> Mua ngay
                                </button>` :
                                `<button class="btn btn-secondary flex-fill" disabled>
                                    <i class="bi bi-x-circle"></i> Hết hàng
                                </button>`
                            }
                        </div>
                        

                    </div>
                </div>
            </div>
        `;
    }

    async loadCartModal() {
        const modal = new bootstrap.Modal(document.getElementById('cartModal'));
        const modalBody = document.getElementById('cartModalBody');
        
        if (Object.keys(this.cart).length === 0) {
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: var(--text-muted);"></i>
                    <h5 class="mt-3">Giỏ hàng trống</h5>
                    <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                </div>
            `;
        } else {
            // Load cart items via AJAX
            try {
                const productIds = Object.keys(this.cart).join(',');
                const response = await fetch(`api/cart_items.php?ids=${productIds}`);
                const data = await response.json();
                
                if (data.success) {
                    modalBody.innerHTML = this.renderCartContent(data.products);
                }
            } catch (error) {
                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Không thể tải giỏ hàng</h5>
                    </div>
                `;
            }
        }
        
        modal.show();
    }

    renderCartContent(products) {
        let html = '';
        let total = 0;
        
        products.forEach(product => {
            const quantity = this.cart[product.id];
            const subtotal = product.price * quantity;
            total += subtotal;
            
            html += `
                <div class="cart-item">
                    <img src="${product.image ? `assets/uploads/${product.image}` : 'assets/images/no-image.png'}" 
                         alt="${product.name}" class="cart-item-image">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${product.name}</div>
                        <div class="cart-item-price">${this.formatPrice(product.price)}₫</div>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-control">
                            <button onclick="shopManager.updateCartQuantity(${product.id}, ${quantity - 1})">-</button>
                            <input type="number" value="${quantity}" min="1" 
                                   onchange="shopManager.updateCartQuantity(${product.id}, this.value)">
                            <button onclick="shopManager.updateCartQuantity(${product.id}, ${quantity + 1})">+</button>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="shopManager.removeFromCart(${product.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += `
            <div class="cart-total mt-4 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Tổng cộng:</h5>
                    <h4 class="text-danger">${this.formatPrice(total)}₫</h4>
                </div>
            </div>
        `;
        
        return html;
    }

    // Utility Functions
    buyNow(productId) {
        this.addToCart(productId);
        setTimeout(() => {
            this.proceedToCheckout();
        }, 500);
    }

    proceedToCheckout() {
        if (!this.isLoggedIn()) {
            this.showLoginRequired();
            return;
        }
        
        // Redirect to checkout page (to be implemented)
        this.showToast('info', 'Chức năng thanh toán đang được phát triển');
    }

    async trackOrder() {
        const orderNumber = document.getElementById('orderNumber').value;
        const orderPhone = document.getElementById('orderPhone').value;
        const resultDiv = document.getElementById('orderTrackingResult');
        
        if (!orderNumber || !orderPhone) {
            this.showToast('error', 'Vui lòng nhập đầy đủ thông tin');
            return;
        }
        
        // Show loading
        resultDiv.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tra cứu...</span>
                </div>
            </div>
        `;
        resultDiv.style.display = 'block';
        
        // Simulate API call
        setTimeout(() => {
            resultDiv.innerHTML = `
                <div class="alert alert-info">
                    <h6>Đơn hàng: ${orderNumber}</h6>
                    <p class="mb-0">Chức năng tra cứu đơn hàng đang được phát triển. Vui lòng liên hệ hotline để được hỗ trợ.</p>
                </div>
            `;
        }, 1500);
    }

    toggleView(viewType) {
        const productsContainer = document.querySelector('.products-container');
        if (productsContainer) {
            productsContainer.className = `products-container view-${viewType}`;
        }
    }

    isLoggedIn() {
        // Check if user is logged in via PHP session
        return typeof window.userLoggedIn !== 'undefined' && window.userLoggedIn === true;
    }

    showLoginRequired() {
        const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        modal.show();
    }

    showToast(type, message) {
        const toastId = type === 'success' ? 'successToast' : 'errorToast';
        const bodyId = type === 'success' ? 'successToastBody' : 'errorToastBody';
        
        document.getElementById(bodyId).textContent = message;
        
        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
}

// Global functions for onclick handlers
let shopManager;

function addToCart(productId, quantity = 1) {
    shopManager.addToCart(productId, quantity);
}



function showProductModal(productId) {
    shopManager.showProductModal(productId);
}

function showCartModal() {
    shopManager.loadCartModal();
}



function showOrderTrackingModal() {
    const modal = new bootstrap.Modal(document.getElementById('orderTrackingModal'));
    modal.show();
}

function buyNow(productId) {
    shopManager.buyNow(productId);
}

function proceedToCheckout() {
    shopManager.proceedToCheckout();
}

// Initialize shop manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    shopManager = new ShopManager();
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Legacy function for backward compatibility
function showLoginPrompt() {
    shopManager.showLoginRequired();
}