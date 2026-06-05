<?php
// Get selected category from query parameter
$selectedCategoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

// Load categories server-side so sidebar shows immediately
require_once '../../../backend/src/config/Database.php';
use App\Config\Database;
$database = Database::getInstance();
$db = $database->getConnection();

$categories = [];
$categoryName = null;
try {
    $stmt = $db->prepare("SELECT ID, Name FROM categories ORDER BY Name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    if ($selectedCategoryId) {
        $cstmt = $db->prepare("SELECT Name FROM categories WHERE ID = :id LIMIT 1");
        $cstmt->execute(['id' => $selectedCategoryId]);
        $crow = $cstmt->fetch();
        if ($crow && !empty($crow['Name'])) $categoryName = $crow['Name'];
    }
} catch (Exception $e) {
    // ignore DB errors here; frontend will fallback to JS fetch
}
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
                        <li><a href="?category=" class="block <?php echo empty($selectedCategoryId) ? 'text-primary font-medium' : 'text-on-surface-variant hover:text-primary'; ?> transition-colors">Tất cả danh mục</a></li>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="?category=<?php echo $cat['ID']; ?>" class="block <?php echo ($selectedCategoryId == $cat['ID']) ? 'text-primary font-medium' : 'text-on-surface-variant hover:text-primary'; ?> transition-colors">
                                        <?php echo htmlspecialchars($cat['Name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="#" class="block text-on-surface-variant">Đang tải...</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </aside>

            <section class="flex-grow">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-headline-md font-bold text-on-surface"><?php echo $categoryName ? htmlspecialchars($categoryName) : 'Tất cả sản phẩm'; ?></h1>
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
    </script>

    <!-- Products JS (re-uses fetchProducts to populate the grid) -->
    <script src="/Project-Web-Programming/frontend/assets/js/products.js"></script>
    <script>
    // When DOM is ready, load categories sidebar and then fetch products for the selected category
    document.addEventListener('DOMContentLoaded', function() {
        // existing loadCategories function is defined inline below if needed; reuse previous handler by calling the function if present
        if (typeof loadCategories === 'function') {
            try { loadCategories(); } catch (e) { /* ignore */ }
        }

        // Fetch products filtered by selected category
        if (typeof fetchProducts === 'function') {
            fetchProducts('', selectedCategoryId);
        }
    });
    </script>
</body>
</html>