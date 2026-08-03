<?php
$selectedCategoryId = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : null;
$searchKeyword       = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortOption          = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$minPrice            = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$maxPrice            = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$locationOption      = isset($_GET['location']) ? trim($_GET['location']) : '';
$conditionOption     = isset($_GET['condition_status']) ? trim($_GET['condition_status']) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Danh mục sản phẩm | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow px-gutter py-8 w-full max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8">
            <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
                <!-- Danh mục -->
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white/60 backdrop-blur-md">
                    <h2 class="font-headline-sm text-primary mb-5">Danh mục</h2>
                    <ul class="flex flex-row md:flex-col gap-2 md:space-y-2 overflow-x-auto md:overflow-x-visible pb-2 md:pb-0 scrollbar-none" id="categoriesList">
                        <li><a href="#" class="block text-on-surface-variant">Đang tải...</a></li>
                    </ul>
                </div>

                <!-- Khu vực -->
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white/60 backdrop-blur-md">
                    <h2 class="font-headline-sm text-primary mb-4">Khu vực</h2>
                    <select id="locationFilter" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer">
                        <option value="" <?= $locationOption === '' ? 'selected' : '' ?>>Tất cả khu vực</option>
                        <option value="Hồ Chí Minh" <?= $locationOption === 'Hồ Chí Minh' ? 'selected' : '' ?>>TP. Hồ Chí Minh</option>
                        <option value="Hà Nội" <?= $locationOption === 'Hà Nội' ? 'selected' : '' ?>>Hà Nội</option>
                        <option value="Đà Nẵng" <?= $locationOption === 'Đà Nẵng' ? 'selected' : '' ?>>Đà Nẵng</option>
                        <option value="Cần Thơ" <?= $locationOption === 'Cần Thơ' ? 'selected' : '' ?>>Cần Thơ</option>
                        <option value="Hải Phòng" <?= $locationOption === 'Hải Phòng' ? 'selected' : '' ?>>Hải Phòng</option>
                        <option value="Bình Dương" <?= $locationOption === 'Bình Dương' ? 'selected' : '' ?>>Bình Dương</option>
                        <option value="Đồng Nai" <?= $locationOption === 'Đồng Nai' ? 'selected' : '' ?>>Đồng Nai</option>
                    </select>
                </div>

                <!-- Tình trạng -->
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white/60 backdrop-blur-md">
                    <h2 class="font-headline-sm text-primary mb-4">Tình trạng</h2>
                    <select id="conditionFilter" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer">
                        <option value="" <?= $conditionOption === '' ? 'selected' : '' ?>>Tất cả tình trạng</option>
                        <option value="Mới" <?= $conditionOption === 'Mới' ? 'selected' : '' ?>>Mới</option>
                        <option value="99%" <?= $conditionOption === '99%' ? 'selected' : '' ?>>99% (Like New)</option>
                        <option value="Đã sử dụng" <?= $conditionOption === 'Đã sử dụng' ? 'selected' : '' ?>>Đã sử dụng (Cũ)</option>
                    </select>
                </div>

                <!-- Khoảng giá -->
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white/60 backdrop-blur-md">
                    <h2 class="font-headline-sm text-primary mb-4">Khoảng giá</h2>
                    <div class="flex items-center gap-2">
                        <input type="text" inputmode="numeric" id="minPriceInput" placeholder="Từ" value="<?= htmlspecialchars($minPrice) ?>" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                        <span class="text-on-surface-variant">-</span>
                        <input type="text" inputmode="numeric" id="maxPriceInput" placeholder="Đến" value="<?= htmlspecialchars($maxPrice) ?>" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button id="applyPriceFilterBtn" class="flex-grow bg-primary text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">Áp dụng</button>
                        <button id="clearFiltersBtn" class="px-3 py-2 text-sm text-on-surface-variant border border-outline-variant/40 rounded-lg hover:bg-surface-container transition-colors">Xóa</button>
                    </div>
                </div>
            </aside>

            <section class="flex-grow">
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-4">
                    <h1 class="text-headline-md font-bold text-on-surface" id="categoryTitle">Đang tải...</h1>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                            <input type="text" id="categorySearchInput" placeholder="Tìm trong kết quả..." value="<?= htmlspecialchars($searchKeyword) ?>" class="pl-9 pr-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 w-44 sm:w-56">
                        </div>
                        <select id="sortSelect" class="px-4 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="newest" <?= $sortOption === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="price_desc" <?= $sortOption === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                            <option value="price_asc" <?= $sortOption === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                        </select>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant mb-6" id="resultCountLabel"></p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="categoryProducts"></div>
                <div class="flex justify-center mt-8">
                    <button id="loadMoreBtn" class="hidden bg-white text-primary border border-primary px-8 py-3 rounded-xl font-medium hover:bg-primary hover:text-white transition-all shadow-sm flex items-center gap-2">
                        <span>Xem thêm sản phẩm</span>
                        <span class="material-symbols-outlined text-[20px]">expand_more</span>
                    </button>
                </div>
            </section>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        const selectedCategoryId = <?= json_encode($selectedCategoryId) ?>;
        const initialSearchKeyword = <?= json_encode($searchKeyword) ?>;
        const initialSort = <?= json_encode($sortOption) ?>;
        const initialMinPrice = <?= json_encode($minPrice) ?>;
        const initialMaxPrice = <?= json_encode($maxPrice) ?>;
        const initialLocation = <?= json_encode($locationOption) ?>;
        const initialCondition = <?= json_encode($conditionOption) ?>;

        let activeCategoryId = selectedCategoryId;
        let loadedCategoriesData = [];

        function syncUrlState(params) {
            const url = new URL(window.location.href);
            Object.keys(params).forEach(key => {
                if (params[key] !== undefined && params[key] !== null && params[key] !== '') url.searchParams.set(key, params[key]);
                else url.searchParams.delete(key);
            });
            window.history.replaceState({}, '', url);
        }

        function updateCategoryUI() {
            const titleEl = document.getElementById("categoryTitle");
            const searchVal = document.getElementById('categorySearchInput')?.value.trim();
            const list = document.getElementById("categoriesList");
            let isAll = !activeCategoryId;
            let currentCatName = "Tất cả sản phẩm";
            
            if (list) {
                list.querySelectorAll('.category-link').forEach(link => {
                    const id = link.getAttribute('data-id');
                    const linkIsAll = !id;
                    const isSelected = id ? (activeCategoryId == id) : isAll;
                    
                    if (linkIsAll) {
                        if (isSelected) {
                            link.className = `category-link flex items-center gap-2 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium whitespace-nowrap md:whitespace-normal`;
                        } else {
                            link.className = `category-link flex items-center gap-2 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary whitespace-nowrap md:whitespace-normal`;
                        }
                    } else {
                        const cat = loadedCategoriesData.find(c => c.ID == id);
                        if (isSelected) {
                            if (cat) currentCatName = cat.Name;
                            link.className = `category-link flex items-center gap-2 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium whitespace-nowrap md:whitespace-normal`;
                        } else {
                            link.className = `category-link flex items-center gap-2 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary whitespace-nowrap md:whitespace-normal`;
                        }
                    }
                });
            }

            if (isAll) {
                titleEl.textContent = searchVal ? `Kết quả cho "${searchVal}"` : "Tất cả sản phẩm";
            } else {
                titleEl.textContent = searchVal ? `${currentCatName} - "${searchVal}"` : currentCatName;
            }
        }

        async function loadCategories(onCategoryClick) {
            let list = document.getElementById("categoriesList");
            try {
                let res = await fetch("/backend/public/index.php/api/categories");
                let categories = await res.json();
                loadedCategoriesData = categories;
                
                const renderList = () => {
                    let isAll = !activeCategoryId;
                    let itemsHtml = `<li class="shrink-0 md:shrink"><a href="#" data-id="" class="category-link flex items-center gap-2 px-3 py-2 rounded-lg ${isAll ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'} whitespace-nowrap md:whitespace-normal"><span class="material-symbols-outlined text-[20px]">grid_view</span><span>Tất cả</span></a></li>`;
                    categories.forEach(cat => {
                        let isSelected = activeCategoryId == cat.ID;
                        itemsHtml += `<li class="shrink-0 md:shrink"><a href="#" data-id="${cat.ID}" class="category-link flex items-center gap-2 px-3 py-2 rounded-lg ${isSelected ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'} whitespace-nowrap md:whitespace-normal"><span class="material-symbols-outlined text-[20px]">${cat.Icon || 'category'}</span><span>${cat.Name}</span></a></li>`;
                    });
                    list.innerHTML = itemsHtml;

                    updateCategoryUI();

                    list.querySelectorAll('.category-link').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const id = this.getAttribute('data-id');
                            activeCategoryId = id ? parseInt(id) : null;
                            updateCategoryUI();
                            if (onCategoryClick) onCategoryClick(activeCategoryId);
                        });
                    });
                }
                
                renderList();
            } catch (e) { list.innerHTML = `<li><span class="text-red-500">Lỗi tải danh mục</span></li>`; }
        }
    </script>
    <script src="/frontend/assets/js/products.js?v=20260702-1"></script>
    <script>
        function formatCurrencyInput(input) {
            let clean = input.value.replace(/\D/g, "");
            if (!clean) {
                input.value = "";
                return;
            }
            input.value = new Intl.NumberFormat('vi-VN').format(parseInt(clean));
        }

        function setupCurrencyInput(id) {
            const input = document.getElementById(id);
            if (!input) return;
            input.type = "text";
            input.setAttribute("inputmode", "numeric");
            if (input.value) {
                let clean = input.value.replace(/\D/g, "");
                if (clean) input.value = new Intl.NumberFormat('vi-VN').format(parseInt(clean));
            }
            input.addEventListener("input", function() {
                let cursorPosition = this.selectionStart;
                let originalLength = this.value.length;
                let clean = this.value.replace(/\D/g, "");
                if (!clean) {
                    this.value = "";
                    return;
                }
                this.value = new Intl.NumberFormat('vi-VN').format(parseInt(clean));
                let newLength = this.value.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                this.setSelectionRange(cursorPosition, cursorPosition);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearchInput');
            const sortSelect = document.getElementById('sortSelect');
            const minP = document.getElementById('minPriceInput');
            const maxP = document.getElementById('maxPriceInput');
            const locationSelect = document.getElementById('locationFilter');
            const conditionSelect = document.getElementById('conditionFilter');

            setupCurrencyInput('minPriceInput');
            setupCurrencyInput('maxPriceInput');
            
            function runSearch() {
                const search = searchInput.value.trim();
                const sort = sortSelect.value;
                const minPrice = minP.value.replace(/\D/g, "");
                const maxPrice = maxP.value.replace(/\D/g, "");
                const location = locationSelect.value;
                const condition = conditionSelect.value;

                updateCategoryUI();

                syncUrlState({
                    category: activeCategoryId || '',
                    search,
                    sort,
                    min_price: minPrice,
                    max_price: maxPrice,
                    location,
                    condition_status: condition
                });

                if (typeof fetchProducts === 'function') {
                    fetchProducts(search, activeCategoryId || '', true, {
                        sort,
                        minPrice,
                        maxPrice,
                        location,
                        condition_status: condition
                    });
                }
            }

            loadCategories((newCategoryId) => {
                runSearch();
            });

            if (typeof fetchProducts === 'function') {
                const minPriceRaw = initialMinPrice.replace(/\D/g, "");
                const maxPriceRaw = initialMaxPrice.replace(/\D/g, "");
                fetchProducts(initialSearchKeyword, activeCategoryId, false, {
                    sort: initialSort,
                    minPrice: minPriceRaw,
                    maxPrice: maxPriceRaw,
                    location: initialLocation,
                    condition_status: initialCondition
                });
            }
            
            let t; 
            searchInput.addEventListener('input', () => { clearTimeout(t); t = setTimeout(runSearch, 500); });
            sortSelect.addEventListener('change', runSearch);
            locationSelect.addEventListener('change', runSearch);
            conditionSelect.addEventListener('change', runSearch);
            document.getElementById('applyPriceFilterBtn').addEventListener('click', runSearch);
            document.getElementById('clearFiltersBtn').addEventListener('click', () => { 
                searchInput.value = ''; 
                sortSelect.value = 'newest'; 
                minP.value = ''; 
                maxP.value = ''; 
                locationSelect.value = '';
                conditionSelect.value = '';
                activeCategoryId = null;
                loadCategories((newCategoryId) => {
                    runSearch();
                });
            });

            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    if (typeof loadNextProductPage === 'function') {
                        loadNextProductPage();
                    }
                });
            }
        });
    </script>
</body>
</html>