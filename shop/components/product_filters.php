<?php
// Product Filters Component
function renderProductFilters($categories, $current_filters = []) {
    $category_filter = $current_filters['category'] ?? 0;
    $price_filter = $current_filters['price'] ?? '';
    $sort_filter = $current_filters['sort'] ?? '';
    $brand_filter = $current_filters['brand'] ?? '';
    $search_filter = $current_filters['search'] ?? '';
    ?>
    
    <div class="filters-section">
        <div class="row">
            <!-- Category Filter -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-grid"></i> Danh mục
                    </h6>
                    <div class="filter-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" value="0" 
                                   id="cat_all" <?php echo $category_filter == 0 ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="cat_all">
                                Tất cả danh mục
                            </label>
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
            
            <!-- Price Filter -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-currency-dollar"></i> Khoảng giá
                    </h6>
                    <div class="filter-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="" 
                                   id="price_all" <?php echo $price_filter == '' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_all">
                                Tất cả mức giá
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="0-5000000" 
                                   id="price_1" <?php echo $price_filter == '0-5000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_1">
                                Dưới 5 triệu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="5000000-10000000" 
                                   id="price_2" <?php echo $price_filter == '5000000-10000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_2">
                                5 - 10 triệu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="10000000-20000000" 
                                   id="price_3" <?php echo $price_filter == '10000000-20000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_3">
                                10 - 20 triệu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="20000000-50000000" 
                                   id="price_4" <?php echo $price_filter == '20000000-50000000' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_4">
                                20 - 50 triệu
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price" value="50000000-999999999" 
                                   id="price_5" <?php echo $price_filter == '50000000-999999999' ? 'checked' : ''; ?>
                                   onchange="applyFilters()">
                            <label class="form-check-label" for="price_5">
                                Trên 50 triệu
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sort Filter -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-sort-down"></i> Sắp xếp
                    </h6>
                    <select class="form-select" name="sort" onchange="applyFilters()">
                        <option value="" <?php echo $sort_filter == '' ? 'selected' : ''; ?>>Mặc định</option>
                        <option value="name_asc" <?php echo $sort_filter == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                        <option value="name_desc" <?php echo $sort_filter == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                        <option value="price_asc" <?php echo $sort_filter == 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                        <option value="price_desc" <?php echo $sort_filter == 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                        <option value="newest" <?php echo $sort_filter == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="popular" <?php echo $sort_filter == 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
                    </select>
                </div>
            </div>
            
            <!-- View Options -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="filter-card">
                    <h6 class="filter-title">
                        <i class="bi bi-grid-3x3"></i> Hiển thị
                    </h6>
                    <div class="view-options">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="view" id="view_grid" value="grid" checked>
                            <label class="btn btn-outline-primary" for="view_grid">
                                <i class="bi bi-grid"></i>
                            </label>
                            
                            <input type="radio" class="btn-check" name="view" id="view_list" value="list">
                            <label class="btn btn-outline-primary" for="view_list">
                                <i class="bi bi-list"></i>
                            </label>
                        </div>
                        
                        <select class="form-select mt-2" name="per_page" onchange="applyFilters()">
                            <option value="12">12 sản phẩm</option>
                            <option value="24">24 sản phẩm</option>
                            <option value="48">48 sản phẩm</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Filters -->
        <div class="active-filters" id="activeFilters">
            <!-- Will be populated by JavaScript -->
        </div>
        
        <!-- Clear Filters -->
        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">
                <i class="bi bi-x-circle"></i> Xóa tất cả bộ lọc
            </button>
        </div>
    </div>
    
    <!-- Hidden form for filter submission -->
    <form id="filterForm" method="GET" action="products.php" style="display: none;">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_filter); ?>">
        <input type="hidden" name="category" value="">
        <input type="hidden" name="price" value="">
        <input type="hidden" name="sort" value="">
        <input type="hidden" name="per_page" value="">
    </form>
    
    <?php
}
?>

<style>
.filters-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 25px;
    border: 2px solid var(--border);
    margin-bottom: 30px;
}

.filter-card {
    background: var(--light);
    border-radius: var(--radius-md);
    padding: 20px;
    height: 100%;
}

.filter-title {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border);
}

