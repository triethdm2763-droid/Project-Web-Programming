<?php
require_once __DIR__ . '/../../components/session.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Thanh toán đơn hàng | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-[#f5f5f5] font-body-md text-on-surface min-h-screen flex flex-col" onload="initPage()">
    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Thanh toán đơn hàng</h1>
            <p class="text-slate-500 mt-2">
                Vui lòng kiểm tra lại thông tin trước khi đặt hàng. Mỗi sản phẩm trong giỏ sẽ được tạo thành một đơn hàng riêng.
            </p>
        </div>

        <div id="payment-form-step" class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-bold text-lg mb-5">Địa chỉ nhận hàng</h2>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Họ và tên</label>
                        <input type="text" id="fullname" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Nhập họ tên người nhận">
                    </div>
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="block mb-2 text-sm text-slate-600">Số điện thoại</label>
                            <input type="text" id="phone" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Nhập số điện thoại">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm text-slate-600">Email</label>
                            <input type="email" id="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Nhập email">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm text-slate-600">Địa chỉ giao hàng</label>
                        <textarea id="address" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Số nhà, tên đường..."></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-bold text-lg mb-6">Sản phẩm (<span id="product-count">0</span>)</h2>
                <div id="product-list" class="divide-y divide-slate-100">
                    <div class="py-4 text-slate-400 text-sm">Đang tải...</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-bold text-lg mb-5">Phương thức thanh toán</h2>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 cursor-pointer">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <span>Thanh toán khi nhận hàng (COD)</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 cursor-pointer">
                        <input type="radio" name="payment_method" value="transfer">
                        <span>Chuyển khoản ngân hàng</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div>
                        <div class="text-slate-500">Tổng thanh toán</div>
                        <div id="total-pay" class="text-4xl font-bold text-[#0066cc]">₫0</div>
                    </div>
                    <div class="flex gap-4">
                        <button onclick="goBack()" class="rounded-2xl border border-slate-200 px-6 py-3">Quay lại</button>
                        <button id="btn-place-order" onclick="buyNow()" class="rounded-2xl bg-[#0066cc] text-white px-8 py-3">Đặt hàng</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="payment-qr-step" class="hidden">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h2 class="text-2xl font-bold text-slate-900">Thanh toán chuyển khoản</h2>
            </div>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-8">
                    <h3 class="font-bold text-lg mb-6">Quét mã QR</h3>
                    <div class="flex justify-center">
                        <img id="qr-img" src="" class="w-[320px] h-[320px] object-contain rounded-2xl border border-slate-200">
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <div class="text-slate-500 text-sm mb-2">Ngân hàng</div>
                        <div class="flex justify-between items-center">
                            <span id="bank-name-only" class="font-semibold text-lg">Vietcombank</span>
                            <button onclick="copyToClipboard('bank-name-only')" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Sao chép</button>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <div class="text-slate-500 text-sm mb-2">Số tài khoản</div>
                        <div class="flex justify-between items-center">
                            <span id="bank-account-number" class="font-bold text-2xl tracking-wider">0071000123456</span>
                            <button onclick="copyToClipboard('bank-account-number')" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">Sao chép</button>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl text-slate-700">
                        ✔ Chuyển khoản đúng số tiền và nội dung.<br>
                        ✔ Nhấn nút bên dưới sau khi chuyển thành công.
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button onclick="backToForm()" class="flex-1 rounded-2xl border border-slate-200 px-6 py-3">Quay lại</button>
                        <button id="btn-confirm-paid" onclick="confirmPaid()" class="flex-1 rounded-2xl bg-[#0066cc] text-white px-8 py-3">Tôi đã thanh toán</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        let currentProducts = [];
        let currentQRImage = "";
        let isLoggedIn = false;

        const PRODUCT_IMAGE_BASE = '/Project-Web-Programming/backend/uploads/products/';
        const PRODUCTS_API_URL = '/Project-Web-Programming/backend/public/index.php/api/products';

        function resolveImageUrl(imageValue) {
            const value = (imageValue || '').toString().trim();
            if (!value) return 'https://placehold.co/200x200?text=No+Image';
            if (value.startsWith('http://') || value.startsWith('https://')) return value;
            return PRODUCT_IMAGE_BASE + value;
        }

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
        };

        function escapeHtml(text) {
            return (text === null || text === undefined) ? '' : String(text)
                .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        function goBack() {
            window.history.back();
        }

        async function initPage() {
            // 1. Load user profile details first
            await loadUserInfo();

            const singleId = getQueryParam('id');
            const multipleIds = getQueryParam('ids');

            if (!singleId && !multipleIds) {
                showToast("Không tìm thấy thông tin sản phẩm thanh toán.", "error");
                setTimeout(() => {
                    window.location.href = '../cart/index.php';
                }, 1500);
                return;
            }

            try {
                if (singleId) {
                    // Single product "Buy Now" flow
                    const response = await fetch(`${PRODUCTS_API_URL}/detail?id=${encodeURIComponent(singleId)}`);
                    if (!response.ok) throw new Error("Không thể tải thông tin sản phẩm.");
                    const product = await response.json();
                    
                    if (product.error) {
                        throw new Error(product.error);
                    }

                    // For single product buy now, default quantity is 1
                    product.Quantity = 1;
                    currentProducts = [product];
                } else if (multipleIds) {
                    // Multiple products checkout flow
                    const idsArr = multipleIds.split(',').map(id => id.trim()).filter(Boolean);
                    if (idsArr.length === 0) throw new Error("Danh sách sản phẩm trống.");

                    // Load quantities from local cart to preserve checkout counts
                    let cart = [];
                    try {
                        cart = JSON.parse(localStorage.getItem("cart")) || [];
                    } catch (e) {
                        cart = [];
                    }

                    const fetchPromises = idsArr.map(async (productId) => {
                        const res = await fetch(`${PRODUCTS_API_URL}/detail?id=${encodeURIComponent(productId)}`);
                        if (!res.ok) return null;
                        const product = await res.json();
                        if (product.error) return null;

                        // Find corresponding cart quantity
                        const cartItem = cart.find(item => String(item.ID ?? item.id) === String(productId));
                        product.Quantity = cartItem ? (parseInt(cartItem.Quantity ?? cartItem.quantity, 10) || 1) : 1;
                        return product;
                    });

                    const results = await Promise.all(fetchPromises);
                    currentProducts = results.filter(Boolean);
                }

                if (currentProducts.length === 0) {
                    throw new Error("Không có sản phẩm nào khả dụng để thanh toán.");
                }

                renderProductList();
                calculateTotal();

            } catch (error) {
                console.error(error);
                showAlert("Lỗi", error.message || "Đã xảy ra lỗi khi tải dữ liệu sản phẩm.", "error");
                document.getElementById('product-list').innerHTML = `<div class="py-4 text-red-500 text-sm">Có lỗi xảy ra: ${escapeHtml(error.message)}</div>`;
            }
        }

        async function loadUserInfo() {
            try {
                const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/me");
                if (res.ok) {
                    const data = await res.json();
                    const user = data.user;
                    if (user) {
                        isLoggedIn = true;
                        document.getElementById('fullname').value = user.Fullname || '';
                        document.getElementById('phone').value = user.Phone || '';
                        document.getElementById('email').value = user.Email || '';
                        document.getElementById('address').value = user.Address || '';
                    }
                }
            } catch (error) {
                console.error("Error loading user profile info:", error);
            }
        }

        function renderProductList() {
            const listContainer = document.getElementById('product-list');
            const countBadge = document.getElementById('product-count');

            if (!listContainer) return;

            countBadge.textContent = currentProducts.length;

            const itemsHtml = currentProducts.map(product => {
                const name = escapeHtml(product.Name || product.name || 'Sản phẩm');
                const category = escapeHtml(product.CategoryName || 'Chưa phân loại');
                const imgUrl = resolveImageUrl(product.Image || product.image);
                const price = Number(product.Price || 0);
                const quantity = parseInt(product.Quantity || 1, 10);
                const lineTotal = price * quantity;

                return `
                <div class="flex items-start md:items-center gap-4 py-4">
                    <img src="${imgUrl}" onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';" class="w-20 h-20 object-cover rounded-xl border border-slate-200 bg-slate-50 shrink-0">
                    <div class="flex-grow min-w-0">
                        <h3 class="font-semibold text-slate-900 truncate">${name}</h3>
                        <p class="text-xs text-slate-500 mt-1">Phân loại: ${category}</p>
                        <div class="flex items-center justify-between mt-2 md:mt-0">
                            <span class="text-sm text-slate-600 md:hidden">${formatCurrency(price)} x ${quantity}</span>
                            <span class="text-xs text-slate-400 hidden md:inline">Đơn giá: ${formatCurrency(price)} | Số lượng: ${quantity}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0 hidden md:block">
                        <span class="font-bold text-slate-900">${formatCurrency(lineTotal)}</span>
                    </div>
                    <div class="text-right shrink-0 md:hidden">
                        <span class="font-bold text-slate-900">${formatCurrency(lineTotal)}</span>
                    </div>
                </div>
                `;
            }).join('');

            listContainer.innerHTML = itemsHtml;
        }

        function calculateTotal() {
            let total = 0;
            currentProducts.forEach(product => {
                const price = Number(product.Price || 0);
                const quantity = parseInt(product.Quantity || 1, 10);
                total += price * quantity;
            });
            document.getElementById('total-pay').textContent = formatCurrency(total);
        }

        function buyNow() {
            const fullname = document.getElementById('fullname').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();
            const address = document.getElementById('address').value.trim();

            if (!fullname) {
                showToast("Vui lòng nhập họ tên người nhận.", "warning");
                return;
            }
            if (!phone) {
                showToast("Vui lòng nhập số điện thoại.", "warning");
                return;
            }
            if (!address || address.length < 10) {
                showToast("Địa chỉ giao hàng phải từ 10 ký tự trở lên.", "warning");
                return;
            }

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

            if (paymentMethod === 'transfer') {
                // Bank transfer payment flow
                let totalAmount = 0;
                currentProducts.forEach(p => {
                    totalAmount += Number(p.Price || 0) * parseInt(p.Quantity || 1, 10);
                });

                // Generate QR Code using VietQR
                const bankId = "vietcombank";
                const accountNo = "0071000123456";
                const addInfo = encodeURIComponent("Thanh toan don hang cho cu");
                const accountName = encodeURIComponent("CHO CU MARKETPLACE");
                
                // Show QR Screen
                document.getElementById('payment-form-step').classList.add('hidden');
                document.getElementById('payment-qr-step').classList.remove('hidden');

                const qrImg = document.getElementById('qr-img');
                qrImg.src = `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png?amount=${totalAmount}&addInfo=${addInfo}&accountName=${accountName}`;
            } else {
                // COD Flow
                placeOrders('COD');
            }
        }

        async function placeOrders(paymentMethod) {
            const fullname = document.getElementById('fullname').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();

            const btnPlaceOrder = document.getElementById('btn-place-order');
            const btnConfirmPaid = document.getElementById('btn-confirm-paid');

            if (btnPlaceOrder) btnPlaceOrder.disabled = true;
            if (btnConfirmPaid) btnConfirmPaid.disabled = true;

            showToast("Đang tạo đơn hàng, vui lòng đợi...", "info");

            let createdOrderIds = [];

            try {
                // Sequentially create order for each product in checkout list
                for (let i = 0; i < currentProducts.length; i++) {
                    const product = currentProducts[i];
                    const payload = {
                        product_id: product.ID ?? product.id,
                        quantity: parseInt(product.Quantity || 1, 10),
                        shipping_address: address,
                        payment_method: paymentMethod,
                        fullname: fullname,
                        phone: phone
                    };

                    const response = await fetch("/Project-Web-Programming/backend/public/index.php/api/orders", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(payload)
                    });

                    const resJson = await response.json();
                    if (!response.ok) {
                        throw new Error(resJson.error || `Lỗi khi tạo đơn hàng cho sản phẩm ${product.Name}`);
                    }
                    if (resJson.order_code) {
                        createdOrderIds.push(resJson.order_code);
                    }
                }

                // Successful checkout! Remove successfully ordered products from localStorage cart
                let cart = [];
                try {
                    cart = JSON.parse(localStorage.getItem("cart")) || [];
                } catch (e) {
                    cart = [];
                }

                const orderedIds = currentProducts.map(p => String(p.ID ?? p.id));
                const remainingCart = cart.filter(item => !orderedIds.includes(String(item.ID ?? item.id)));
                localStorage.setItem("cart", JSON.stringify(remainingCart));

                if (typeof updateNavCartBadge === 'function') {
                    updateNavCartBadge();
                }

                showAlert("Thành công", "Đơn hàng đã được tạo thành công!", "success");
                
                setTimeout(() => {
                    const targetId = createdOrderIds.length > 0 ? createdOrderIds[0] : '';
                    window.location.href = "./track.php?id=" + encodeURIComponent(targetId);
                }, 2000);

            } catch (error) {
                console.error(error);
                showAlert("Lỗi đặt hàng", error.message || "Không thể hoàn tất đặt hàng. Vui lòng thử lại.", "error");
                if (btnPlaceOrder) btnPlaceOrder.disabled = false;
                if (btnConfirmPaid) btnConfirmPaid.disabled = false;
            }
        }

        function confirmPaid() {
            placeOrders('transfer');
        }

        function backToForm() {
            document.getElementById('payment-qr-step').classList.add('hidden');
            document.getElementById('payment-form-step').classList.remove('hidden');
        }

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                showToast("Đã sao chép vào bộ nhớ tạm!", "success");
            }).catch(err => {
                console.error('Không thể sao chép: ', err);
            });
        }
    </script>
</body>
</html>