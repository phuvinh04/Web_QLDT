// Shop JavaScript Functions - LocalStorage Cart
class ShopManager {
  constructor() {
    this.cart = this.loadCart();
    this.init();
  }

  init() {
    this.updateCartBadge();
    this.bindEvents();
  }

  // Load cart from localStorage
  loadCart() {
    try {
      const cart = localStorage.getItem("shop_cart");
      return cart ? JSON.parse(cart) : {};
    } catch (e) {
      return {};
    }
  }

  // Save cart to localStorage
  saveCart() {
    localStorage.setItem("shop_cart", JSON.stringify(this.cart));
    this.updateCartBadge();
  }

  bindEvents() {
    const orderTrackingForm = document.getElementById("orderTrackingForm");
    if (orderTrackingForm) {
      orderTrackingForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.trackOrder();
      });
    }
  }

  // Add to cart using localStorage
  addToCart(productId, quantity = 1) {
    const button = document.querySelector(
      `[onclick*="addToCart(${productId}"]`
    );

    if (button) {
      button.classList.add("loading");
      button.disabled = true;
    }

    // Add to cart
    if (this.cart[productId]) {
      this.cart[productId] += quantity;
    } else {
      this.cart[productId] = quantity;
    }

    this.saveCart();
    this.showToast("success", "Đã thêm sản phẩm vào giỏ hàng!");

    // Animate cart badge
    const badges = document.querySelectorAll(".nav-link .badge");
    badges.forEach((badge) => {
      badge.classList.add("animate");
      setTimeout(() => badge.classList.remove("animate"), 500);
    });

    if (button) {
      setTimeout(() => {
        button.classList.remove("loading");
        button.disabled = false;
      }, 300);
    }
  }

  removeFromCart(productId) {
    delete this.cart[productId];
    this.saveCart();
    this.renderCartPage();
  }

  updateCartQuantity(productId, quantity) {
    quantity = parseInt(quantity);
    if (quantity <= 0) {
      this.removeFromCart(productId);
    } else {
      this.cart[productId] = quantity;
      this.saveCart();
      this.renderCartPage();
    }
  }

  updateCartBadge() {
    const totalItems = Object.values(this.cart).reduce(
      (sum, qty) => sum + parseInt(qty),
      0
    );
    const badges = document.querySelectorAll(".nav-link .badge, .cart-badge");
    badges.forEach((badge) => {
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? "inline" : "none";
    });
  }

  getCartCount() {
    return Object.values(this.cart).reduce(
      (sum, qty) => sum + parseInt(qty),
      0
    );
  }

  getCartItems() {
    return this.cart;
  }

  clearCart() {
    this.cart = {};
    this.saveCart();
  }

  // Render cart page content
  async renderCartPage() {
    const cartContainer = document.getElementById("cartContainer");
    if (!cartContainer) return;

    const productIds = Object.keys(this.cart).filter((id) => this.cart[id] > 0);

    if (productIds.length === 0) {
      cartContainer.innerHTML = `
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #6c757d;"></i>
            <h4 class="mt-3">Giỏ hàng trống</h4>
            <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="products.php" class="btn btn-primary mt-3">
              <i class="bi bi-grid me-2"></i>Xem sản phẩm
            </a>
          </div>
        </div>
      `;
      this.updateCartSummary(0);

      // Update cart count
      const cartCountEl = document.getElementById("cartCount");
      if (cartCountEl) cartCountEl.textContent = "0 sản phẩm trong giỏ hàng";

      // Disable checkout button
      const checkoutBtn = document.getElementById("checkoutBtn");
      if (checkoutBtn) {
        checkoutBtn.style.pointerEvents = "none";
        checkoutBtn.style.opacity = "0.5";
      }
      return;
    }

    // Fetch product details
    try {
      const response = await fetch(
        `api/cart_items.php?ids=${productIds.join(",")}`
      );
      const text = await response.text();

      // Try to parse JSON
      let data;
      try {
        data = JSON.parse(text);
      } catch (parseError) {
        console.error("Invalid JSON response:", text);
        cartContainer.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Lỗi tải dữ liệu giỏ hàng</div>`;
        return;
      }

      if (data.success) {
        this.renderCartItems(data.products);
      } else {
        cartContainer.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>${
          data.message || "Không thể tải giỏ hàng"
        }</div>`;
      }
    } catch (error) {
      console.error("Error loading cart:", error);
      cartContainer.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Lỗi kết nối server</div>`;
    }
  }

  renderCartItems(products) {
    const cartContainer = document.getElementById("cartContainer");
    if (!cartContainer) return;

    let html = "";
    let total = 0;
    let itemCount = 0;

    products.forEach((product) => {
      const quantity = this.cart[product.id] || 0;
      if (quantity <= 0) return;

      const subtotal = product.price * quantity;
      total += subtotal;
      itemCount += quantity;

      html += `
        <div class="card mb-3" data-product-id="${product.id}">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2 col-3">
                ${
                  product.image
                    ? `<img src="../assets/images/products/${product.image}" alt="${product.name}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;" onerror="this.outerHTML='<div class=\\'no-image rounded\\' style=\\'height:100px;\\'><i class=\\'bi bi-phone\\'></i></div>'">`
                    : `<div class="no-image rounded" style="height:100px;"><i class="bi bi-phone"></i></div>`
                }
              </div>
              <div class="col-md-4 col-9">
                <h6 class="mb-1">${product.name}</h6>
                <small class="text-muted">${product.category_name || ""}</small>
                <div class="text-primary fw-bold d-md-none mt-1">${this.formatPrice(
                  product.price
                )}₫</div>
              </div>
              <div class="col-md-2 d-none d-md-block text-center">
                <span class="text-primary fw-bold">${this.formatPrice(
                  product.price
                )}₫</span>
              </div>
              <div class="col-md-2 col-6 mt-2 mt-md-0">
                <div class="input-group input-group-sm">
                  <button class="btn btn-outline-secondary" type="button" onclick="shopManager.updateCartQuantity(${
                    product.id
                  }, ${quantity - 1})">−</button>
                  <input type="number" class="form-control text-center" value="${quantity}" min="1" max="${
        product.quantity || 99
      }"
                         onchange="shopManager.updateCartQuantity(${
                           product.id
                         }, this.value)">
                  <button class="btn btn-outline-secondary" type="button" onclick="shopManager.updateCartQuantity(${
                    product.id
                  }, ${quantity + 1})">+</button>
                </div>
                <small class="text-muted">Còn ${product.quantity || 0}</small>
              </div>
              <div class="col-md-2 col-6 mt-2 mt-md-0 text-end">
                <div class="fw-bold text-success">${this.formatPrice(
                  subtotal
                )}₫</div>
                <button class="btn btn-sm btn-outline-danger mt-1" onclick="shopManager.removeFromCart(${
                  product.id
                })">
                  <i class="bi bi-trash"></i> Xóa
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    });

    cartContainer.innerHTML = html;
    this.updateCartSummary(total);

    // Update cart count text
    const cartCountEl = document.getElementById("cartCount");
    if (cartCountEl) {
      cartCountEl.textContent = `${itemCount} sản phẩm trong giỏ hàng`;
    }

    // Show/hide checkout button based on cart
    const checkoutBtn = document.getElementById("checkoutBtn");
    if (checkoutBtn) {
      checkoutBtn.style.pointerEvents = itemCount > 0 ? "auto" : "none";
      checkoutBtn.style.opacity = itemCount > 0 ? "1" : "0.5";
    }

    // Free shipping notice
    const freeShippingNotice = document.getElementById("freeShippingNotice");
    if (freeShippingNotice) {
      freeShippingNotice.style.display =
        total > 0 && total < 500000 ? "block" : "none";
      if (total > 0 && total < 500000) {
        freeShippingNotice.innerHTML = `<small class="text-info"><i class="bi bi-info-circle me-1"></i>Mua thêm ${this.formatPrice(
          500000 - total
        )}₫ để được miễn phí vận chuyển</small>`;
      }
    }
  }

  updateCartSummary(total) {
    const subtotalEl = document.getElementById("cartSubtotal");
    const totalEl = document.getElementById("cartTotal");

    if (subtotalEl) subtotalEl.textContent = this.formatPrice(total) + "₫";
    if (totalEl) totalEl.textContent = this.formatPrice(total) + "₫";
  }

  // Buy now
  buyNow(productId, quantity = 1) {
    this.addToCart(productId, quantity);
    setTimeout(() => {
      window.location.href = "cart.php";
    }, 500);
  }

  proceedToCheckout() {
    if (this.getCartCount() === 0) {
      this.showToast("error", "Giỏ hàng trống!");
      return;
    }
    window.location.href = "checkout.php";
  }

  async trackOrder() {
    const orderNumber = document.getElementById("orderNumber")?.value;
    const orderPhone = document.getElementById("orderPhone")?.value;
    const resultDiv = document.getElementById("orderTrackingResult");

    if (!orderNumber || !orderPhone) {
      this.showToast("error", "Vui lòng nhập đầy đủ thông tin");
      return;
    }

    if (resultDiv) {
      resultDiv.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      `;
      resultDiv.style.display = "block";

      setTimeout(() => {
        resultDiv.innerHTML = `
          <div class="alert alert-info">
            <h6>Đơn hàng: ${orderNumber}</h6>
            <p class="mb-0">Chức năng tra cứu đang phát triển. Vui lòng liên hệ hotline.</p>
          </div>
        `;
      }, 1000);
    }
  }

  showToast(type, message) {
    // Try to use existing toast elements
    const toastId = type === "success" ? "successToast" : "errorToast";
    const bodyId = type === "success" ? "successToastBody" : "errorToastBody";

    const toastEl = document.getElementById(toastId);
    const bodyEl = document.getElementById(bodyId);

    if (toastEl && bodyEl) {
      bodyEl.textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    } else {
      // Fallback: create simple toast
      const toast = document.createElement("div");
      toast.className = `alert alert-${
        type === "success" ? "success" : "danger"
      } position-fixed`;
      toast.style.cssText =
        "top: 80px; right: 20px; z-index: 9999; min-width: 250px;";
      toast.innerHTML = `<i class="bi bi-${
        type === "success" ? "check-circle" : "exclamation-circle"
      } me-2"></i>${message}`;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
  }

  formatPrice(price) {
    return new Intl.NumberFormat("vi-VN").format(price);
  }
}

// Global instance
let shopManager;

// Global functions
function addToCart(productId, quantity = 1) {
  shopManager.addToCart(productId, quantity);
}

function buyNow(productId, quantity = 1) {
  shopManager.buyNow(productId, quantity);
}

function proceedToCheckout() {
  shopManager.proceedToCheckout();
}

// Initialize
document.addEventListener("DOMContentLoaded", function () {
  shopManager = new ShopManager();

  // Render cart page if on cart.php
  if (document.getElementById("cartContainer")) {
    shopManager.renderCartPage();
  }
});