.filter-options {
    max-height: 200px;
    overflow-y: auto;
}

.form-check {
    margin-bottom: 8px;
}

.form-check-label {
    font-weight: 500;
    color: var(--secondary);
    cursor: pointer;
}

.form-check-input:checked + .form-check-label {
    color: var(--primary);
    font-weight: 700;
}

.view-options .btn-group {
    margin-bottom: 10px;
}

.active-filters {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-light);
}

.filter-tag {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 6px 12px;
    border-radius: var(--radius-xl);
    font-size: 0.85rem;
    font-weight: 600;
    margin: 4px;
}

.filter-tag .remove-filter {
    margin-left: 8px;
    cursor: pointer;
    opacity: 0.8;
}

.filter-tag .remove-filter:hover {
    opacity: 1;
}

/* Custom scrollbar for filter options */
.filter-options::-webkit-scrollbar {
    width: 6px;
}

.filter-options::-webkit-scrollbar-track {
    background: var(--border-light);
    border-radius: 3px;
}

.filter-options::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 3px;
}

.filter-options::-webkit-scrollbar-thumb:hover {
    background: var(--secondary);
}
</style>

<script>
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData();
    
    // Get current search value
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && searchInput.value) {
        formData.append('search', searchInput.value);
    }
    
    // Get category filter
    const categoryInput = document.querySelector('input[name="category"]:checked');
    if (categoryInput && categoryInput.value !== '0') {
        formData.append('category', categoryInput.value);
    }
    
    // Get price filter
    const priceInput = document.querySelector('input[name="price"]:checked');
    if (priceInput && priceInput.value) {
        formData.append('price', priceInput.value);
    }
    
    // Get sort filter
    const sortSelect = document.querySelector('select[name="sort"]');
    if (sortSelect && sortSelect.value) {
        formData.append('sort', sortSelect.value);
    }
    
    // Get per_page filter
    const perPageSelect = document.querySelector('select[name="per_page"]');
    if (perPageSelect && perPageSelect.value) {
        formData.append('per_page', perPageSelect.value);
    }
    
    // Build URL
    const params = new URLSearchParams(formData);
    const url = 'products.php' + (params.toString() ? '?' + params.toString() : '');
    
    // Navigate to filtered URL
    window.location.href = url;
}

function clearAllFilters() {
    window.location.href = 'products.php';
}

function updateActiveFilters() {
    const activeFiltersContainer = document.getElementById('activeFilters');
    const filters = [];
    
    // Check category
    const categoryInput = document.querySelector('input[name="category"]:checked');
    if (categoryInput && categoryInput.value !== '0') {
        const label = document.querySelector(`label[for="${categoryInput.id}"]`).textContent;
        filters.push({type: 'category', value: categoryInput.value, label: label});
    }
    
    // Check price
    const priceInput = document.querySelector('input[name="price"]:checked');
    if (priceInput && priceInput.value) {
        const label = document.querySelector(`label[for="${priceInput.id}"]`).textContent;
        filters.push({type: 'price', value: priceInput.value, label: label});
    }
    
    // Check sort
    const sortSelect = document.querySelector('select[name="sort"]');
    if (sortSelect && sortSelect.value) {
        const label = sortSelect.options[sortSelect.selectedIndex].text;
        filters.push({type: 'sort', value: sortSelect.value, label: label});
    }
    
    // Render active filters
    if (filters.length > 0) {
        let html = '<h6 class="mb-2">Bộ lọc đang áp dụng:</h6>';
        filters.forEach(filter => {
            html += `<span class="filter-tag">
                ${filter.label}
                <span class="remove-filter" onclick="removeFilter('${filter.type}', '${filter.value}')">×</span>
            </span>`;
        });
        activeFiltersContainer.innerHTML = html;
        activeFiltersContainer.style.display = 'block';
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

function removeFilter(type, value) {
    if (type === 'category') {
        document.getElementById('cat_all').checked = true;
    } else if (type === 'price') {
        document.getElementById('price_all').checked = true;
    } else if (type === 'sort') {
        document.querySelector('select[name="sort"]').value = '';
    }
    
    applyFilters();
}

// Initialize active filters on page load
document.addEventListener('DOMContentLoaded', function() {
    updateActiveFilters();
});
</script>