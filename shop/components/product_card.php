<?php
// Product Card Component
function renderProductCard($product) {
    $product_id = $product['id'];
    $is_in_stock = $product['quantity'] > 0;
    $is_low_stock = $product['quantity'] <= $product['min_quantity'] && $product['quantity'] > 0;
    
    // Calculate discount if any
    $original_price = $product['price'];
    $discount_percent = 0;
    if (isset($product['discount_price']) && $product['discount_price'] < $original_price) {
        $discount_percent = round((($original_price - $product['discount_price']) / $original_price) * 100);
    }
    ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="product-card h-100" data-product-id="<?php echo $product_id; ?>">
            <!-- Product Image -->
            <div class="product-image-container position-relative">
                <?php if (!empty($product['image'])): ?>
                    <img src="assets/uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image"
                         onclick="showProductModal(<?php echo $product_id; ?>)">
                <?php else: ?>
                    <div class="product-image no-image" onclick="showProductModal(<?php echo $product_id; ?>)">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Product Badges -->
                <div class="product-badges">
                    <?php if ($discount_percent > 0): ?>
                        <span class="badge bg-danger">-<?php echo $discount_percent; ?>%</span>
                    <?php endif; ?>
                    
                    <?php if (!$is_in_stock): ?>
                        <span class="badge bg-secondary">Hết hàng</span>
                    <?php elseif ($is_low_stock): ?>
                        <span class="badge bg-warning">Sắp hết</span>
                    <?php endif; ?>
                    
                    <?php if (isset($product['is_new']) && $product['is_new']): ?>
                        <span class="badge bg-success">Mới</span>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="product-quick-actions">
                    <button class="btn btn-sm btn-light rounded-circle" 
                            onclick="showProductModal(<?php echo $product_id; ?>)" 
                            title="Xem nhanh">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <?php if (!empty($product['category_name'])): ?>
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                
                <h5 class="product-title" onclick="showProductModal(<?php echo $product_id; ?>)">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h5>
                
                <!-- Product Rating -->
                <div class="product-rating mb-2">
                    <?php 
                    $rating = isset($product['rating']) ? $product['rating'] : 4.5;
                    $full_stars = floor($rating);
                    $half_star = ($rating - $full_stars) >= 0.5;
                    ?>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $full_stars): ?>
                                <i class="bi bi-star-fill text-warning"></i>
                            <?php elseif ($i == $full_stars + 1 && $half_star): ?>
                                <i class="bi bi-star-half text-warning"></i>
                            <?php else: ?>
                                <i class="bi bi-star text-muted"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="rating-text">(<?php echo number_format($rating, 1); ?>)</span>
                    </div>
                </div>
                
                <!-- Product Price -->
                <div class="product-price-container">
                    <?php if ($discount_percent > 0): ?>
                        <div class="product-price-original">
                            <?php echo number_format($original_price, 0, ',', '.'); ?>₫
                        </div>
                        <div class="product-price">
                            <?php echo number_format($product['discount_price'], 0, ',', '.'); ?>₫
                        </div>
                    <?php else: ?>
                        <div class="product-price">
                            <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Stock Info -->
                <?php if ($is_in_stock): ?>
                    <div class="stock-info text-success small mb-2">
                        <i class="bi bi-check-circle"></i> Còn <?php echo $product['quantity']; ?> sản phẩm
                    </div>
                <?php else: ?>
                    <div class="stock-info text-danger small mb-2">
                        <i class="bi bi-x-circle"></i> Tạm hết hàng
                    </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="product-actions">
                    <?php if ($is_in_stock): ?>
                        <button class="btn btn-primary flex-fill me-2" onclick="addToCart(<?php echo $product_id; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <button class="btn btn-outline-primary" onclick="buyNow(<?php echo $product_id; ?>)" title="Mua ngay">
                            <i class="bi bi-lightning"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary flex-fill" disabled>
                            <i class="bi bi-x-circle"></i> Hết hàng
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<style>
.product-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 2px solid var(--border);
    transition: all 0.3s ease;
    overflow: hidden;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    background: var(--light);
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

.product-badges .badge {
    display: block;
    margin-bottom: 5px;
    font-size: 0.7rem;
    font-weight: 700;
}

.product-quick-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    opacity: 0;
    transform: translateX(10px);
    transition: all 0.3s ease;
}

.product-card:hover .product-quick-actions {
    opacity: 1;
    transform: translateX(0);
}

.product-quick-actions .btn {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.product-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 250px);
}

.product-category {
    background: var(--primary-light);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: var(--radius-xl);
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-block;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 12px;
    line-height: 1.4;
    flex-grow: 1;
    cursor: pointer;
}

.product-title:hover {
    color: var(--primary);
}

.product-rating .stars {
    display: flex;
    align-items: center;
    gap: 2px;
}

.rating-text {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-left: 8px;
}

.product-price-container {
    margin-bottom: 12px;
}

.product-price {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--danger);
    letter-spacing: -0.5px;
}

.product-price-original {
    font-size: 1rem;
    color: var(--text-muted);
    text-decoration: line-through;
    margin-bottom: 4px;
}

.stock-info {
    font-weight: 600;
}

.product-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.no-image {
    background: linear-gradient(135deg, var(--light), var(--border-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 3rem;
    height: 250px;
}

/* Responsive */
@media (max-width: 768px) {
    .product-quick-actions {
        opacity: 1;
        transform: translateX(0);
    }
    
    .product-image {
        height: 200px;
    }
    
    .product-info {
        height: calc(100% - 200px);
    }
}
</style>