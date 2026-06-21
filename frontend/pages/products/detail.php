<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Chi tiết sản phẩm | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="loadProductDetail()">

    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">

        <div class="text-sm text-outline mb-6">
            <a href="../../index.php" class="hover:text-primary transition-colors">
                Trang chủ
            </a>
            <span class="mx-2">/</span>
            <span class="text-on-surface">Chi tiết sản phẩm</span>
        </div>

        <div id="product-detail-container" class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-5">
                <div id="product-image-container" class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">
                    <img
                        id="product-image"
                        src="https://placehold.co/600x600"
                        alt="Ảnh sản phẩm"
                        class="w-full aspect-square object-cover"
                    >
                </div>
            </div>

            <div class="lg:col-span-7">

                <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-6 space-y-6">

                    <div>
                        <h1
                            id="product-name"
                            class="font-headline-lg text-headline-lg text-on-background leading-tight"
                        >
                            Đang tải sản phẩm...
                        </h1>
                        <div class="text-sm text-on-surface-variant mt-2">
                            <span id="product-seller">Người bán ẩn danh</span>
                            <span class="mx-2">•</span>
                            <span id="product-category">Danh mục</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            id="product-price"
                            class="text-secondary text-[32px] font-bold"
                        >
                            ₫0
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500" id="status-dot"></span>
                        <span
                            id="product-status"
                            class="text-body-md text-on-surface-variant font-medium"
                        >
                            Còn hàng
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-body-md text-on-surface-variant border-t border-b border-outline-variant/10 py-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-outline">Danh mục:</span>
                            <span id="product-category" class="font-medium text-on-surface">Đang tải...</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-outline">Người bán:</span>
                            <span id="product-seller" class="font-medium text-on-surface">Đang tải...</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-outline">Liên hệ:</span>
                            <span id="product-seller-contact" class="font-medium text-on-surface">Đang tải...</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <h3 class="font-headline-sm text-headline-sm mb-3">
                            Mô tả sản phẩm
                        </h3>

                        <p
                            id="product-description"
                            class="text-body-md text-on-surface-variant leading-relaxed whitespace-pre-line"
                        >
                            Nội dung mô tả sẽ hiển thị tại đây...
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2 text-sm text-on-surface">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-on-surface-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-on-surface-variant">Tình trạng:</span> <span id="product-condition" class="font-medium">Đang tải...</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-on-surface-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-on-surface-variant">Sử dụng:</span> <span id="product-usage" class="font-medium">Đang tải...</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-on-surface-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="text-on-surface-variant">Phụ kiện:</span> <span id="product-accessories" class="font-medium">Đang tải...</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-on-surface-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <span class="text-on-surface-variant">Bảo hành:</span> <span id="product-warranty" class="font-medium">Đang tải...</span>
                            </div>
                        </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">

                        <button
                            id="btn-buy-now"
                            data-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
                            class="flex-1 bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide"
                        >
                            MUA NGAY
                        </button>

                        <button
                            id="btn-add-to-cart"
                            onclick="addToCart()"
                            data-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
                            class="flex-1 border border-primary text-primary py-4 rounded-xl font-headline-sm hover:bg-primary hover:text-white transition-all uppercase tracking-wide"
                        >
                            THÊM VÀO GIỎ
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <?php include '../../components/footer.php'; ?>

    <script>

    let currentProduct = null;

    async function loadProductDetail() {

        try {

            const params = new URLSearchParams(window.location.search);
            const productId = params.get("id");

            if (!productId) {
                alert("Không tìm thấy ID sản phẩm trên đường dẫn.");
                return;
            }

            // Call backend public router with query param id
            const response = await fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();

            // Nếu API trả lỗi (404, sản phẩm đã bị xóa...) thì KHÔNG gán object lỗi vào currentProduct,
            // nếu không hàm addToCart()/buyNow() sẽ vô tình đẩy { error: "..." } vào giỏ hàng
            if (!response.ok || data.error) {
                currentProduct = null;
                alert(data.error || "Không tìm thấy sản phẩm.");
                return;
            }

            // Support various response shapes
            let payload = null;
            if (Array.isArray(data) && data.length > 0) payload = data[0];
            else if (data && data.data) payload = data.data;
            else payload = data;

            // Chỉ chấp nhận khi payload thực sự có ID sản phẩm hợp lệ
            currentProduct = (payload && (payload.ID || payload.id)) ? payload : null;
            if (!currentProduct) {
                alert("Không tìm thấy sản phẩm.");
                return;
            }

            // normalize fields
            const imageField = currentProduct.Image || currentProduct.image || '';
            const title = currentProduct.Name || currentProduct.name || 'Sản phẩm chưa rõ tên';
            const priceVal = parseFloat(currentProduct.Price || currentProduct.price || 0) || 0;
            const desc = currentProduct.Description || currentProduct.description || '';
            const status = (currentProduct.Status || currentProduct.status || '').toString();
            const seller = currentProduct.SellerName || currentProduct.seller || currentProduct.seller_name || '';
            const category = currentProduct.CategoryName || currentProduct.category || currentProduct.category_name || '';

            // Update UI
            const imgEl = document.getElementById("product-image");
            if (imgEl) {
                imgEl.src = imageField ? 
                    (imageField.startsWith('http://') || imageField.startsWith('https://') ? imageField : `/Project-Web-Programming/backend/uploads/products/${imageField}`) 
                    : 'https://placehold.co/600x600';
            }

            const nameEl = document.getElementById("product-name");
            if (nameEl) nameEl.innerText = title;

            const priceEl = document.getElementById("product-price");
            if (priceEl) priceEl.innerText = `₫${priceVal.toLocaleString('vi-VN')}`;

            const descEl = document.getElementById("product-description");
            if (descEl) descEl.innerText = desc || 'Chưa có mô tả cho sản phẩm này.';

            // Tình trạng / Sử dụng / Phụ kiện / Bảo hành: ưu tiên đọc trực tiếp từ các cột
            // riêng trong CSDL (Condition_status, Used_duration, Accessories, Warranty).
            // Với các tin đăng cũ (trước khi các cột này tồn tại) thì các cột sẽ rỗng, lúc đó
            // mới thử trích xuất từ nội dung mô tả theo định dạng "Nhãn: nội dung." như fallback.
            function extractDetailFromDescription(text, label) {
                if (!text) return "";
                const regex = new RegExp(label + "\\s*:\\s*([^.]+)\\.", "i");
                const match = text.match(regex);
                return match ? match[1].trim() : "";
            }

            function resolveDetailField(directValue, fallbackLabel) {
                const direct = (directValue || '').toString().trim();
                if (direct) return direct;
                const fallback = extractDetailFromDescription(desc, fallbackLabel);
                return fallback || "Chưa cập nhật";
            }

            const conditionEl = document.getElementById("product-condition");
            if (conditionEl) conditionEl.innerText = resolveDetailField(currentProduct.Condition_status, "Tình trạng");

            const usageEl = document.getElementById("product-usage");
            if (usageEl) usageEl.innerText = resolveDetailField(currentProduct.Used_duration, "Sử dụng");

            const accessoriesEl = document.getElementById("product-accessories");
            if (accessoriesEl) accessoriesEl.innerText = resolveDetailField(currentProduct.Accessories, "Phụ kiện");

            const warrantyEl = document.getElementById("product-warranty");
            if (warrantyEl) warrantyEl.innerText = resolveDetailField(currentProduct.Warranty, "Bảo hành");

            // Thông tin liên hệ người bán (backend đã trả về SellerPhone/SellerEmail nhưng trước đây chưa hiển thị)
            const contactEl = document.getElementById("product-seller-contact");
            if (contactEl) {
                const phone = currentProduct.SellerPhone || '';
                const email = currentProduct.SellerEmail || '';
                contactEl.innerText = phone || email || 'Liên hệ qua tin nhắn';
            }

            // Update all elements that may have duplicate IDs in template
            document.querySelectorAll('#product-category').forEach(el => el.innerText = category || 'Chưa phân loại');
            document.querySelectorAll('#product-seller').forEach(el => el.innerText = seller || 'Ẩn danh');

            // Status
            const stockQty = parseInt(currentProduct.Stock_quantity || currentProduct.stock_quantity || 0);
            let statusText = `Còn hàng (Số lượng: ${stockQty})`;
            let statusColor = "bg-green-500";
            if (status === "sold") {
                statusText = "Đã bán (Số lượng: 0)";
                statusColor = "bg-red-500";
            } else if (status === "pending") {
                statusText = `Chờ duyệt (Số lượng: ${stockQty})`;
                statusColor = "bg-yellow-500";
            } else if (status === "rejected" || status === "deleted") {
                statusText = "Ngừng kinh doanh";
                statusColor = "bg-slate-400";
            }
            const statusTextEl = document.getElementById("product-status");
            if (statusTextEl) statusTextEl.innerText = statusText;
            const statusDotEl = document.getElementById("status-dot");
            if (statusDotEl) statusDotEl.className = `w-2.5 h-2.5 rounded-full ${statusColor}`;

            // Handle Buy/Add to Cart buttons state based on status
            const btnBuyNow = document.getElementById("btn-buy-now");
            const btnAddToCart = document.getElementById("btn-add-to-cart");

            if (status === "sold" || status === "pending") {
                if (btnBuyNow) {
                    btnBuyNow.disabled = true;
                    btnBuyNow.innerText = status === "sold" ? "ĐÃ BÁN" : "CHỜ DUYỆT";
                    btnBuyNow.className = "flex-1 bg-gray-400 text-white py-4 rounded-xl font-headline-sm cursor-not-allowed uppercase tracking-wide opacity-70";
                }
                if (btnAddToCart) {
                    btnAddToCart.disabled = true;
                    btnAddToCart.innerText = status === "sold" ? "ĐÃ BÁN" : "CHỜ DUYỆT";
                    btnAddToCart.className = "flex-1 border border-gray-400 text-gray-400 py-4 rounded-xl font-headline-sm cursor-not-allowed uppercase tracking-wide opacity-70";
                }
            } else {
                if (btnBuyNow) {
                    btnBuyNow.disabled = false;
                    btnBuyNow.innerText = "MUA NGAY";
                    btnBuyNow.className = "flex-1 bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide";
                }
                if (btnAddToCart) {
                    btnAddToCart.disabled = false;
                    btnAddToCart.innerText = "THÊM VÀO GIỎ";
                    btnAddToCart.className = "flex-1 border border-primary text-primary py-4 rounded-xl font-headline-sm hover:bg-primary hover:text-white transition-all uppercase tracking-wide";
                }
            }

        } catch (error) {

            console.error(error);
            alert("Lỗi kết nối máy chủ. Không thể tải thông tin chi tiết sản phẩm.");

        }

    }

    function addToCart() {

        if (!currentProduct) {
            showToast("Sản phẩm chưa được tải xong, vui lòng thử lại!", "warning");
            return;
        }

        const currentId = currentProduct.ID || currentProduct.id;
        if (!currentId) {
            showToast("Dữ liệu sản phẩm không hợp lệ, không thể thêm vào giỏ hàng.", "error");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        const isExist = cart.find(item => (item.ID || item.id) === currentId);
        if(isExist) {
            showToast("Sản phẩm này đã có sẵn trong giỏ hàng của bạn!", "info");
            return;
        }

        cart.push({ ...currentProduct, Quantity: 1 });
        localStorage.setItem("cart", JSON.stringify(cart));
        if (typeof updateNavbarCartBadge === 'function') updateNavbarCartBadge();
        showToast("Đã thêm sản phẩm vào giỏ hàng thành công!", "success");
        if (typeof updateNavCartBadge === 'function') {
            updateNavCartBadge();
        }

    }

    function buyNow() {

        if (!currentProduct) return;

        const currentId = currentProduct.ID || currentProduct.id;
        if (!currentId) {
            showToast("Dữ liệu sản phẩm không hợp lệ, không thể mua ngay.", "error");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        const isExist = cart.find(item => (item.ID || item.id) === currentId);
        if(!isExist) {
            cart.push({ ...currentProduct, Quantity: 1 });
            localStorage.setItem("cart", JSON.stringify(cart));
            if (typeof updateNavbarCartBadge === 'function') updateNavbarCartBadge();
        }

        window.location.href = `../payment/index.php?id=${encodeURIComponent(currentId)}`;

    }
    function requireLogin(message) {

        showToast(message, "warning");

        setTimeout(() => {
            window.location.href =
                "/Project-Web-Programming/frontend/pages/auth/login.php";
        }, 1500);

    }
    document.addEventListener("DOMContentLoaded", function(){

    const btnBuy = document.getElementById("btn-buy-now");

        if(btnBuy){

            btnBuy.addEventListener("click", function(){

                if(this.dataset.loggedIn !== "true"){

                    requireLogin(
                        "Vui lòng đăng nhập trước khi mua hàng!"
                    );

                    return;
                }

                buyNow();

            });

        }

    });
    document.addEventListener("DOMContentLoaded", function(){

    const btnCart = document.getElementById("btn-add-to-cart");

        if(btnCart){

            btnCart.addEventListener("click", function(){

                if(this.dataset.loggedIn !== "true"){

                    requireLogin(
                        "Vui lòng đăng nhập trước khi thêm sản phẩm vào giỏ hàng!"
                    );

                    return;
                }

                addToCart();

            });

        }

    });

    </script>

</body>
</html>