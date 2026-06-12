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
            <a href="/Project-Web-Programming/frontend/pages/home/index.php" class="hover:text-primary transition-colors">
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
                        <span class="text-outline line-through text-body-md" id="old-price">
                            ₫0
                        </span>

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

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">

                        <button
                            id="btn-buy-now"
                            onclick="buyNow()"
                            class="flex-1 bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide"
                        >
                            MUA NGAY
                        </button>

                        <button
                            onclick="addToCart()"
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
                await showAlert("Lỗi đường dẫn", "Không tìm thấy ID sản phẩm trên đường dẫn.", "error");
                return;
            }

            // Call backend public router with query param id
            const response = await fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();

            // Support various response shapes
            let payload = null;
            if (Array.isArray(data) && data.length > 0) payload = data[0];
            else if (data && data.data) payload = data.data;
            else payload = data;

            currentProduct = payload || {};

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

            // Update all elements that may have duplicate IDs in template
            document.querySelectorAll('#product-category').forEach(el => el.innerText = category || 'Chưa phân loại');
            document.querySelectorAll('#product-seller').forEach(el => el.innerText = seller || 'Ẩn danh');

            // Status
            let statusText = "Còn hàng";
            let statusColor = "bg-green-500";
            if (status === "sold") {
                statusText = "Đã bán";
                statusColor = "bg-red-500";
            } else if (status === "pending") {
                statusText = "Chờ duyệt";
                statusColor = "bg-yellow-500";
            }
            const statusTextEl = document.getElementById("product-status");
            if (statusTextEl) statusTextEl.innerText = statusText;
            const statusDotEl = document.getElementById("status-dot");
            if (statusDotEl) statusDotEl.className = `w-2.5 h-2.5 rounded-full ${statusColor}`;

        } catch (error) {

            console.error(error);
            await showAlert("Lỗi kết nối", "Lỗi kết nối máy chủ. Không thể tải thông tin chi tiết sản phẩm.", "error");

        }

    }

    function addToCart() {

        if (!currentProduct) {
            showAlert("Chưa sẵn sàng", "Sản phẩm chưa được tải xong, vui lòng thử lại!", "warning");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        const currentId = currentProduct.ID || currentProduct.id;
        const isExist = cart.find(item => (item.ID || item.id) === currentId);
        if(isExist) {
            showAlert("Thông báo", "Sản phẩm này đã có sẵn trong giỏ hàng của bạn!", "info");
            return;
        }

        cart.push(currentProduct);
        localStorage.setItem("cart", JSON.stringify(cart));
        showToast("Đã thêm sản phẩm vào giỏ hàng thành công!", "success");

    }

    function buyNow() {

        if (!currentProduct) return;

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        
        const currentId = currentProduct.ID || currentProduct.id;
        const isExist = cart.find(item => (item.ID || item.id) === currentId);
        if(!isExist) {
            cart.push(currentProduct);
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        window.location.href = "../cart/index.php";

    }

    </script>

</body>
</html>