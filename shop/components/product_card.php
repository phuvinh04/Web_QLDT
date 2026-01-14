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
                <a href="product_detail.php?id=<?php echo $product_id; ?>">
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo $image_base; ?>assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="no-image" style="display: none;">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php else: ?>
                    <div class="no-image">
                        <i class="bi bi-phone"></i>
                    </div>
                <?php endif; ?>
                </a>
                
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
                    <a href="product_detail.php?id=<?php echo $product_id; ?>" class="btn" title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>

            <div class="product-info">
                <?php if (!empty($product['category_name'])): ?>
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                
                <h5 class="product-title">
                    <a href="product_detail.php?id=<?php echo $product_id; ?>" style="color: inherit; text-decoration: none;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                </h5>
                
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
