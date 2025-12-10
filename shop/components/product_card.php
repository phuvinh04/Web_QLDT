<?php
// Product Card Component - Flat Design
function renderProductCard($product) {
    $product_id = $product['id'];
    $is_in_stock = $product['quantity'] > 0;
    $is_low_stock = $product['quantity'] <= $product['min_quantity'] && $product['quantity'] > 0;
    
    $original_price = $product['price'];
    $discount_percent = 0;
    if (isset($product['discount_price']) && $product['discount_price'] < $original_price) {
        $discount_percent = round((($original_price - $product['discount_price']) / $original_price) * 100);
    }
    ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="product-card" data-product-id="<?php echo $product_id; ?>">
            <div class="product-image-container">
                <?php 
                // Determine base path for images
                $image_base = isset($GLOBALS['base_url']) ? $GLOBALS['base_url'] : '../';
                ?>
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo $image_base; ?>assets/uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image"
                         onclick="showProductModal(<?php echo $product_id; ?>)"
                         loading="lazy">
                <?php else: ?>
                    <div class="no-image" onclick="showProductModal(<?php echo $product_id; ?>)">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php endif; ?>
                
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
                
                <div class="product-quick-actions">
                    <button class="btn" onclick="showProductModal(<?php echo $product_id; ?>)" title="Xem nhanh">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="product-info">
                <?php if (!empty($product['category_name'])): ?>
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                
                <h5 class="product-title" onclick="showProductModal(<?php echo $product_id; ?>)">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h5>
                
                <div class="product-rating">
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
                
                <div class="stock-info <?php echo $is_in_stock ? 'text-success' : 'text-danger'; ?>">
                    <?php if ($is_in_stock): ?>
                        <i class="bi bi-check-circle"></i> Còn <?php echo $product['quantity']; ?> sản phẩm
                    <?php else: ?>
                        <i class="bi bi-x-circle"></i> Tạm hết hàng
                    <?php endif; ?>
                </div>
                
                <div class="product-actions">
                    <?php if ($is_in_stock): ?>
                        <button class="btn btn-primary flex-fill" onclick="addToCart(<?php echo $product_id; ?>)">
                            <i class="bi bi-cart-plus"></i> Thêm giỏ
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
