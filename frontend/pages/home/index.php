<?php
// Frontend trang chủ - Hoàn toàn không truy vấn cơ sở dữ liệu trực tiếp nữa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <div class="relative w-full rounded-[24px] overflow-hidden shadow-sm h-[280px] md:h-[360px]">
    
            <div class="absolute inset-0 w-full h-full">
                <img src="/Project-Web-Programming/backend/uploads/products/banner1.jpg" 
            class="banner-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out opacity-100">

            <img src="/Project-Web-Programming/backend/uploads/products/banner2.jpg" 
            class="banner-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out opacity-0">

            <img src="/Project-Web-Programming/backend/uploads/products/banner3.jpg" 
            class="banner-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out opacity-0">
                    
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
                <a href="#products-section" class="bg-white text-primary font-semibold px-6 py-2.5 rounded-full text-[14px] w-max hover:bg-opacity-90 active:scale-95 transition-all text-center shadow">
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
                <div class="grid grid-cols-3 md:grid-cols-6 gap-4" id="categories-container">
                    <!-- Sẽ được tải động qua API -->
                </div>
            </section>

        <!-- ==========================================================================
           3. PRODUCT MARKETPLACE GRID (Lưới trống chờ đổ dữ liệu Tuần 2)
           ========================================================================== -->
        <section class="space-y-6" id="products-section">
            <div>
                <h2 class="font-headline-md text-xl font-bold text-on-background flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">campaign</span>
                    Tin đăng mới nhất
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter" id="products-container">
                <!-- Sẽ được tải động qua API -->
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        loadCategories();
        loadProducts();
        initBannerSlider();
    });

    async function loadCategories() {
        let container = document.getElementById("categories-container");
        container.innerHTML = `<div class="col-span-full text-center text-outline py-4">Đang tải danh mục...</div>`;
        
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/api/categories");
            let categories = await res.json();
            
            if (categories && categories.length > 0) {
                // Hiển thị tối đa 6 danh mục
                let limitCats = categories.slice(0, 6);
                container.innerHTML = limitCats.map(cat => `
                    <a href="/Project-Web-Programming/frontend/pages/products/category.php?category=${cat.ID}" data-id="${cat.ID}" data-navigate="1" class="category-btn bg-white border border-outline-variant/20 p-4 rounded-xl flex flex-col items-center justify-center text-center hover:border-primary hover:shadow-sm transition-all group">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary mb-2">category</span>
                        <span class="text-label-sm font-semibold text-on-surface">${escapeHtml(cat.Name)}</span>
                    </a>
                `).join('');
            } else {
                container.innerHTML = `<div class="col-span-full text-center text-outline py-4">Không có danh mục nào.</div>`;
            }
        } catch (error) {
            console.error("Error loading categories:", error);
            container.innerHTML = `<div class="col-span-full text-center text-red-500 py-4">Lỗi tải danh mục.</div>`;
        }
    }

    async function loadProducts() {
        let container = document.getElementById("products-container");
        container.innerHTML = `<div class="col-span-full text-center text-outline py-4">Đang tải sản phẩm...</div>`;
        
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/api/products");
            let products = await res.json();
            
            if (products && products.length > 0) {
                // Hiển thị tối đa 4 sản phẩm mới nhất
                let latestProducts = products.slice(0, 4);
                container.innerHTML = latestProducts.map(row => `
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-outline-variant/10 flex flex-col">
                        <div class="h-48 bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden">
                            <span class="absolute top-3 left-3 bg-tertiary text-white font-semibold text-[11px] px-2 py-1 rounded shadow-sm">Độc Bản (SL=1)</span>
                            <img src="/Project-Web-Programming/backend/uploads/products/${escapeHtml(row.Image || 'placeholder.png')}" alt="${escapeHtml(row.Name)}" onerror="this.src='/Project-Web-Programming/frontend/assets/images/placeholder.png'" class="w-full h-full object-contain p-4">
                        </div>
                        <div class="p-4 flex flex-col flex-grow space-y-2">
                            <h3 class="font-semibold text-[15px] line-clamp-2 h-11 block">
                                ${escapeHtml(row.Name)}
                            </h3>
                            <div class="text-primary font-bold text-lg">
                                ${new Intl.NumberFormat('vi-VN').format(row.Price)} đ
                            </div>
                            <div class="text-[13px] text-on-surface-variant">
                                ${escapeHtml(row.SellerName || 'Người bán ẩn danh')} • ${escapeHtml(row.CategoryName || '')}
                            </div>
                            <div class="mt-3">
                                <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${row.ID}" class="inline-block w-full text-center border border-primary text-primary rounded-md px-4 py-2 hover:bg-primary/10">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `<div class="col-span-full text-center text-outline py-4">Chưa có sản phẩm nào trong hệ thống.</div>`;
            }
        } catch (error) {
            console.error("Error loading products:", error);
            container.innerHTML = `<div class="col-span-full text-center text-red-500 py-4">Lỗi tải sản phẩm.</div>`;
        }
    }

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

    function initBannerSlider() {
        const slides = document.querySelectorAll('.banner-slide');
        if (slides.length <= 1) return;
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.replace('opacity-100', 'opacity-0');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.replace('opacity-0', 'opacity-100');
        }, 5000);
    }
    </script>
</body>
</html>