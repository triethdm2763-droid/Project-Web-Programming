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
        <div id="order-details-container" class="hidden space-y-6">
            <!-- Stepper Progress -->
            <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-8">Trạng thái đơn hàng #<span id="txt-order-id"></span></h3>

                <div class="relative flex flex-col md:flex-row justify-between items-center gap-8 md:gap-0">
                    <!-- Progress Line (desktop) -->
                    <div class="absolute top-[21px] left-[10%] right-[10%] h-[2px] bg-slate-100 hidden md:block z-0">
                        <div id="progress-bar-line" class="h-full bg-green-500 transition-all duration-500" style="width: 0%"></div>
                    </div>

                    <!-- Step 1: Pending -->
                    <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                        <div id="step-pending" class="w-11 h-11 rounded-full flex items-center justify-center border-2 border-slate-200 bg-white text-slate-400 font-bold transition-all">
                            <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                        </div>
                        <div class="text-left md:text-center">
                            <div class="font-bold text-sm text-slate-800">Đặt hàng</div>
                            <div class="text-xs text-slate-400 mt-0.5">Chờ xử lý</div>
                        </div>
                    </div>

                    <!-- Step 2: Confirmed -->
                    <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                        <div id="step-confirmed" class="w-11 h-11 rounded-full flex items-center justify-center border-2 border-slate-200 bg-white text-slate-400 font-bold transition-all">
                            <span class="material-symbols-outlined text-[20px]">thumb_up</span>
                        </div>
                        <div class="text-left md:text-center">
                            <div class="font-bold text-sm text-slate-800">Xác nhận</div>
                            <div class="text-xs text-slate-400 mt-0.5">Người bán xác nhận</div>
                        </div>
                    </div>

                    <!-- Step 3: Completed / Success -->
                    <div class="flex md:flex-col items-center gap-4 md:gap-2 z-10 w-full md:w-auto">
                        <div id="step-completed" class="w-11 h-11 rounded-full flex items-center justify-center border-2 border-slate-200 bg-white text-slate-400 font-bold transition-all">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </div>
                        <div class="text-left md:text-center">
                            <div class="font-bold text-sm text-slate-800">Thành công</div>
                            <div class="text-xs text-slate-400 mt-0.5">Đơn hàng hoàn tất</div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Banner -->
                <div id="cancelled-banner" class="hidden mt-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined">cancel</span>
                    <span class="font-semibold text-sm">Đơn hàng này đã bị hủy bỏ.</span>
                </div>
            </div>

            <!-- Product and Details Cards -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-6">Thông tin sản phẩm</h3>
                <div class="flex gap-4">
                    <img id="product-img" src="" onerror="this.src='https://placehold.co/200x200?text=No+Image'" class="w-24 h-24 object-cover rounded-xl border border-slate-200 shrink-0">
                    <div class="flex-grow min-w-0 flex flex-col justify-between py-1">
                        <div>
                            <h4 id="product-name" class="font-bold text-slate-900 truncate text-base"></h4>
                            <p class="text-xs text-slate-400 mt-1">Đơn hàng được khởi tạo: <span id="txt-created-at" class="font-medium text-slate-600"></span></p>
                        </div>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-sm text-slate-500">Đơn giá:</span>
                            <span id="product-price" class="font-black text-xl text-[#0066cc]"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Payment Info -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Shipping details -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h4 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#0066cc]">local_shipping</span>
                        Thông tin giao hàng
                    </h4>
                    <p id="txt-shipping-address" class="text-sm text-slate-600 leading-relaxed font-medium whitespace-pre-wrap"></p>
                </div>

                <!-- Payment details -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h4 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#0066cc]">payment</span>
                        Thanh toán
                    </h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Phương thức:</span>
                            <span id="txt-payment-method" class="font-bold text-slate-700"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Trạng thái:</span>
                            <span id="txt-payment-status" class="px-2.5 py-0.5 rounded-full text-xs font-semibold"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        async function fetchOrderDetails(orderId) {
            const container = document.getElementById('order-details-container');
            const placeholder = document.getElementById('state-placeholder');

            placeholder.innerHTML = `
                <div class="flex flex-col items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0066cc] mb-4"></div>
                    <span class="text-slate-500 font-semibold text-sm">Đang truy xuất thông tin đơn hàng...</span>
                </div>
            `;
            placeholder.classList.remove('hidden');
            container.classList.add('hidden');

            try {
                const res = await fetch(`${TRACK_API_URL}?code=${encodeURIComponent(orderId)}`);
                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.error || "Không thể tìm thấy thông tin đơn hàng.");
                }

                // Render Details
                document.getElementById('txt-order-id').textContent = data.order_code || data.id;
                document.getElementById('product-name').textContent = data.product_name;
                document.getElementById('product-price').textContent = formatCurrency(data.total_price);
                document.getElementById('txt-shipping-address').textContent = data.shipping_address;
                document.getElementById('txt-created-at').textContent = new Date(data.created_at).toLocaleString('vi-VN');

                // Image Url
                const imgEl = document.getElementById('product-img');
                const imageVal = (data.product_image || '').trim();
                if (imageVal) {
                    imgEl.src = imageVal.startsWith('http') ? imageVal : PRODUCT_IMAGE_BASE + imageVal;
                } else {
                    imgEl.src = 'https://placehold.co/200x200?text=No+Image';
                }

                // Payment details
                const payMethodMap = {
                    'COD': 'Thanh toán COD (Nhận hàng trả tiền)',
                    'transfer': 'Chuyển khoản ngân hàng'
                };
                document.getElementById('txt-payment-method').textContent = payMethodMap[data.payment_method] || data.payment_method;

                const payStatusEl = document.getElementById('txt-payment-status');
                if (data.payment_status === 'success' || data.payment_status === 'completed') {
                    payStatusEl.textContent = 'Đã thanh toán';
                    payStatusEl.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200';
                } else {
                    payStatusEl.textContent = 'Chưa thanh toán';
                    payStatusEl.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
                }

                // Update Stepper progress
                updateProgressStepper(data.status);

                placeholder.classList.add('hidden');
                container.classList.remove('hidden');

            } catch (error) {
                console.error(error);
                placeholder.innerHTML = `
                    <span class="material-symbols-outlined text-red-400 text-6xl">error</span>
                    <h3 class="font-bold text-slate-700 mt-4 text-lg">Không tìm thấy đơn hàng</h3>
                    <p class="text-red-500 text-sm mt-2">${error.message || "Vui lòng kiểm tra lại mã đơn hàng."}</p>
                `;
            }
        }

        function updateProgressStepper(status) {
            const stepPending = document.getElementById('step-pending');
            const stepConfirmed = document.getElementById('step-confirmed');
            const stepCompleted = document.getElementById('step-completed');
            const progressBar = document.getElementById('progress-bar-line');
            const cancelledBanner = document.getElementById('cancelled-banner');

            // Reset classes
            const allSteps = [stepPending, stepConfirmed, stepCompleted];
            allSteps.forEach(step => {
                step.className = 'w-11 h-11 rounded-full flex items-center justify-center border-2 border-slate-200 bg-white text-slate-400 font-bold transition-all';
            });
            cancelledBanner.classList.add('hidden');
            progressBar.style.width = '0%';

            const activeClass = 'w-11 h-11 rounded-full flex items-center justify-center border-2 border-green-500 bg-green-500 text-white font-bold transition-all shadow-md shadow-green-500/20';
            const pastClass = 'w-11 h-11 rounded-full flex items-center justify-center border-2 border-green-500 bg-green-500 text-white font-bold transition-all';

            const statusLower = status.toLowerCase();

            if (statusLower === 'pending') {
                stepPending.className = activeClass;
                progressBar.style.width = '0%';
            } else if (statusLower === 'confirmed') {
                stepPending.className = pastClass;
                stepConfirmed.className = activeClass;
                progressBar.style.width = '50%';
            } else if (statusLower === 'completed' || statusLower === 'success') {
                stepPending.className = pastClass;
                stepConfirmed.className = pastClass;
                stepCompleted.className = activeClass;
                progressBar.style.width = '100%';
            } else if (statusLower === 'cancelled') {
                // If cancelled, color stepPending red/gray and show cancelled banner
                stepPending.className = 'w-11 h-11 rounded-full flex items-center justify-center border-2 border-red-500 bg-red-500 text-white font-bold transition-all';
                cancelledBanner.classList.remove('hidden');
                progressBar.style.width = '0%';
            }
        }
    </script>
</body>

</html>