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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">

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

                    <div class="border-t border-outline-variant/20 pt-5">
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
                alert("Không tìm thấy ID sản phẩm trên đường dẫn.");
                return;
            }

            const response = await fetch(`http://localhost/api/products/${productId}`);
            const data = await response.json();

            // Lấy đúng data từ cấu trúc JSON trả về (tùy thuộc vào BE đang trả về {data: {...}} hay {...})
            currentProduct = data.data || data;

            // Cập nhật giao diện
            document.getElementById("product-image").src =
                currentProduct.image || "https://placehold.co/600x600";

            document.getElementById("product-name").innerText =
                currentProduct.name || "Sản phẩm chưa rõ tên";

            document.getElementById("product-price").innerText =
                `₫${Number(currentProduct.price).toLocaleString('vi-VN')}`;

            document.getElementById("product-description").innerText =
                currentProduct.description || "Chưa có mô tả cho sản phẩm này.";

            // Xử lý dịch trạng thái (Status)
            let statusText = "Còn hàng";
            let statusColor = "bg-green-500"; // Mặc định xanh lá
            
            if (currentProduct.status === "sold") {
                statusText = "Đã bán";
                statusColor = "bg-red-500";
            } else if (currentProduct.status === "pending") {
                statusText = "Chờ duyệt";
                statusColor = "bg-yellow-500";
            }

            document.getElementById("product-status").innerText = statusText;
            document.getElementById("status-dot").className = `w-2.5 h-2.5 rounded-full ${statusColor}`;

        } catch (error) {

            console.error(error);
            alert("Lỗi kết nối máy chủ. Không thể tải thông tin chi tiết sản phẩm.");

        }

    }

    function addToCart() {

        if (!currentProduct) {
            alert("Sản phẩm chưa được tải xong, vui lòng thử lại!");
            return;
        }

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        // Tránh lỗi mua trùng 2 món đồ độc bản
        const isExist = cart.find(item => item.id === currentProduct.id);
        if(isExist) {
            alert("Sản phẩm này đã có sẵn trong giỏ hàng của bạn!");
            return;
        }

        cart.push(currentProduct);

        localStorage.setItem("cart", JSON.stringify(cart));

        alert("Đã thêm sản phẩm vào giỏ hàng thành công!");

    }

    function buyNow() {

        if (!currentProduct) return;

        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa, nếu chưa thì mới thêm vào
        const isExist = cart.find(item => item.id === currentProduct.id);
        if(!isExist) {
            cart.push(currentProduct);
            localStorage.setItem("cart", JSON.stringify(cart));
        }

        // Chuyển hướng thẳng sang trang giỏ hàng/thanh toán
        window.location.href = "../cart/index.php";

    }

    </script>

</body>
</html>