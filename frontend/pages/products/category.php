<?php
// Get selected category from query parameter
$selectedCategoryId = isset($_GET['category']) ? intval($_GET['category']) : null;
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
            
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm">
                    <h2 class="font-headline-sm text-primary mb-5">Danh mục</h2>
                    <ul class="space-y-3" id="categoriesList">
                        <li><a href="#" class="block text-on-surface-variant">Đang tải...</a></li>
                    </ul>
                </div>
            </aside>

            <section class="flex-grow">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-headline-md font-bold text-on-surface" id="categoryTitle">Đang tải...</h1>
                    <select class="px-4 py-2 bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                        <option>Mới nhất</option>
                        <option>Giá cao đến thấp</option>
                        <option>Giá thấp đến cao</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="categoryProducts">
                    <!-- Product cards will be populated by frontend/assets/js/products.js via fetchProducts('', selectedCategoryId) -->
                </div>
            </section>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
    // Export selected category id from PHP to JS
    const selectedCategoryId = <?= json_encode($selectedCategoryId) ?>;

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    async function loadCategories() {
        let list = document.getElementById("categoriesList");
        let titleEl = document.getElementById("categoryTitle");
        
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/api/categories");
            let categories = await res.json();
            
            let isAll = !selectedCategoryId;
            if (isAll) {
                titleEl.textContent = "Tất cả sản phẩm";
            }
            
            let itemsHtml = `<li><a href="?category=" class="block ${isAll ? 'text-primary font-medium' : 'text-on-surface-variant hover:text-primary'} transition-colors">Tất cả danh mục</a></li>`;
            
            if (categories && categories.length > 0) {
                categories.forEach(cat => {
                    let isSelected = selectedCategoryId == cat.ID;
                    if (isSelected) {
                        titleEl.textContent = cat.Name;
                    }
                    itemsHtml += `
                        <li>
                            <a href="?category=${cat.ID}" class="block ${isSelected ? 'text-primary font-medium' : 'text-on-surface-variant hover:text-primary'} transition-colors">
                                ${escapeHtml(cat.Name)}
                            </a>
                        </li>
                    `;
                });
            }
            list.innerHTML = itemsHtml;
        } catch (error) {
            console.error("Error loading categories:", error);
            list.innerHTML = `<li><a href="#" class="block text-red-500">Lỗi tải danh mục</a></li>`;
            titleEl.textContent = "Danh mục sản phẩm";
        }
    }
    </script>

    <!-- Products JS (re-uses fetchProducts to populate the grid) -->
    <script src="/Project-Web-Programming/frontend/assets/js/products.js"></script>
    <script>
    // When DOM is ready, load categories sidebar and then fetch products for the selected category
    document.addEventListener('DOMContentLoaded', function() {
        loadCategories();

        // Fetch products filtered by selected category
        if (typeof fetchProducts === 'function') {
            fetchProducts('', selectedCategoryId);
        }
    });
    </script>
</body>
</html>