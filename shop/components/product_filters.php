<?php
// Product Filters Component - Flat Design
function renderProductFilters($categories, $current_filters = [], $brands = []) {
    $category_filter = $current_filters['category'] ?? 0;
    $brand_filter = $current_filters['brand'] ?? 0;
    $price_filter = $current_filters['price'] ?? '';
    $sort_filter = $current_filters['sort'] ?? '';
    $search_filter = $current_filters['search'] ?? '';
    ?>
    
    <div class="filters-section">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-grid"></i> Danh mục
                    </h6>
                    <div class="filter-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" value="0" 
                                   id="cat_all" <?php echo $category_filter == 0 ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="cat_all">Tất cả</label>
                        </div>
                        <?php foreach ($categories as $category): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" 
                                       value="<?php echo $category['id']; ?>" 
                                       id="cat_<?php echo $category['id']; ?>"
                                       <?php echo $category_filter == $category['id'] ? 'checked' : ''; ?>
                                       onchange="applyFilters()">
                                <label class="form-check-label" for="cat_<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-building"></i> Thương hiệu
                    </h6>
                    <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="brand" value="0" 
                                   id="brand_all" <?php echo $brand_filter == 0 ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="brand_all">Tất cả</label>
                        </div>
                        <?php foreach ($brands as $brand): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="brand" 
                                       value="<?php echo $brand['id']; ?>" 
                                       id="brand_<?php echo $brand['id']; ?>"
                                       <?php echo $brand_filter == $brand['id'] ? 'checked' : ''; ?>
                                       onchange="applyFilters()">
                                <label class="form-check-label" for="brand_<?php echo $brand['id']; ?>">
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-currency-dollar"></i> Khoảng giá
                    </h6>
                    <div class="filter-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="" 
                                   id="price_all" <?php echo $price_filter == '' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_all">Tất cả</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="0-5000000" 
                                   id="price_1" <?php echo $price_filter == '0-5000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_1">Dưới 5 triệu</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="5000000-10000000" 
                                   id="price_2" <?php echo $price_filter == '5000000-10000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_2">5 - 10 triệu</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="10000000-20000000" 
                                   id="price_3" <?php echo $price_filter == '10000000-20000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_3">10 - 20 triệu</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="20000000-999999999" 
                                   id="price_4" <?php echo $price_filter == '20000000-999999999' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_4">Trên 20 triệu</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-sort-down"></i> Sắp xếp
                    </h6>
                    <select class="form-select mb-2" name="sort" onchange="applyFilters()">
                        <option value="" <?php echo $sort_filter == '' ? 'selected' : ''; ?>>Mặc định</option>
                        <option value="name_asc" <?php echo $sort_filter == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                        <option value="name_desc" <?php echo $sort_filter == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                        <option value="price_asc" <?php echo $sort_filter == 'price_asc' ? 'selected' : ''; ?>>Giá thấp → cao</option>
                        <option value="price_desc" <?php echo $sort_filter == 'price_desc' ? 'selected' : ''; ?>>Giá cao → thấp</option>
                        <option value="newest" <?php echo $sort_filter == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    </select>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="view" id="view_grid" value="grid" checked>
                        <label class="btn btn-outline-primary btn-sm" for="view_grid">
                            <i class="bi bi-grid"></i>
                        </label>
                        <input type="radio" class="btn-check" name="view" id="view_list" value="list">
                        <label class="btn btn-outline-primary btn-sm" for="view_list">
                            <i class="bi bi-list"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="active-filters" id="activeFilters" style="display: none;"></div>
        
        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllFilters()">
                <i class="bi bi-x-circle"></i> Xóa bộ lọc
            </button>
        </div>
    </div>
    
    <form id="filterForm" method="GET" action="products.php" style="display: none;">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_filter); ?>">
    </form>
    
    <script>
    function applyFilters() {
        const formData = new FormData();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && searchInput.value) formData.append('search', searchInput.value);
        
        const categoryInput = document.querySelector('input[name="category"]:checked');
        if (categoryInput && categoryInput.value !== '0') formData.append('category', categoryInput.value);
        
        const brandInput = document.querySelector('input[name="brand"]:checked');
        if (brandInput && brandInput.value !== '0') formData.append('brand', brandInput.value);
        
        const priceInput = document.querySelector('input[name="price"]:checked');
        if (priceInput && priceInput.value) formData.append('price', priceInput.value);
        
        const sortSelect = document.querySelector('select[name="sort"]');
        if (sortSelect && sortSelect.value) formData.append('sort', sortSelect.value);
        
        const params = new URLSearchParams(formData);
        window.location.href = 'products.php' + (params.toString() ? '?' + params.toString() : '');
    }
    
    function clearAllFilters() {
        window.location.href = 'products.php';
    }
    
    document.querySelectorAll('input[name="view"]').forEach(input => {
        input.addEventListener('change', function() {
            const container = document.querySelector('.products-container');
            if (container) {
                container.classList.remove('view-grid', 'view-list');
                container.classList.add('view-' + this.value);
            }
        });
    });
    </script>
    <?php
}
?>
