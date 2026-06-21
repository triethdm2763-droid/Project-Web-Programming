<?php
$selectedCategoryId = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : null;
$searchKeyword       = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortOption          = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$minPrice            = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$maxPrice            = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Danh mục sản phẩm | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow px-gutter py-8 w-full max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8">
            <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm">
                    <h2 class="font-headline-sm text-primary mb-5">Danh mục</h2>
                    <ul class="space-y-3" id="categoriesList">
                        <li><a href="#" class="block text-on-surface-variant">Đang tải...</a></li>
                    </ul>
                </div>

                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm">
                    <h2 class="font-headline-sm text-primary mb-4">Khoảng giá</h2>
                    <div class="flex items-center gap-2">
                        <input type="number" min="0" id="minPriceInput" placeholder="Từ" value="<?= htmlspecialchars($minPrice) ?>" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                        <span class="text-on-surface-variant">-</span>
                        <input type="number" min="0" id="maxPriceInput" placeholder="Đến" value="<?= htmlspecialchars($maxPrice) ?>" class="w-full px-3 py-2 text-sm bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
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

        function syncUrlState(params) {
            const url = new URL(window.location.href);
            Object.keys(params).forEach(key => {
                if (params[key]) url.searchParams.set(key, params[key]);
                else url.searchParams.delete(key);
            });
            window.history.replaceState({}, '', url);
        }

        async function loadCategories() {
            let list = document.getElementById("categoriesList");
            let titleEl = document.getElementById("categoryTitle");
            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/categories");
                let categories = await res.json();
                let isAll = !selectedCategoryId;
                if (isAll) titleEl.textContent = initialSearchKeyword ? `Kết quả cho "${initialSearchKeyword}"` : "Tất cả sản phẩm";
                
                let itemsHtml = `<li><a href="?category=" class="block ${isAll ? 'text-primary font-medium' : 'text-on-surface-variant hover:text-primary'} transition-colors">Tất cả danh mục</a></li>`;
                categories.forEach(cat => {
                    let isSelected = selectedCategoryId == cat.ID;
                    if (isSelected) titleEl.textContent = initialSearchKeyword ? `${cat.Name} - "${initialSearchKeyword}"` : cat.Name;
                    itemsHtml += `<li><a href="?category=${cat.ID}" class="flex items-center gap-2 px-3 py-2 rounded-lg ${isSelected ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary'}"><span class="material-symbols-outlined text-[20px]">${cat.Icon || 'category'}</span><span>${cat.Name}</span></a></li>`;
                });
                list.innerHTML = itemsHtml;
            } catch (e) { list.innerHTML = `<li><span class="text-red-500">Lỗi tải danh mục</span></li>`; }
        }
    </script>
    <script src="/Project-Web-Programming/frontend/assets/js/products.js?v=20260621-1"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            const searchInput = document.getElementById('categorySearchInput'), sortSelect = document.getElementById('sortSelect'), minP = document.getElementById('minPriceInput'), maxP = document.getElementById('maxPriceInput');
            function runSearch() {
                const search = searchInput.value.trim(), sort = sortSelect.value, minPrice = minP.value.trim(), maxPrice = maxP.value.trim();
                syncUrlState({ category: selectedCategoryId || '', search, sort, min_price: minPrice, max_price: maxPrice });
                if (typeof fetchProducts === 'function') fetchProducts(search, selectedCategoryId, true, { sort, minPrice, maxPrice });
            }
            if (typeof fetchProducts === 'function') fetchProducts(initialSearchKeyword, selectedCategoryId, false, { sort: initialSort, minPrice: initialMinPrice, maxPrice: initialMaxPrice });
            let t; searchInput.addEventListener('input', () => { clearTimeout(t); t = setTimeout(runSearch, 500); });
            sortSelect.addEventListener('change', runSearch);
            document.getElementById('applyPriceFilterBtn').addEventListener('click', runSearch);
            document.getElementById('clearFiltersBtn').addEventListener('click', () => { searchInput.value = ''; sortSelect.value = 'newest'; minP.value = ''; maxP.value = ''; runSearch(); });
        });
    </script>
</body>
</html>