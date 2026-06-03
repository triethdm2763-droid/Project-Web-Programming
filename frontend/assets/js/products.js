// frontend/assets/js/products.js

// 1. KHỞI TẠO HÀM ĐỢI GIAO DIỆN TẢI XONG (DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function() {
    
    // --- KHU VỰC 1: XỬ LÝ NÚT ĐĂNG TIN TRÊN NAVBAR ---
    const btnCreatePost = document.getElementById('btn-create-post');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', function(e) {
            e.preventDefault();
            // Đọc trạng thái đăng nhập từ nút HTML do PHP truyền sang
            const isLoggedIn = this.getAttribute('data-logged-in') === 'true';

            if (isLoggedIn) {
                // Đã đăng nhập -> Vào trang Đăng tin của Seller
                window.location.href = '/Project-Web-Programming/frontend/pages/seller/my-store.php';
            } else {
                // Chưa đăng nhập -> Bật thông báo rồi ép qua trang đăng nhập
                alert('Bạn cần phải đăng nhập tài khoản trước khi thực hiện chức năng đăng tin thanh lý đồ cũ!');
                window.location.href = '/Project-Web-Programming/frontend/pages/auth/login.php';
            }
        });
    }

    // --- KHU VỰC 2: TỰ ĐỘNG GỌI SẢN PHẨM HOẶC TRANG CHI TIẾT ---
    // Kiểm tra xem đang đứng ở Trang chủ hay Trang chi tiết sản phẩm
    if (document.getElementById('product-detail-container')) {
        // Nếu thấy container chi tiết -> Chạy hàm load trang chi tiết
        loadProductDetail();
    } else {
        // Nếu không -> Mặc định gọi danh sách sản phẩm ngoài Trang chủ
        fetchProducts();
    }

    // --- KHU VỰC 3: BẮT SỰ KIỆN TÌM KIẾM SẢN PHẨM ĐỘNG ---
    const searchInput = document.querySelector('input[placeholder="Tìm kiếm sản phẩm đồ cũ..."]');
    if (searchInput) {
        let typingTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                const query = searchInput.value.trim();
                fetchProducts(query, '');
            }, 500); // Chờ gõ xong 500ms mới gọi API
        });
    }

    // --- KHU VỰC 4: BẮT SỰ KIỆN BỘ LỌC DANH MỤC ---
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-id');
            fetchProducts('', categoryId);
        });
    });

}); // KẾT THÚC HÀM DOMContentLoaded


