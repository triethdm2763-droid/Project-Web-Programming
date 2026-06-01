<?php
// 1. Nhúng file cấu hình database vào
require_once '../../../backend/config/database.php';

// 2. Khởi tạo kết nối
$database = new Database();
$db = $database->connection();

// 3. Viết câu lệnh SQL bốc 4 sản phẩm mẫu từ database ra
$query = "SELECT * FROM products LIMIT 4";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll();
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
        <section class="relative bg-gradient-to-r from-primary to-[#1e6aff] text-white rounded-2xl p-8 md:p-12 shadow-md overflow-hidden group">
            <div class="max-w-xl relative z-10 space-y-4">
                <span class="bg-secondary-container text-white text-[12px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                    Sàn C2C Uy Tín Toàn Quốc
                </span>
                <h1 class="font-headline-lg text-3xl md:text-5xl font-extrabold tracking-tight leading-tight">
                    Nền Tảng Mua Bán & Thanh Lý Đồ Cũ Chính Chủ
                </h1>
                <p class="text-white/80 text-body-md max-w-md leading-relaxed">
                    Trải nghiệm luồng chốt đơn siêu tốc với mô hình **"Mua Ngay"** dành riêng cho các mặt hàng độc bản, số lượng chỉ có một. Ai đến trước, mua trước!
                </p>
                <div class="pt-2">
                    <a href="#" class="inline-block bg-white text-primary font-bold px-8 py-3.5 rounded-full shadow-md hover:bg-surface transition-all active:scale-95 text-[15px]">
                        Khám Phá Ngay
                    </a>
                </div>
            </div>
            <div class="absolute right-0 bottom-0 top-0 w-1/2 hidden md:block opacity-20 group-hover:opacity-30 transition-opacity pointer-events-none">
                <span class="material-symbols-outlined text-[300px] absolute right-4 top-1/2 -translate-y-1/2 text-white">storefront</span>
            </div>
        </section>

        <!-- ==========================================================================
           2. BROWSE BY CATEGORIES (Bộ lọc danh mục trực quan)
           ========================================================================== -->
        <section class="space-y-4">
            <h2 class="font-headline-md text-xl md:text-2xl font-bold text-on-background flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">grid_view</span> Danh Mục Nổi Bật
            </h2>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">devices</span>
                    <span class="text-label-sm font-semibold text-on-surface">Đồ Điện Tử</span>
                </a>
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">apparel</span>
                    <span class="text-label-sm font-semibold text-on-surface">Thời Trang</span>
                </a>
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">chair</span>
                    <span class="text-label-sm font-semibold text-on-surface">Nhà Cửa & Đời Sống</span>
                </a>
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">directions_bike</span>
                    <span class="text-label-sm font-semibold text-on-surface">Xe Cộ</span>
                </a>
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">menu_book</span>
                    <span class="text-label-sm font-semibold text-on-surface">Sách & Giải Trí</span>
                </a>
                <a href="#" class="bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                    <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">sports_esports</span>
                    <span class="text-label-sm font-semibold text-on-surface">Máy Game / Console</span>
                </a>
            </div>
        </section>

        <!-- ==========================================================================
           3. PRODUCT MARKETPLACE GRID (Lưới trống chờ đổ dữ liệu Tuần 2)
           ========================================================================== -->
        <section class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $row): ?>
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-outline-variant/10 flex flex-col">
                <div class="aspect-square bg-surface-container flex items-center justify-center relative text-outline/50">
                    <span class="absolute top-3 left-3 bg-tertiary text-white font-semibold text-[11px] px-2 py-1 rounded shadow-sm">Độc Bản (SL=1)</span>
                    <span class="material-symbols-outlined text-5xl">image</span>
                </div>
                <div class="p-4 flex flex-col flex-grow space-y-2">
                    <h3 class="font-semibold text-[15px] line-clamp-2 h-11 block">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </h3>
                    <div class="text-primary font-bold text-lg">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> đ
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có sản phẩm nào trong hệ thống.</p>
    <?php endif; ?>
</div>
        </section>
    </main>

    <!-- Nhúng chân trang Footer -->
    <?php include '../../components/footer.php'; ?>
</body>
</html>