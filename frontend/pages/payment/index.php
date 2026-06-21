<?php
session_start();
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

        // ... [Giữ nguyên các hàm: loadUserInfo, renderProductList, calculateTotal, loadPaymentInfo, placeOrders, buyNow, confirmPaid, backToForm, copyToClipboard, renderQrCode, initPage như trong file của bạn] ...
        // Lưu ý: Đảm bảo bạn đã giữ lại hàm initPage() và các hàm logic xử lý đã được viết trong file gốc của bạn.
    </script>
</body>
</html>