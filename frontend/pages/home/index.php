<?php
// Frontend trang chủ - Hoàn toàn không truy vấn cơ sở dữ liệu trực tiếp nữa
require_once __DIR__ . '/../../components/session.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Trang Chủ | Chợ Thanh Lý Marketplace</title>
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

                <img src="/backend/uploads/products/banner1.jpg"
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-[center_66%] transition-opacity duration-1000 ease-in-out opacity-100">
                <img src="/backend/uploads/products/banner2.jpg"
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-1000 ease-in-out opacity-0">

                <img src="/backend/uploads/products/banner3.jpg"
                    class="banner-slide absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-1000 ease-in-out opacity-0">

                <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent z-10"></div>
            </div>

            <div class="relative z-20 h-full flex flex-col justify-center px-6 md:px-10 max-w-xl space-y-4 text-white">
                <span class="bg-orange-500 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-md w-max">
                    SÀN THANH LÝ UY TÍN TOÀN QUỐC
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
                    Nền Tảng Mua Bán &<br>Thanh Lý Đồ Cũ Chính Chủ
                </h1>
                <p class="text-xs md:text-sm text-gray-200 leading-relaxed font-light">
                    Trải nghiệm luồng chốt đơn siêu tốc với mô hình <strong class="text-orange-400">"Mua Ngay"</strong> dành riêng cho các mặt hàng độc bản, số lượng chỉ có một. Ai đến trước, mua trước!
                </p>
                <a href="/frontend/pages/products/category.php" class="bg-white text-primary font-semibold px-6 py-2.5 rounded-full text-[14px] w-max hover:bg-opacity-90 active:scale-95 transition-all text-center shadow">
                    Khám Phá Ngay
                </a>
            </div>

        </div>
        <!-- ==========================================================================
            2. PRODUCT MARKETPLACE GRID (Lưới sản phẩm mới nhất)
            ========================================================================== -->
        <section class="space-y-6" id="products-section">
            <div>
                <h2 class="font-headline-md text-xl font-bold text-on-background flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">campaign</span>
                    Tin đăng mới nhất
                </h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-gutter" id="products-container">
                <!-- Sẽ được tải động qua API -->
            </div>
            <div class="flex justify-center mt-8">
                <button id="loadMoreBtn" class="hidden bg-white text-primary border border-primary px-8 py-3 rounded-xl font-medium hover:bg-primary hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <span>Xem thêm sản phẩm</span>
                    <span class="material-symbols-outlined text-[20px]">expand_more</span>
                </button>
            </div>
        </section>

        <!-- ==========================================================================
           4. THREE FEATURE HIGHLIGHTS (Giao dịch an toàn / Vận chuyển nhanh / Hỗ trợ 24/7)
           ========================================================================== -->
        <section class="mt-4">
            <div class="max-w-container-max mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="bg-white border border-slate-100 p-8 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-all duration-300 flex flex-col items-center group">
                        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mb-4 ring-8 ring-blue-50/50 group-hover:scale-105 transition-transform duration-300">
                            <span class="material-symbols-outlined text-blue-600 text-[26px]">verified</span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base mb-2">Giao dịch an toàn</h3>
                        <p class="text-xs text-slate-400 max-w-[250px] leading-relaxed">Quy trình xác thực người dùng và tin đăng nghiêm ngặt, đảm bảo an toàn tối đa cho người mua và người bán.</p>
                    </div>

                    <div class="bg-white border border-slate-100 p-8 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-all duration-300 flex flex-col items-center group">
                        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mb-4 ring-8 ring-blue-50/50 group-hover:scale-105 transition-transform duration-300">
                            <span class="material-symbols-outlined text-blue-600 text-[26px]">local_shipping</span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base mb-2">Vận chuyển nhanh</h3>
                        <p class="text-xs text-slate-400 max-w-[250px] leading-relaxed">Hợp tác với các đơn vị vận chuyển hàng đầu, lấy hàng tận nơi, giao hàng tận tay chỉ trong vài ngày.</p>
                    </div>

                    <div class="bg-white border border-slate-100 p-8 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-all duration-300 flex flex-col items-center group">
                        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mb-4 ring-8 ring-blue-50/50 group-hover:scale-105 transition-transform duration-300">
                            <span class="material-symbols-outlined text-blue-600 text-[26px]">support_agent</span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base mb-2">Hỗ trợ 24/7</h3>
                        <p class="text-xs text-slate-400 max-w-[250px] leading-relaxed">Đội ngũ chăm sóc khách hàng luôn sẵn sàng giải đáp mọi thắc mắc và khiếu nại của bạn bất cứ lúc nào.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Nhúng chân trang Footer -->
    <?php include '../../components/footer.php'; ?>
    <!-- Nhúng file JavaScript để xử lý tương tác sản phẩm -->
    <script src="/frontend/assets/js/products.js?v=20260702-1"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadProducts(1);
            initBannerSlider();

            const loadMoreBtn = document.getElementById("loadMoreBtn");
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener("click", function() {
                    homeCurrentPage++;
                    loadProducts(homeCurrentPage);
                });
            }
        });

        let homeCurrentPage = 1;
        const homeLimit = 10;
        let homeTotalProducts = 0;

        async function loadProducts(page = 1) {
            let container = document.getElementById("products-container");
            const loadMoreBtn = document.getElementById("loadMoreBtn");

            if (page === 1) {
                container.innerHTML = `<div class="col-span-full text-center text-outline py-4">Đang tải sản phẩm...</div>`;
            }

            try {
                let res = await fetch(`/backend/public/index.php/api/products?limit=${homeLimit}&page=${page}`);
                let result = await res.json();
                let products = result.data || [];
                homeTotalProducts = result.total || 0;

                if (page === 1) {
                    container.innerHTML = '';
                }

                if (products && products.length > 0) {
                    const productsHtml = products.map(row => {
                        const qty = parseInt(row.Stock_quantity ?? row.stock_quantity ?? 1);
                        const badgeHtml = qty === 1 ?
                            `<span class="absolute top-3 left-3 bg-[#fd761a] text-white font-bold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm">Độc bản</span>` :
                            `<span class="absolute top-3 left-3 bg-blue-600 text-white font-bold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm">Còn ${qty}</span>`;
                        return `
                    <a href="/frontend/pages/products/detail.php?id=${row.ID}" class="bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-[0_8px_30px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-all duration-300 flex flex-col group">
                        <div class="aspect-square bg-slate-50 flex items-center justify-center relative overflow-hidden shrink-0">
                            ${badgeHtml}
                            <img src="${(row.Image && (row.Image.startsWith('http://') || row.Image.startsWith('https://'))) ? escapeHtml(row.Image) : '/backend/uploads/products/' + escapeHtml(row.Image || 'placeholder.png')}" alt="${escapeHtml(row.Name)}" onerror="this.src='/frontend/assets/images/placeholder.png'" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-4 flex flex-col flex-grow justify-between gap-3">
                            <div class="space-y-1.5">
                                <h3 class="font-bold text-[14px] leading-snug line-clamp-2 h-10 text-slate-800 group-hover:text-primary transition-colors">
                                    ${escapeHtml(row.Name)}
                                </h3>
                                <div class="text-primary font-bold text-base">
                                    ${new Intl.NumberFormat('vi-VN').format(row.Price)} đ
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-50">
                                <span class="flex items-center gap-1 truncate max-w-[100px]">
                                    <span class="material-symbols-outlined text-[12px] shrink-0 text-slate-400">person</span>
                                    <span class="truncate">${escapeHtml(row.SellerName || 'Ẩn danh')}</span>
                                </span>
                                <span class="bg-slate-50 text-slate-500 px-2 py-0.5 rounded-md text-[10px] font-semibold shrink-0 border border-slate-100">${escapeHtml(row.CategoryName || 'Khác')}</span>
                            </div>
                        </div>
                    </a>
                    `;
                    }).join('');

                    container.insertAdjacentHTML('beforeend', productsHtml);
                } else if (page === 1) {
                    container.innerHTML = `<div class="col-span-full text-center text-outline py-8">Chưa có sản phẩm nào trong hệ thống.</div>`;
                }

                // Cập nhật ẩn hiện nút Xem thêm
                if (loadMoreBtn) {
                    const loadedCount = container.children.length;
                    if (loadedCount < homeTotalProducts && products.length > 0) {
                        loadMoreBtn.classList.remove('hidden');
                    } else {
                        loadMoreBtn.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error("Error loading products:", error);
                if (page === 1) {
                    container.innerHTML = `<div class="col-span-full text-center text-red-500 py-4">Lỗi tải sản phẩm.</div>`;
                }
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