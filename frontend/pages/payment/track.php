<?php
require_once __DIR__ . '/../../components/session.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Theo dõi đơn hàng | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-[#f5f5f5] font-body-md text-on-surface min-h-screen flex flex-col" onload="initTrackPage()">
    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-3xl mx-auto px-4 py-12 flex-grow w-full">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Theo Dõi Đơn Hàng</h1>
            <p class="text-slate-500 mt-2">Dành cho khách hàng vãng lai tra cứu trạng thái đơn hàng nhanh chóng</p>
        </div>

        <!-- Search Box -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                    <input type="text" id="search-order-id" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-[#0066cc]/20 focus:border-[#0066cc] transition-all text-sm font-semibold" placeholder="Nhập mã đơn hàng (Ví dụ: DH2606260001)">
                </div>
                <button onclick="searchOrder()" class="bg-[#0066cc] hover:bg-[#0052a3] text-white font-bold px-6 py-3 rounded-xl transition-all hover:scale-[1.02] active:scale-95 shadow-md shadow-blue-500/10">Tra cứu</button>
            </div>
        </div>

        <!-- Order Information Section -->
        <!-- Order Information Section -->
        <div id="orders-wrapper" class="hidden flex flex-col gap-10"></div>

        <!-- Empty State / Loading State / Error State -->
        <div id="state-placeholder" class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <span class="material-symbols-outlined text-slate-300 text-6xl">track_changes</span>
            <h3 class="font-bold text-slate-700 mt-4 text-lg">Tìm kiếm đơn hàng của bạn</h3>
            <p class="text-slate-400 text-sm mt-2">Vui lòng nhập mã đơn hàng của bạn vào ô tìm kiếm ở trên để cập nhật trạng thái đơn hàng của bạn.</p>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        const TRACK_API_URL = '/backend/public/index.php/api/orders/track';
        const PRODUCT_IMAGE_BASE = '/backend/uploads/products/';

        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount || 0);
        }

        async function initTrackPage() {
            const orderId = getQueryParam('id');
            if (orderId) {
                document.getElementById('search-order-id').value = orderId;
                await fetchOrderDetails(orderId);
            }
        }

        async function searchOrder() {
            const orderId = document.getElementById('search-order-id').value.trim();
            if (!orderId) {
                showToast("Vui lòng nhập mã đơn hàng.", "warning");
                return;
            }
            // Update URL without reloading page
            const newUrl = `${window.location.pathname}?id=${encodeURIComponent(orderId)}`;
            window.history.pushState({
                path: newUrl
            }, '', newUrl);
            await fetchOrderDetails(orderId);
        }

        async function fetchOrderDetails(orderIdsStr) {
            const wrapper = document.getElementById('orders-wrapper');
            const placeholder = document.getElementById('state-placeholder');

            placeholder.innerHTML = `
                <div class="flex flex-col items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0066cc] mb-4"></div>
                    <span class="text-slate-500 font-semibold text-sm">Đang truy xuất thông tin đơn hàng...</span>
                </div>
            `;
            placeholder.classList.remove('hidden');
            wrapper.classList.add('hidden');
            wrapper.innerHTML = '';

            try {
                const orderIds = orderIdsStr.split(',').map(id => id.trim()).filter(Boolean);
                if(orderIds.length === 0) throw new Error("Mã đơn hàng không hợp lệ.");

                let htmlContent = '';

                for(const orderId of orderIds) {
                    const res = await fetch(`${TRACK_API_URL}?code=${encodeURIComponent(orderId)}`);
                    const data = await res.json();

                    if (!res.ok) {
                        htmlContent += `<div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 mb-6">Không tìm thấy thông tin cho mã đơn hàng: ${escapeHtml(orderId)}</div>`;
                        continue;
                    }

                    // Render for this order
                    const imgUrl = (data.product_image || '').trim() ? (data.product_image.startsWith('http') ? data.product_image : PRODUCT_IMAGE_BASE + data.product_image) : 'https://placehold.co/200x200?text=No+Image';
                    
                    const payMethodMap = { 'COD': 'Thanh toán COD (Nhận hàng trả tiền)', 'transfer': 'Chuyển khoản ngân hàng' };
                    const paymentMethod = payMethodMap[data.payment_method] || data.payment_method;
                    
                    let payStatusClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                    let payStatusText = 'Chưa thanh toán';
                    if (data.payment_status === 'success' || data.payment_status === 'completed') {
                        payStatusClass = 'bg-green-50 text-green-700 border border-green-200';
                        payStatusText = 'Đã thanh toán';
                    }

                    // Status stepper
                    const statusLower = (data.status || '').toLowerCase();
                    let pWidth = '0%';
                    let s1 = 'border-2 border-slate-200 bg-white text-slate-400 font-bold',
                        s2 = 'border-2 border-slate-200 bg-white text-slate-400 font-bold',
                        s3 = 'border-2 border-slate-200 bg-white text-slate-400 font-bold';
                    
                    let cancelBanner = '';

                    const activeClass = 'border-2 border-green-500 bg-green-500 text-white font-bold shadow-md shadow-green-500/20';
                    const pastClass = 'border-2 border-green-500 bg-green-500 text-white font-bold';

                    if (statusLower === 'pending') {
                        s1 = activeClass;
                    } else if (statusLower === 'confirmed') {
                        s1 = pastClass; s2 = activeClass; pWidth = '50%';
                    } else if (statusLower === 'completed' || statusLower === 'success') {
                        s1 = pastClass; s2 = pastClass; s3 = activeClass; pWidth = '100%';
                    } else if (statusLower === 'cancelled') {
                        s1 = 'border-2 border-red-500 bg-red-500 text-white font-bold';
                        cancelBanner = `<div class="mt-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3"><span class="material-symbols-outlined">cancel</span><span class="font-semibold text-sm">Đơn hàng này đã bị hủy bỏ.</span></div>`;
                    }

                    htmlContent += `
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
                            <h3 class="font-bold text-slate-800 text-lg mb-8">Trạng thái đơn hàng #<span>${escapeHtml(data.order_code || data.id)}</span></h3>
                            <div class="relative flex flex-col md:flex-row justify-between items-center gap-8 md:gap-0">
                                <div class="absolute top-[21px] left-[10%] right-[10%] h-[2px] bg-slate-100 hidden md:block z-0">
                                    <div class="h-full bg-green-500 transition-all duration-500" style="width: ${pWidth}"></div>
                                </div>
                                <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                                    <div class="w-11 h-11 rounded-full flex items-center justify-center transition-all ${s1}"><span class="material-symbols-outlined text-[20px]">shopping_cart</span></div>
                                    <div class="text-left md:text-center"><div class="font-bold text-sm text-slate-800">Đặt hàng</div><div class="text-xs text-slate-400 mt-0.5">Chờ xử lý</div></div>
                                </div>
                                <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                                    <div class="w-11 h-11 rounded-full flex items-center justify-center transition-all ${s2}"><span class="material-symbols-outlined text-[20px]">thumb_up</span></div>
                                    <div class="text-left md:text-center"><div class="font-bold text-sm text-slate-800">Xác nhận</div><div class="text-xs text-slate-400 mt-0.5">Người bán xác nhận</div></div>
                                </div>
                                <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                                    <div class="w-11 h-11 rounded-full flex items-center justify-center transition-all ${s3}"><span class="material-symbols-outlined text-[20px]">check_circle</span></div>
                                    <div class="text-left md:text-center"><div class="font-bold text-sm text-slate-800">Thành công</div><div class="text-xs text-slate-400 mt-0.5">Đơn hàng hoàn tất</div></div>
                                </div>
                            </div>
                            ${cancelBanner}
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <h3 class="font-bold text-slate-800 text-lg mb-6">Thông tin sản phẩm</h3>
                            <div class="flex gap-4">
                                <img src="${imgUrl}" class="w-24 h-24 object-cover rounded-xl border border-slate-200 shrink-0">
                                <div class="flex-grow min-w-0 flex flex-col justify-between py-1">
                                    <div>
                                        <h4 class="font-bold text-slate-900 truncate text-base">${escapeHtml(data.product_name)}</h4>
                                        <p class="text-xs text-slate-400 mt-1">Đơn hàng được khởi tạo: <span class="font-medium text-slate-600">${new Date(data.created_at).toLocaleString('vi-VN')}</span></p>
                                    </div>
                                    <div class="flex items-baseline justify-between mt-2">
                                        <span class="text-sm text-slate-500">Thành tiền:</span>
                                        <span class="font-black text-xl text-[#0066cc]">${formatCurrency(data.total_price)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                                <h4 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-[#0066cc]">local_shipping</span>Thông tin giao hàng</h4>
                                <p class="text-sm text-slate-600 leading-relaxed font-medium whitespace-pre-wrap">${escapeHtml(data.shipping_address)}</p>
                            </div>
                            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                                <h4 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-[#0066cc]">payment</span>Thanh toán</h4>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between"><span class="text-slate-400">Phương thức:</span><span class="font-bold text-slate-700">${escapeHtml(paymentMethod)}</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">Trạng thái:</span><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ${payStatusClass}">${escapeHtml(payStatusText)}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="border-slate-200 border-2 my-8 last:hidden" />
                    `;
                }

                wrapper.innerHTML = htmlContent;
                placeholder.classList.add('hidden');
                wrapper.classList.remove('hidden');

            } catch (error) {
                console.error(error);
                placeholder.innerHTML = `
                    <span class="material-symbols-outlined text-red-400 text-6xl">error</span>
                    <h3 class="font-bold text-slate-700 mt-4 text-lg">Lỗi tra cứu đơn hàng</h3>
                    <p class="text-red-500 text-sm mt-2">${error.message || "Vui lòng kiểm tra lại mã đơn hàng."}</p>
                `;
            }
        }

        function escapeHtml(text) {
            return (text === null || text === undefined) ? '' : String(text)
                .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>