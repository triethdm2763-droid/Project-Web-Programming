<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Chi tiết sản phẩm | Chợ Thanh Lý</title>
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
                <div id="product-image-container" class="bg-white/60 backdrop-blur-md rounded-2xl border border-outline-variant/10 shadow-sm overflow-hidden">
                    <img
                        id="product-image"
                        src="https://placehold.co/600x600"
                        alt="Ảnh sản phẩm"
                        class="w-full aspect-square object-cover"
                    >
                </div>
            </div>

            <div class="lg:col-span-7">

                <div class="bg-white/60 backdrop-blur-md rounded-2xl border border-outline-variant/10 shadow-sm p-6 space-y-6">

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

                    <!-- Chọn số lượng & Thanh kéo -->
                    <div id="quantity-selector-container" class="flex flex-col gap-3 py-3 border-t border-b border-outline-variant/10">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Số lượng:</span>
                            <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 shadow-sm overflow-hidden">
                                <button type="button" onclick="changeDetailQuantity(-1)" class="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-all font-bold select-none text-lg">-</button>
                                <input type="text" id="detail-qty-input" value="1" readonly class="w-14 text-center bg-transparent border-none outline-none font-semibold text-slate-800 select-none text-base">
                                <button type="button" onclick="changeDetailQuantity(1)" class="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-all font-bold select-none text-lg">+</button>
                            </div>
                            <span id="stock-qty-helper" class="text-sm text-outline font-medium"></span>
                        </div>
                        <!-- Thanh kéo số lượng (chỉ hiện khi stockQty > 1) -->
                        <div id="quantity-slider-wrapper" class="flex items-center gap-3 w-full max-w-[280px] mt-1 pl-[75px]">
                            <span class="text-xs text-slate-400 font-semibold select-none">1</span>
                            <input type="range" id="detail-qty-slider" min="1" max="1" value="1" oninput="syncQuantityFromSlider(this.value)" class="flex-grow h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#F97316] [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-[#F97316] [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:border [&::-webkit-slider-thumb]:border-white">
                            <span id="qty-slider-max-label" class="text-xs text-slate-400 font-semibold select-none">1</span>
                        </div>
                    </div>

                    <!-- Tính năng mới: Bộ ước tính phí vận chuyển -->
                    <div id="shipping-estimator-container" class="p-4 bg-slate-50/60 backdrop-blur-md border border-outline-variant/10 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 text-primary font-semibold text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                            <span>Ước tính phí vận chuyển & giao hàng</span>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                            <select id="shipping-province" onchange="calculateShippingFee()" class="w-full sm:w-44 px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none text-slate-700 cursor-pointer hover:border-primary/50 transition-colors">
                                <option value="hanoi">Hà Nội (Gốc)</option>
                                <option value="danang">Đà Nẵng</option>
                                <option value="hcm">TP. Hồ Chí Minh</option>
                                <option value="haiphong">Hải Phòng</option>
                                <option value="cantho">Cần Thơ</option>
                                <option value="other">Các tỉnh thành khác</option>
                            </select>
                            <div class="flex-wrap items-center text-left w-full flex gap-x-2 gap-y-1">
                                <span class="text-xs text-slate-500">Phí ship:</span>
                                <span id="shipping-fee-val" class="text-sm font-bold text-slate-800">₫15.000</span>
                                <span class="text-slate-300">|</span>
                                <span id="shipping-time-val" class="text-xs text-slate-500 font-medium">Trong ngày hoặc ngày mai</span>
                            </div>
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
                            data-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
                            class="flex-1 border border-primary text-primary py-4 rounded-xl font-headline-sm hover:bg-primary hover:text-white transition-all uppercase tracking-wide"
                        >
                            THÊM VÀO GIỎ
                        </button>

                    </div>

                </div>

        </div>
    </div>

        <!-- Sản phẩm tương tự (Đề xuất thông minh) -->
        <section id="similar-products-section" class="mt-12 bg-white/60 backdrop-blur-md rounded-2xl border border-outline-variant/10 p-6 shadow-sm hidden">
            <h3 class="text-xl font-bold text-slate-800 mb-1">Sản phẩm tương tự</h3>
            <p class="text-xs text-slate-400 mb-6">Đề xuất các mặt hàng thanh lý cùng danh mục và cùng phân khúc giá</p>
            <div id="similar-products-grid" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Tải động bằng JS -->
            </div>
        </section>

    </main>

    <?php include '../../components/footer.php'; ?>

    <script>

    const currentUserId = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null'; ?>;
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
            let statusText = stockQty === 1 ? "Còn hàng (Độc bản - SL: 1)" : `Còn hàng (Số lượng: ${stockQty})`;
            let statusColor = "bg-green-500";
            if (status === "sold") {
                statusText = "Đã bán (Số lượng: 0)";
                statusColor = "bg-red-500";
            } else if (status === "pending") {
                statusText = stockQty === 1 ? "Chờ duyệt (Độc bản - SL: 1)" : `Chờ duyệt (Số lượng: ${stockQty})`;
                statusColor = "bg-yellow-500";
            } else if (status === "rejected" || status === "deleted") {
                statusText = "Ngừng kinh doanh";
                statusColor = "bg-slate-400";
            }
            const statusTextEl = document.getElementById("product-status");
            if (statusTextEl) statusTextEl.innerText = statusText;
            const statusDotEl = document.getElementById("status-dot");
            if (statusDotEl) statusDotEl.className = `w-2.5 h-2.5 rounded-full ${statusColor}`;

            // Configure quantity selector & slider
            const qtySelectorContainer = document.getElementById("quantity-selector-container");
            const qtyInput = document.getElementById("detail-qty-input");
            const stockHelper = document.getElementById("stock-qty-helper");
            const qtySlider = document.getElementById("detail-qty-slider");
            const sliderWrapper = document.getElementById("quantity-slider-wrapper");
            const maxLabel = document.getElementById("qty-slider-max-label");
            const shippingContainer = document.getElementById("shipping-estimator-container");

            if (qtyInput) {
                qtyInput.value = 1;
                qtyInput.max = stockQty;
            }
            if (stockHelper) {
                stockHelper.innerText = `${stockQty} sản phẩm có sẵn`;
            }
            if (qtySlider) {
                qtySlider.min = 1;
                qtySlider.max = stockQty;
                qtySlider.value = 1;
            }
            if (maxLabel) {
                maxLabel.innerText = stockQty;
            }
            if (sliderWrapper) {
                if (stockQty <= 1) {
                    sliderWrapper.style.display = 'none';
                } else {
                    sliderWrapper.style.display = 'flex';
                }
            }
            if (qtySelectorContainer) {
                if (status === "sold" || status === "pending" || status === "rejected" || status === "deleted" || stockQty <= 0) {
                    qtySelectorContainer.style.display = 'none';
                    if (shippingContainer) shippingContainer.style.display = 'none';
                } else {
                    qtySelectorContainer.style.display = 'flex';
                    if (shippingContainer) shippingContainer.style.display = 'block';
                }
            }

            // Handle Buy/Add to Cart buttons state based on status
            const btnBuyNow = document.getElementById("btn-buy-now");
            const btnAddToCart = document.getElementById("btn-add-to-cart");

            const sellerId = parseInt(currentProduct.SellerID || currentProduct.Seller_ID || currentProduct.seller_id || 0);

            if (currentUserId && sellerId === currentUserId) {
                if (btnBuyNow) {
                    btnBuyNow.disabled = true;
                    btnBuyNow.innerText = "SẢN PHẨM CỦA BẠN";
                    btnBuyNow.className = "flex-1 bg-gray-400 text-white py-4 rounded-xl font-headline-sm cursor-not-allowed uppercase tracking-wide opacity-70";
                }
                if (btnAddToCart) {
                    btnAddToCart.disabled = true;
                    btnAddToCart.innerText = "SẢN PHẨM CỦA BẠN";
                    btnAddToCart.className = "flex-1 border border-gray-400 text-gray-400 py-4 rounded-xl font-headline-sm cursor-not-allowed uppercase tracking-wide opacity-70";
                }
            } else if (status === "sold" || status === "pending") {
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

            // Tải sản phẩm tương tự (Đề xuất thông minh)
            const categoryId = currentProduct.Category_ID || currentProduct.category_id;
            loadSimilarProducts(categoryId, currentProduct.ID || currentProduct.id, priceVal);

        } catch (error) {

            console.error(error);
            alert("Lỗi kết nối máy chủ. Không thể tải thông tin chi tiết sản phẩm.");

        }

    }

    function escapeHtml(text) {
        return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
    }

    async function loadSimilarProducts(categoryId, currentId, priceVal) {
        const section = document.getElementById("similar-products-section");
        const grid = document.getElementById("similar-products-grid");
        if (!section || !grid || !categoryId) return;

        try {
            const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/products?category_id=${categoryId}&limit=12`);
            if (!res.ok) return;
            const result = await res.json();
            const products = result.data || result || [];

            let similar = products.filter(p => (p.ID || p.id) !== currentId);

            // Sắp xếp đề xuất: Ưu tiên các sản phẩm có giá trị gần nhất với sản phẩm hiện tại
            similar.sort((a, b) => {
                const diffA = Math.abs((a.Price || a.price || 0) - priceVal);
                const diffB = Math.abs((b.Price || b.price || 0) - priceVal);
                return diffA - diffB;
            });

            similar = similar.slice(0, 4);

            if (similar.length === 0) {
                section.classList.add("hidden");
                return;
            }

            section.classList.remove("hidden");
            grid.innerHTML = similar.map(p => {
                const img = p.Image ? (p.Image.startsWith('http') ? p.Image : `/Project-Web-Programming/backend/uploads/products/${p.Image}`) : 'https://placehold.co/300x300';
                const priceFormatted = new Intl.NumberFormat('vi-VN').format(p.Price || p.price) + ' đ';
                const name = p.Name || p.name || 'Sản phẩm';
                const qty = p.Stock_quantity ?? p.stock_quantity ?? 1;
                const qtyBadge = parseInt(qty) === 1 
                    ? `<span class="bg-orange-50 text-orange-600 text-[10px] font-bold px-1.5 py-0.5 rounded border border-orange-100 whitespace-nowrap">Độc bản</span>` 
                    : `<span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-1.5 py-0.5 rounded border border-blue-100 whitespace-nowrap">SL: ${qty}</span>`;

                return `
                <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${p.ID || p.id}" class="bg-white/80 p-3 rounded-2xl border border-outline-variant/10 hover:border-primary/30 transition-all flex flex-col justify-between group shadow-sm hover:shadow">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl overflow-hidden mb-3">
                            <img src="${img}" class="w-full h-full object-contain group-hover:scale-[1.03] transition-transform" alt="${escapeHtml(name)}">
                        </div>
                        <h4 class="font-medium text-xs sm:text-sm line-clamp-2 text-slate-800 group-hover:text-primary transition-colors">${escapeHtml(name)}</h4>
                        <div class="flex items-center justify-between gap-1 mt-2">
                            <div class="text-primary font-bold text-xs sm:text-sm">${priceFormatted}</div>
                            ${qtyBadge}
                        </div>
                    </div>
                </a>
                `;
            }).join('');

        } catch (e) {
            console.error("Lỗi khi tải sản phẩm tương tự:", e);
        }
    }

    function syncQuantityFromSlider(val) {
        const qtyInput = document.getElementById("detail-qty-input");
        if (qtyInput) {
            qtyInput.value = val;
        }
    }

    function calculateShippingFee() {
        const province = document.getElementById("shipping-province").value;
        const feeVal = document.getElementById("shipping-fee-val");
        const timeVal = document.getElementById("shipping-time-val");
        if (!feeVal || !timeVal) return;

        let fee = 25000;
        let days = "1-2 ngày";

        if (province === "hanoi") {
            fee = 15000;
            days = "Trong ngày hoặc ngày mai";
        } else if (province === "hcm") {
            fee = 30000;
            days = "Nhận hàng sau 2-3 ngày";
        } else if (province === "danang") {
            fee = 20000;
            days = "Nhận hàng sau 1-2 ngày";
        } else if (province === "cantho") {
            fee = 35000;
            days = "Nhận hàng sau 3-4 ngày";
        } else if (province === "other") {
            fee = 40000;
            days = "Nhận hàng sau 3-5 ngày";
        }

        feeVal.innerText = `₫${fee.toLocaleString('vi-VN')}`;
        timeVal.innerText = days;
    }

    function changeDetailQuantity(delta) {
        if (!currentProduct) return;
        const stockQty = parseInt(currentProduct.Stock_quantity || currentProduct.stock_quantity || 1);
        const qtyInput = document.getElementById("detail-qty-input");
        const qtySlider = document.getElementById("detail-qty-slider");
        if (!qtyInput) return;
        let newQty = parseInt(qtyInput.value || 1) + delta;
        if (newQty < 1) newQty = 1;
        if (newQty > stockQty) {
            newQty = stockQty;
            showToast(`Sản phẩm này chỉ còn ${stockQty} sản phẩm trong kho.`, "warning");
        }
        qtyInput.value = newQty;
        if (qtySlider) {
            qtySlider.value = newQty;
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

        const sellerId = parseInt(currentProduct.SellerID || currentProduct.Seller_ID || currentProduct.seller_id || 0);
        if (currentUserId && sellerId === currentUserId) {
            showToast("Bạn không thể thêm sản phẩm của chính mình vào giỏ hàng!", "warning");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const qtyInput = document.getElementById("detail-qty-input");
        const quantityToAdd = qtyInput ? parseInt(qtyInput.value || 1) : 1;

        const isExistIndex = cart.findIndex(item => (item.ID || item.id) === currentId);
        if(isExistIndex > -1) {
            const stockQty = parseInt(currentProduct.Stock_quantity || currentProduct.stock_quantity || 1);
            const currentQty = parseInt(cart[isExistIndex].Quantity || 1);
            const newQty = currentQty + quantityToAdd;
            if (newQty > stockQty) {
                cart[isExistIndex].Quantity = stockQty;
                localStorage.setItem("cart", JSON.stringify(cart));
                showToast(`Sản phẩm đã có sẵn trong giỏ hàng. Đã cập nhật số lượng trong giỏ lên mức tối đa là ${stockQty}!`, "info");
            } else {
                cart[isExistIndex].Quantity = newQty;
                localStorage.setItem("cart", JSON.stringify(cart));
                showToast("Đã cập nhật thêm số lượng sản phẩm vào giỏ hàng!", "success");
            }
        } else {
            cart.push({ ...currentProduct, Quantity: quantityToAdd });
            localStorage.setItem("cart", JSON.stringify(cart));
            showToast("Đã thêm sản phẩm vào giỏ hàng thành công!", "success");
        }

        if (typeof updateNavbarCartBadge === 'function') updateNavbarCartBadge();
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

        const sellerId = parseInt(currentProduct.SellerID || currentProduct.Seller_ID || currentProduct.seller_id || 0);
        if (currentUserId && sellerId === currentUserId) {
            showToast("Bạn không thể mua sản phẩm của chính mình!", "warning");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const qtyInput = document.getElementById("detail-qty-input");
        const quantityToAdd = qtyInput ? parseInt(qtyInput.value || 1) : 1;

        const isExistIndex = cart.findIndex(item => (item.ID || item.id) === currentId);
        if(isExistIndex > -1) {
            const stockQty = parseInt(currentProduct.Stock_quantity || currentProduct.stock_quantity || 1);
            const currentQty = parseInt(cart[isExistIndex].Quantity || 1);
            const newQty = currentQty + quantityToAdd;
            cart[isExistIndex].Quantity = Math.min(newQty, stockQty);
            localStorage.setItem("cart", JSON.stringify(cart));
        } else {
            cart.push({ ...currentProduct, Quantity: quantityToAdd });
            localStorage.setItem("cart", JSON.stringify(cart));
        }
        if (typeof updateNavbarCartBadge === 'function') updateNavbarCartBadge();

        window.location.href = `../cart/index.php`;

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