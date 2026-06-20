<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Giỏ hàng | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-[#f5f5f5] font-body-md text-on-surface min-h-screen flex flex-col"
      onload="if(typeof renderCart==='function') renderCart();">

    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-[1080px] mx-auto px-4 py-8 flex-grow w-full">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-[#0066cc]">
                Giỏ hàng
            </h1>
            <p id="cart-item-count"
            class="text-sm text-slate-500 mt-2">
                Đang tải...
            </p>
        </div>

        <!-- KHỐI DUY NHẤT: danh sách sản phẩm + thanh thanh toán dính liền ở đáy (kiểu Shopee) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            <!-- Header cột (chỉ hiện khi có sản phẩm) -->
            <div id="cart-table-header"
                class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 border-b text-sm text-slate-500 bg-slate-50/60">
                <div class="col-span-6 flex items-center gap-3">
                    <input
                        type="checkbox"
                        id="select-all-checkbox"
                        checked
                        onchange="toggleSelectAll(this.checked)"
                        class="w-5 h-5 accent-[#0066cc] cursor-pointer">
                    <span>Sản phẩm</span>
                </div>
                <div class="col-span-2 text-center">
                    Đơn giá
                </div>
                <div class="col-span-2 text-center">
                    Số lượng
                </div>
                <div class="col-span-1 text-right">
                    Thành tiền
                </div>
                <div class="col-span-1 text-right">
                    <button onclick="clearCart()"
                            class="text-red-500 text-sm hover:text-red-700 font-medium">
                        Xóa hết
                    </button>
                </div>
            </div>

            <!-- Thanh hành động gọn cho mobile (thay cho header bảng) -->
            <div id="cart-mobile-bar"
                class="hidden md:hidden flex items-center justify-between px-4 py-3 border-b bg-slate-50/60">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input
                        type="checkbox"
                        id="select-all-checkbox-mobile"
                        checked
                        onchange="toggleSelectAll(this.checked)"
                        class="w-5 h-5 accent-[#0066cc] cursor-pointer">
                    Chọn tất cả
                </label>
                <button onclick="clearCart()"
                        class="text-red-500 text-sm font-medium hover:text-red-700">
                    Xóa hết
                </button>
            </div>

            <!-- Giỏ trống -->
            <div id="empty-cart-view"
                class="hidden min-h-[320px] p-10 flex-col items-center justify-center text-center">
                <span class="material-symbols-outlined text-[70px] text-slate-300">
                    shopping_cart
                </span>
                <div class="font-bold text-xl mt-4 text-slate-700">
                    Giỏ hàng trống
                </div>
                <div class="text-slate-500 mt-2">
                    Hãy thêm sản phẩm để tiếp tục mua sắm
                </div>
                <a href="/Project-Web-Programming/frontend/pages/products/category.php"
                class="mt-6 bg-[#0066cc] hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Tiếp tục mua sắm
                </a>
            </div>

            <!-- Trạng thái lỗi (khi tải dữ liệu giỏ hàng gặp sự cố) -->
            <div id="cart-error-view"
                class="hidden min-h-[200px] p-10 flex-col items-center justify-center text-center">
                <span class="material-symbols-outlined text-[60px] text-red-300">
                    error
                </span>
                <div class="font-bold text-lg mt-4 text-slate-700">
                    Đã có lỗi xảy ra khi tải giỏ hàng
                </div>
                <div class="text-slate-500 mt-2 text-sm">
                    Vui lòng tải lại trang. Nếu lỗi vẫn tiếp diễn, hãy xóa toàn bộ giỏ hàng và thử lại.
                </div>
                <button onclick="location.reload()"
                    class="mt-6 bg-[#0066cc] hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Tải lại trang
                </button>
            </div>

            <!-- Danh sách sản phẩm -->
            <div id="cart-content-view" class="hidden">
                <div id="cart-list" class="divide-y divide-slate-100">
                </div>

                <!-- Thanh thanh toán: full-width, dính liền đáy của khối, theo đúng bố cục Shopee -->
                <div class="bg-white border-t border-slate-200 px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">

                    <!-- Trái: chọn tất cả (mobile) + voucher -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 min-w-0">
                        <label class="hidden sm:flex items-center gap-2 text-sm text-slate-600 shrink-0 cursor-pointer">
                            <input
                                type="checkbox"
                                id="select-all-checkbox-bottom"
                                checked
                                onchange="toggleSelectAll(this.checked)"
                                class="w-5 h-5 accent-[#0066cc] cursor-pointer">
                            Chọn tất cả (<span id="selected-count">0</span>)
                        </label>

                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full shrink-0">
                                Freeship
                            </span>
                            <span class="flex items-center gap-1.5">
                                Voucher
                                <span class="text-xs text-slate-400">(Sắp ra mắt)</span>
                            </span>
                            <button type="button" disabled
                                    class="text-xs text-slate-400 border border-slate-200 rounded-full px-3 py-1 cursor-not-allowed shrink-0">
                                Chọn voucher
                            </button>
                        </div>
                    </div>

                    <!-- Phải: tổng tiền + nút mua hàng -->
                    <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6">
                        <div class="text-right">
                            <div class="text-xs text-slate-500">
                                Tổng thanh toán (<span id="summary-count">0</span> sản phẩm)
                            </div>
                            <div id="summary-total" class="text-2xl font-bold text-[#0066cc]">
                                0đ
                            </div>
                        </div>
                        <button onclick="goToCheckout()"
                                id="btn-checkout"
                                class="bg-[#0066cc] hover:bg-blue-700 text-white rounded-xl px-8 py-3.5 font-semibold transition whitespace-nowrap shrink-0">
                            Mua hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gợi ý sản phẩm khác -->
        <div id="recommendations-section"
            class="hidden mt-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-2xl font-bold text-slate-900">
                    Có thể bạn cũng thích
                </h2>
                <p class="text-sm text-slate-500 mt-2">
                    Một vài gợi ý từ các danh mục khác trên Chợ Cũ
                </p>
                <div id="recommendations-list"
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 mt-6">
                </div>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script src="../../assets/js/cart.js?v=20260618-2"></script>

</body>
</html>
