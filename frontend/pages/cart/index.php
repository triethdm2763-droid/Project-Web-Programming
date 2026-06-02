<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Giỏ hàng của bạn | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="renderCart()">
    <?php include '../../components/navbar.php'; ?>
    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">
        
        <div class="mb-8">
            <h1 class="font-headline-lg text-headline-lg text-on-background mb-2 border-l-4 border-primary pl-3">Giỏ hàng của bạn</h1>
            <p id="cart-item-count" class="text-on-surface-variant text-body-md pl-4">Bạn đang có 0 sản phẩm trong giỏ hàng</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-8 bg-white p-6 rounded-xl border border-outline-variant/20 shadow-sm min-h-[200px]">
                <div id="cart-list" class="space-y-4"></div>
                <div class="mt-6 pt-4 border-t border-outline-variant/20 flex justify-between items-center">
                    <a href="../../index.php" class="text-primary hover:underline font-medium text-sm flex items-center gap-2">
                        ← Tiếp tục mua sắm
                    </a>
                    <button onclick="clearCart()" class="text-on-surface-variant hover:text-red-500 text-sm transition-colors">
                        Xóa toàn bộ giỏ hàng
                    </button>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white p-6 rounded-xl border border-outline-variant/20 shadow-sm space-y-4">
                <h3 class="font-headline-sm text-headline-sm text-on-background mb-4">Thông tin đặt hàng</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Họ và tên</label>
                        <input type="text" id="fullname" class="w-full px-4 py-2.5 bg-surface border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-[15px]" placeholder="Nhập họ tên người nhận">
                    </div>
                    
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại</label>
                        <input type="text" id="phone" class="w-full px-4 py-2.5 bg-surface border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-[15px]" placeholder="Nhập số điện thoại liên hệ">
                    </div>

                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ giao hàng</label>
                        <textarea id="address" rows="3" class="w-full px-4 py-2.5 bg-surface border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-[15px] resize-none" placeholder="Số nhà, tên đường, phường/xã..."></textarea>
                    </div>

                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Ghi chú (Tùy chọn)</label>
                        <input type="text" id="notes" class="w-full px-4 py-2.5 bg-surface border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-[15px]" placeholder="Ví dụ: Giao giờ hành chính">
                    </div>
                </div>

                <div class="border-t border-outline-variant/20 pt-4 mt-6 space-y-3">
                    <div class="flex justify-between text-body-md text-on-surface-variant">
                        <span id="summary-count">Tạm tính (0 sản phẩm)</span>
                        <span id="summary-subtotal">0đ</span>
                    </div>
                    <div class="flex justify-between text-body-md text-on-surface-variant">
                        <span>Phí vận chuyển</span>
                        <span class="text-green-600 font-medium">Miễn phí</span>
                    </div>
                    
                    <div class="flex justify-between items-center mt-2 pt-3 border-t border-outline-variant/20">
                        <span class="font-medium text-on-background">Tổng thanh toán</span>
                        <span id="summary-total" class="font-bold text-[24px] text-secondary">0đ</span>
                    </div>

                    <button onclick="checkout()" id="checkoutBtn" class="w-full bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide mt-4 flex justify-center items-center gap-2">
                        XÁC NHẬN ĐẶT HÀNG
                    </button>
                    
                    <p class="text-center text-[12px] text-outline mt-3">
                        Bằng cách đặt hàng, bạn đồng ý với <a href="#" class="text-primary hover:underline">Điều khoản sử dụng</a> của Chợ Cũ.
                    </p>
                </div>
            </div>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    <script src="../../assets/js/cart.js"></script>
</body>
</html>