// ==========================================================================
// HÀM 1: GỌI API LẤY DANH SÁCH SẢN PHẨM (ĐÃ NÂNG CẤP GIỐNG ẢNH MẪU)
// ==========================================================================
function fetchProducts(searchQuery = '', categoryId = '') {
    let url = '/Project-Web-Programming/backend/public/index.php/api/products';
    let params = [];
    if (searchQuery) params.push(`search=${encodeURIComponent(searchQuery)}`);
    if (categoryId) params.push(`category_id=${categoryId}`); 
    
    if (params.length > 0) url += `?${params.join('&')}`;

    const productGrid = document.querySelector('.grid-cols-2.md\\:grid-cols-4') || document.querySelector('.grid');
    if (!productGrid) return;

    // If server already rendered the grid, skip client-side fetch to avoid overwriting
    if (productGrid.getAttribute && productGrid.getAttribute('data-server-rendered') === '1') {
        console.debug('[products.js] Server already rendered products; skipping client fetch.');
        return;
    }

    productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-on-surface-variant">Đang tải sản phẩm...</div>`;

    fetch(url)
        .then(response => response.json())
        .then(result => {
            const products = result.data || [];
            if (products.length === 0) {
                productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-outline">Không tìm thấy sản phẩm nào phù hợp.</div>`;
                return;
            }

            productGrid.innerHTML = '';
            products.forEach(product => {
                // normalize fields: accept Name/ name, Price/ price, Image/ image, ID/ id
                const name = product.Name || product.name || '';
                const id = product.ID || product.id || '';
                const priceVal = parseFloat(product.Price || product.price || 0) || 0;
                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(priceVal);
                const imageField = product.Image || product.image || '';
                const productImage = imageField ? `/Project-Web-Programming/backend/uploads/products/${imageField}` : '';

                // Xử lý hiển thị địa điểm và thời gian mặc định nếu DB chưa kịp cập nhật
                const location = product.location || 'Quận 1, TP. HCM';
                const timeAgo = product.time_ago || 'Vừa xong';

                const cardHTML = `
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-primary transition-all border border-outline-variant/20 flex flex-col group p-3 space-y-3 relative">
                        <!-- Khung ảnh sản phẩm -->
                        <div class="aspect-square bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden rounded-xl">
                            <!-- Tag Tình trạng góc trên bên trái -->
                            <span class="absolute top-2 left-2 bg-black/60 text-white font-medium text-[10px] px-2 py-0.5 rounded shadow-sm z-10">Đã qua sử dụng</span>
                            <!-- Nút Tim góc trên bên phải -->
                            <button class="absolute top-2 right-2 z-10 bg-white/80 p-1.5 rounded-full shadow-sm text-on-surface hover:text-error transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">favorite</span>
                            </button>
                            
                            ${productImage ? 
                                `<img src="${productImage}" alt="${escapeHtml(name)}" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300">` :
                                `<span class="material-symbols-outlined text-5xl select-none">image</span>`
                            }
                        </div>

                        <!-- Thông tin chữ -->
                        <div class="flex flex-col flex-grow space-y-1.5 px-1">
                            <!-- Tên sản phẩm -->
                            <h3 class="font-medium text-[14px] line-clamp-2 text-on-surface h-10 block leading-snug">
                                ${escapeHtml(name)}
                            </h3>
                            <!-- Giá tiền -->
                            <div class="text-primary font-bold text-[15px]">\${formattedPrice}</div>
                            
                            <!-- Địa điểm và Thời gian (Giao diện giống ảnh mẫu) -->
                            <div class="text-[12px] text-on-surface-variant flex items-center gap-1 pt-1">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                <span class="truncate">\${escapeHtml(location)}</span>
                            </div>
                            <div class="text-[12px] text-on-surface-variant flex items-center gap-1 pb-2">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <span>\${timeAgo}</span>
                            </div>

                            <!-- Nút Xem chi tiết riêng biệt ở đáy Card -->
                            <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${id}" 
                               class="w-full border border-primary text-primary text-center py-2 rounded-xl text-[13px] font-medium hover:bg-primary hover:text-white transition-colors block mt-auto">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                `;
                productGrid.insertAdjacentHTML('beforeend', cardHTML);
            });
        })
        .catch(err => {
            console.error(err);
            productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-error">Không thể kết nối Server Backend.</div>`;
        });
}

// ==========================================================================
// HÀM 2: GỌI API CHI TIẾT 1 SẢN PHẨM (TRANG DETAIL)
// ==========================================================================
function loadProductDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    if (!productId) return;

    // Use backend public index.php router and pass id as query param
        fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`)       
        .then(response => response.json())
        .then(result => {
            // Backend returns the product data directly (or inside result.data depending on implementation)
            const product = Array.isArray(result) ? (result[0] || null) : (result.data || result);
            if (!product) return;

            if (document.getElementById('product-name')) document.getElementById('product-name').innerText = product.name;
            if (document.getElementById('product-price')) {
                document.getElementById('product-price').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price);
            }
            if (document.getElementById('product-description')) document.getElementById('product-description').innerText = product.description;
            if (document.getElementById('product-category')) document.getElementById('product-category').innerText = product.danh_muc || 'Đồ cũ';
            if (document.getElementById('product-seller')) document.getElementById('product-seller').innerText = product.nguoi_ban || 'Thành viên Chợ Cũ';
            
            const imgContainer = document.getElementById('product-image-container');
            if (imgContainer) {
                imgContainer.innerHTML = product.image ? 
                    `<img src="/Project-Web-Programming/backend/uploads/products/\${product.image}" class="w-full h-full object-contain rounded-2xl">` :
                    `<span class="material-symbols-outlined text-6xl text-outline/50">image</span>`;
            }
        });
}

// Hàm mã hóa ký tự đặc biệt chống XSS độc hại
function escapeHtml(text) {
    return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
}