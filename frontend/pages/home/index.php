<?php
// 1. Nhúng file Database Singleton của Backend vào
require_once '../../../backend/src/config/Database.php';

// 2. Vì Backend dùng Namespace nên ta phải chỉ định rõ tên Class đầy đủ
use App\Config\Database;

// 3. Gọi kết nối theo chuẩn Singleton (Dùng getInstance() chứ không dùng "new")
$database = Database::getInstance();
$db = $database->getConnection(); // Gọi đúng hàm getConnection() của các bạn viết

$query = "SELECT p.ID AS id, p.Name AS name, p.Price AS price, p.Image AS image, u.Username AS seller, c.Name AS category, p.created_at
          FROM products p
          LEFT JOIN users u ON p.Seller_ID = u.ID
          LEFT JOIN categories c ON p.Category_ID = c.ID
          WHERE p.Status IN ('active', 'available')
          ORDER BY p.created_at DESC
          LIMIT 4";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll();

// Load categories for the Browse section from the database (seeded data)
$catsStmt = $db->prepare("SELECT ID, Name FROM categories ORDER BY Name ASC LIMIT 6");
$catsStmt->execute();
$categories = $catsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Trang Chủ | Chợ Cũ Marketplace</title>
    <!-- Nhúng Header chứa cấu hình Tailwind và biến màu style.css của Triết -->
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">

    <!-- Nhúng thanh điều hướng Navbar -->
    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full space-y-12">
        
        <!-- ==========================================================================
           1. HERO BANNER SECTION (Giao diện giới thiệu mô hình C2C Độc bản)
           ========================================================================== -->
        <div class="relative w-full rounded-[24px] overflow-hidden shadow-sm h-[200px] md:h-[400px]">
    
            <div class="absolute inset-0 w-full h-full">
                
                <img src="/Project-Web-Programming/backend/uploads/products/banner1.jpg" 
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-[center_66%] transition-opacity duration-1000 ease-in-out opacity-100">
                <img src="/Project-Web-Programming/backend/uploads/products/banner2.jpg" 
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-1000 ease-in-out opacity-0">

                <img src="/Project-Web-Programming/backend/uploads/products/banner3.jpg" 
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-1000 ease-in-out opacity-0">
                    
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent z-10"></div>
            </div>

            <div class="relative z-20 h-full flex flex-col justify-center px-6 md:px-10 max-w-xl space-y-4 text-white">
                <span class="bg-orange-500 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-md w-max">
                    SÀN C2C UY TÍN TOÀN QUỐC
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
                    Nền Tảng Mua Bán &<br>Thanh Lý Đồ Cũ Chính Chủ
                </h1>
                <p class="text-xs md:text-sm text-gray-200 leading-relaxed font-light">
                    Trải nghiệm luồng chốt đơn siêu tốc với mô hình <strong class="text-orange-400">"Mua Ngay"</strong> dành riêng cho các mặt hàng độc bản, số lượng chỉ có một. Ai đến trước, mua trước!
                </p>
                <a href="/Project-Web-Programming/frontend/pages/products/category.php" class="bg-white text-primary font-semibold px-6 py-2.5 rounded-full text-[14px] w-max hover:bg-opacity-90 active:scale-95 transition-all text-center shadow">
                    Khám Phá Ngay
                </a>
            </div>
            
        </div>
            <!-- ==========================================================================
            2. BROWSE BY CATEGORIES (Bộ lọc danh mục trực quan)
            ========================================================================== -->
            <section class="space-y-4">
                <h2 class="font-headline-md text-xl md:text-2xl font-bold text-on-background flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">grid_view</span> Danh Mục Nổi Bật
                </h2>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <a href="/Project-Web-Programming/frontend/pages/products/category.php?category=<?php echo $cat['ID']; ?>" data-id="<?php echo $cat['ID']; ?>" data-navigate="1" class="category-btn bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                                <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">category</span>
                                <span class="text-label-sm font-semibold text-on-surface"><?php echo htmlspecialchars($cat['Name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- fallback hardcoded items if DB not available -->
                        <a href="/Project-Web-Programming/frontend/pages/products/category.php?category=1" data-id="1" data-navigate="1" class="category-btn bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">devices</span>
                            <span class="text-label-sm font-semibold text-on-surface">Đồ Điện Tử</span>
                        </a>
                    <?php endif; ?>
                </div>
            </section>

        <!-- ==========================================================================
           3. PRODUCT MARKETPLACE GRID (Lưới trống chờ đổ dữ liệu Tuần 2)
           ========================================================================== -->
        <section class="space-y-6">
            <div>
                <h2 class="font-headline-md text-xl font-bold text-on-background flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">campaign</span>
                    Tin đăng mới nhất
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter" <?php if (!empty($products)) { echo 'data-server-rendered="1"'; if (!empty($products[0]['created_at'])) echo ' data-last-created="'.htmlspecialchars($products[0]['created_at']).'"'; } ?>>
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $row): ?>
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-outline-variant/10 flex flex-col">
                <div class="h-48 bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden">
                    <span class="absolute top-3 left-3 bg-tertiary text-white font-semibold text-[11px] px-2 py-1 rounded shadow-sm">Độc Bản (SL=1)</span>
                    <img src="/Project-Web-Programming/backend/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" onerror="this.src='/Project-Web-Programming/frontend/assets/images/placeholder.png'" class="w-full h-full object-contain p-4">
                </div>
                <div class="p-4 flex flex-col flex-grow space-y-2">
                    <h3 class="font-semibold text-[15px] line-clamp-2 h-11 block">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </h3>
                    <div class="text-primary font-bold text-lg">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> đ
                    </div>
                    <div class="text-[13px] text-on-surface-variant">
                        <?php echo htmlspecialchars($row['seller'] ?? 'Người bán ẩn danh'); ?> • <?php echo htmlspecialchars($row['category'] ?? ''); ?>
                    </div>
                    <div class="mt-3">
                        <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=<?php echo $row['id']; ?>" class="inline-block w-full text-center border border-primary text-primary rounded-md px-4 py-2 hover:bg-primary/10">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có sản phẩm nào trong hệ thống.</p>
    <?php endif; ?>
</div>
        </section>

        <!-- ==========================================================================
           4. THREE FEATURE HIGHLIGHTS (Giao dịch an toàn / Vận chuyển nhanh / Hỗ trợ 24/7)
           ========================================================================== -->
    <section class="mt-12 bg-[rgba(0,74,198,0.1)] py-8 rounded-lg">
            <div class="max-w-container-max mx-auto px-gutter">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="p-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary">verified</span>
                        </div>
                        <h3 class="font-semibold text-md mb-2">Giao dịch an toàn</h3>
                        <p class="text-sm text-on-surface-variant">Quy trình xác thực người dùng và tin đăng nghiêm ngặt, đảm bảo an toàn tối đa cho người mua và người bán.</p>
                    </div>

                    <div class="p-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary">local_shipping</span>
                        </div>
                        <h3 class="font-semibold text-md mb-2">Vận chuyển nhanh</h3>
                        <p class="text-sm text-on-surface-variant">Hợp tác với các đơn vị vận chuyển hàng đầu, lấy hàng tận nơi, giao hàng tận tay chỉ trong vài ngày.</p>
                    </div>

                    <div class="p-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary">support_agent</span>
                        </div>
                        <h3 class="font-semibold text-md mb-2">Hỗ trợ 24/7</h3>
                        <p class="text-sm text-on-surface-variant">Đội ngũ chăm sóc khách hàng luôn sẵn sàng giải đáp mọi thắc mắc và khiếu nại của bạn bất cứ lúc nào.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Nhúng chân trang Footer -->
    <?php include '../../components/footer.php'; ?>
    <!-- Nhúng file JavaScript để xử lý tương tác sản phẩm -->
    <script src="/Project-Web-Programming/frontend/assets/js/products.js"></script>

</body>
</html>