<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Thanh toán đơn hàng | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-[#f5f5f5] font-body-md text-on-surface min-h-screen flex flex-col"
      onload="initPage()">

        <?php include '../../components/navbar.php'; ?>

        <main class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">

            <!-- tiêu đề -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h1 class="text-2xl font-bold text-slate-900">
                    Thanh toán đơn hàng
                </h1>
                <p class="text-slate-500 mt-2">
                    Vui lòng kiểm tra lại thông tin trước khi đặt hàng.
                </p>
            </div>

                <div id="payment-form-step" class="space-y-6">
                    
                    <!-- thông tin giao hàng -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-bold text-lg mb-5">
                            Địa chỉ nhận hàng
                        </h2>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block mb-2 text-sm text-slate-600">
                                    Họ và tên
                                </label>
                                <input
                                    type="text"
                                    id="fullname"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                    placeholder="Nhập họ tên người nhận">
                            </div>
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block mb-2 text-sm text-slate-600">
                                        Số điện thoại
                                    </label>
                                    <input
                                        type="text"
                                        id="phone"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                        placeholder="Nhập số điện thoại">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm text-slate-600">
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        id="email"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                        placeholder="Nhập email">
                                </div>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm text-slate-600">
                                    Địa chỉ giao hàng
                                </label>
                                <textarea
                                        id="address"
                                        rows="3"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                        placeholder="Số nhà, tên đường..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- sản phẩm -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-bold text-lg mb-6">
                            Sản phẩm
                        </h2>
                        <div class="flex flex-col md:flex-row gap-5">
                            <img
                                id="product-thumb"
                                src="https://placehold.co/400x400"
                                class="w-36 h-36 rounded-2xl object-cover border border-slate-200">
                            <div class="flex-1">
                                <h3 id="product-name"
                                    class="font-semibold text-lg text-slate-900">
                                    Đang tải...
                                </h3>
                                <div class="mt-4 text-sm text-slate-500">
                                    Số lượng : 1
                                </div>
                                <div class="mt-4">

                                    <span id="product-price"
                                        class="text-3xl font-bold text-[#0066cc]">
                                        ₫0
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- phương thức thanh toán -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-bold text-lg mb-5">
                            Phương thức thanh toán
                        </h2>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 cursor-pointer">
                                <input
                                        type="radio"
                                        name="payment_method"
                                        value="cash"
                                        checked>
                                <span>
                                    Thanh toán khi nhận hàng
                                </span>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 cursor-pointer">
                                <input
                                        type="radio"
                                        name="payment_method"
                                        value="transfer">
                                <span>
                                    Chuyển khoản ngân hàng
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- mã giảm giá -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-bold text-lg mb-5">
                            Mã giảm giá
                        </h2>
                        <div class="flex items-center justify-between rounded-2xl border border-dashed border-slate-300 p-4">
                            <span class="text-sm text-slate-500">
                                Hệ thống chưa hỗ trợ mã giảm giá (sắp ra mắt)
                            </span>
                            <button type="button" disabled
                                    class="text-sm text-slate-400 border border-slate-200 rounded-full px-4 py-2 cursor-not-allowed">
                                Chọn mã
                            </button>
                        </div>
                    </div>

                    <!-- tổng tiền -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                            <div>
                                <div class="text-slate-500">
                                    Tổng thanh toán
                                </div>
                                <div id="total-pay"
                                    class="text-4xl font-bold text-[#0066cc]">
                                    ₫0
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <button
                                        onclick="goBack()"
                                        class="rounded-2xl border border-slate-200 px-6 py-3">
                                    Quay lại
                                </button>
                                <button
                                        onclick="buyNow()"
                                        class="rounded-2xl bg-[#0066cc] text-white px-8 py-3">
                                    Đặt hàng
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="payment-qr-step" class="hidden">
                        <div class="space-y-6">

                            <!-- tiêu đề -->
                            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-slate-500">
                                            Bước 2 / 2
                                        </p>
                                        <h2 class="text-2xl font-bold text-slate-900">
                                            Thanh toán chuyển khoản
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="grid lg:grid-cols-2 gap-6">

                                <!-- QR -->
                                <div class="bg-white rounded-2xl border border-slate-200 p-8">
                                    <h3 class="font-bold text-lg mb-6">
                                        Quét mã QR
                                    </h3>
                                    <div class="flex justify-center">

                                        <img id="qr-img"
                                            src=""
                                            class="w-[320px] h-[320px] object-contain rounded-2xl border border-slate-200">
                                    </div>
                                    <div class="grid gap-4 mt-8">
                                        <button
                                            onclick="downloadQR()"
                                            class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 hover:bg-slate-100">
                                            Tải mã QR
                                        </button>
                                        <button
                                            onclick="shareQR()"
                                            class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 hover:bg-slate-100">
                                            Chia sẻ mã QR
                                        </button>
                                    </div>
                                </div>

                                <!-- thông tin chuyển khoản -->
                                <div class="space-y-5">
                                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                                        <div class="text-slate-500 text-sm mb-2">
                                            Ngân hàng
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span
                                                    id="bank-name-only"
                                                    class="font-semibold text-lg">
                                                Đang tải...
                                            </span>
                                            <button
                                                    onclick="copyToClipboard('bank-name-only')"
                                                    class="rounded-xl border border-slate-200 px-4 py-2">
                                                Sao chép
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                                        <div class="text-slate-500 text-sm mb-2">
                                            Chủ tài khoản
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span
                                                    id="bank-owner-name"
                                                    class="font-semibold text-lg uppercase">
                                                Đang tải...
                                            </span>
                                            <button
                                                    onclick="copyToClipboard('bank-owner-name')"
                                                    class="rounded-xl border border-slate-200 px-4 py-2">
                                                Sao chép
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                                        <div class="text-slate-500 text-sm mb-2">
                                            Số tài khoản
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span
                                                    id="bank-account-number"
                                                    class="font-bold text-2xl tracking-wider">
                                                Đang tải...
                                            </span>
                                            <button
                                                    onclick="copyToClipboard('bank-account-number')"
                                                    class="rounded-xl border border-slate-200 px-4 py-2">
                                                Sao chép
                                            </button>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-2xl border border-blue-200 bg-blue-50 p-6">
                                        <div class="text-slate-700 leading-8">
                                            ✔ Kiểm tra đúng số tài khoản trước khi chuyển.<br>
                                            ✔ Sau khi chuyển khoản thành công, nhấn
                                            <b>"Tôi đã thanh toán"</b>.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- nút -->
                            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                                <div class="flex flex-col md:flex-row justify-end gap-4">
                                    <button
                                            onclick="backToForm()"
                                            class="rounded-2xl border border-slate-200 px-6 py-3">
                                        Quay lại sửa thông tin
                                    </button>
                                    <button
                                            onclick="confirmPaid()"
                                            class="rounded-2xl bg-[#0066cc] text-white px-8 py-3">
                                        Tôi đã thanh toán
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </main>

        <?php include '../../components/footer.php'; ?>

        <script>
        let currentProduct = null;
        let currentQRImage = "";

        async function loadUserInfo() {
            try {
                const response = await fetch(
                    "/Project-Web-Programming/backend/public/index.php/api/auth/me",
                    {
                        credentials: "include"
                    }
                );
                const result = await response.json();
                // API /api/auth/me trả về { user: {...} } khi thành công,
                // hoặc { error: "..." } (kèm mã 401/404) khi chưa đăng nhập -> không có field 'success'/'data'
                if (!response.ok || !result.user) return;
                const user = result.user;
                document.getElementById("fullname").value =
                    user.Fullname || user.fullname || "";
                document.getElementById("phone").value =
                    user.Phone || user.phone || "";
                document.getElementById("email").value =
                    user.Email || user.email || "";
                document.getElementById("address").value =
                    user.Address || user.address || "";
            }
            catch (e) {
                console.log("Không lấy được thông tin người dùng");
            }
        }

        async function loadPaymentInfo() {
            const params = new URLSearchParams(window.location.search);
            const productId = params.get("id");
            if (!productId) {

                document.getElementById("product-name").innerText =
                    "Không tìm thấy sản phẩm";
                document.getElementById("product-price").innerText = "₫0";
                document.getElementById("total-pay").innerText = "₫0";
                document.getElementById("product-thumb").src =
                    "https://placehold.co/400x400";
                showToast("Không tìm thấy sản phẩm cần thanh toán", "error");
                return;
            }
            try {
                const response = await fetch(
                    `/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`
                );
                const data = await response.json();
                const product = data.data || data;
                currentProduct = product;
                document.getElementById("product-name").innerText =
                    product.Name || product.name;
                const price =
                    Number(product.Price || product.price).toLocaleString("vi-VN");
                document.getElementById("product-price").innerText =
                    `₫${price}`;
                document.getElementById("total-pay").innerText =
                    `₫${price}`;
                document.getElementById("product-thumb").src =
                    product.Image
                        ? `/Project-Web-Programming/backend/uploads/products/${product.Image}`
                        : "https://placehold.co/400x400";
                currentQRImage =
                    "https://placehold.co/360x360?text=QR+Thanh+Toan";
                document.getElementById("qr-img").src =
                    currentQRImage;
                document.getElementById("bank-name-only").innerText =
                    "Vietcombank - Chi nhánh TP.HCM";
                document.getElementById("bank-owner-name").innerText =
                    "NGUYEN VAN TRIET";
                document.getElementById("bank-account-number").innerText =
                    "0071000123456";
            }
            catch {
                showToast("Lỗi tải thông tin thanh toán", "error");
            }
        }

        // Kiểm tra dữ liệu địa chỉ nhận hàng trước khi đặt hàng
        // (Validator backend yêu cầu shipping_address >= 10 ký tự, xem OrderService::checkout)
        function validateShippingForm() {
            const fullname = document.getElementById("fullname").value.trim();
            const phone = document.getElementById("phone").value.trim();
            const email = document.getElementById("email").value.trim();
            const address = document.getElementById("address").value.trim();

            if (!fullname) {
                showToast("Vui lòng nhập họ và tên người nhận.", "warning");
                return null;
            }
            if (!phone) {
                showToast("Vui lòng nhập số điện thoại.", "warning");
                return null;
            }
            if (!address || address.length < 10) {
                showToast("Vui lòng nhập địa chỉ giao hàng đầy đủ (ít nhất 10 ký tự).", "warning");
                return null;
            }
            return { fullname, phone, email, address };
        }

        let isSubmittingOrder = false;

        // Gọi API thật để tạo đơn hàng (POST /api/orders), thay cho việc chỉ hiện toast giả như trước
        async function createOrder(paymentMethod, successMessage) {
            if (!currentProduct) {
                showToast("Không tìm thấy sản phẩm cần thanh toán.", "error");
                return;
            }
            const form = validateShippingForm();
            if (!form) return;

            if (isSubmittingOrder) return; // chống bấm đúp gây tạo trùng đơn hàng
            isSubmittingOrder = true;

            const productId = currentProduct.ID || currentProduct.id;

            try {
                const response = await fetch(
                    "/Project-Web-Programming/backend/public/index.php/api/orders",
                    {
                        method: "POST",
                        credentials: "include",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            product_id: productId,
                            shipping_address: form.address,
                            payment_method: paymentMethod,
                            fullname: form.fullname,
                            phone: form.phone
                        })
                    }
                );
                const result = await response.json();

                if (!response.ok) {
                    const errMsg = result.error ||
                        (result.errors ? Object.values(result.errors).flat().join(", ") : "Đặt hàng thất bại.");
                    showToast(errMsg, "error");
                    isSubmittingOrder = false;
                    return;
                }

                // Đặt hàng thành công -> xóa sản phẩm này khỏi giỏ hàng cục bộ (nếu có)
                try {
                    let cart = JSON.parse(localStorage.getItem("cart")) || [];
                    cart = cart.filter(item => (item.ID || item.id) !== productId);
                    localStorage.setItem("cart", JSON.stringify(cart));
                } catch (e) {}

                showToast(successMessage, "success");
                setTimeout(() => {
                    window.location.href = "../../index.php";
                }, 2000);
            }
            catch (e) {
                showToast("Lỗi kết nối đến máy chủ, vui lòng thử lại.", "error");
                isSubmittingOrder = false;
            }
        }

        function buyNow() {
            const method =
                document.querySelector(
                    'input[name="payment_method"]:checked'
                )?.value;

            if (!validateShippingForm()) return;

            if (method === "transfer") {
                document.getElementById("payment-form-step")
                    .classList.add("hidden");
                document.getElementById("payment-qr-step")
                    .classList.remove("hidden");
                showToast(
                    "Vui lòng quét mã QR để hoàn tất thanh toán",
                    "info"
                );
                return;
            }

            if (method === "cash") {
                // Thanh toán khi nhận hàng -> tạo đơn hàng ngay với trạng thái 'COD'
                createOrder("COD", "Đơn hàng của bạn đã được đặt thành công!");
            }
        }

        function confirmPaid() {
            // Người dùng xác nhận đã chuyển khoản -> tạo đơn hàng với phương thức chuyển khoản
            createOrder("BankTransfer", "Thanh toán thành công! Đơn hàng của bạn đã được ghi nhận.");
        }

        function backToForm() {
            document.getElementById("payment-qr-step")
                .classList.add("hidden");
            document.getElementById("payment-form-step")
                .classList.remove("hidden");
        }

        function goBack() {
            window.history.back();
        }

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text)
                .then(() => {
                    showToast("Đã sao chép", "success");
                });
        }

        function downloadQR() {
            if (!currentQRImage) return;
            const a = document.createElement("a");
            a.href = currentQRImage;
            a.download = "QR_ThanhToan.jpg";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function shareQR() {
            if (navigator.share && currentQRImage) {
                navigator.share({
                    title: "QR Thanh Toán",
                    url: currentQRImage
                });
            }
        }

        async function initPage() {
            await loadPaymentInfo();
            loadUserInfo();
        }
        </script>

</body>
</html>