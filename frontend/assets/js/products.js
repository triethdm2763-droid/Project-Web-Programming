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
// HÀM 1: GỌI API LẤY DANH SÁCH SẢN PHẨM (TRANG CHỦ)
// ==========================================================================
function fetchProducts(searchQuery = '', categoryId = '') {
    let url = '/Project-Web-Programming/backend/api/products';
    let params = [];
    if (searchQuery) params.push(`search=${encodeURIComponent(searchQuery)}`);
    if (categoryId) params.push(`category=${categoryId}`);
    if (params.length > 0) url += `?${params.join('&')}`;

    // Tìm lưới sản phẩm dựa theo cấu trúc class Tailwind của nhóm
    const productGrid = document.querySelector('.grid-cols-2.md\\:grid-cols-4') || document.querySelector('.grid');
    if (!productGrid) return;

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
                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price);
                const productImage = product.image ? `/Project-Web-Programming/backend/uploads/products/${product.image}` : '';
                
                const cardHTML = `
                    <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${product.id}" 
                       class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-primary transition-all border border-outline-variant/10 flex flex-col group block">
                        <div class="aspect-square bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden">
                            <span class="absolute top-3 left-3 bg-tertiary text-white font-semibold text-[11px] px-2 py-1 rounded shadow-sm z-10 uppercase tracking-wider">Độc Bản</span>
                            \${productImage ? 
                                \`<img src="\${productImage}" alt="\${product.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">\` : 
                                \`<span class="material-symbols-outlined text-5xl select-none">image</span>\`
                            }
                        </div>
                        <div class="p-4 flex flex-col flex-grow space-y-2">
                            <h3 class="font-semibold text-[15px] line-clamp-2 text-on-surface group-hover:text-primary transition-colors h-11 block leading-snug">
                                \${escapeHtml(product.name)}
                            </h3>
                            <div class="text-primary font-bold text-lg">\${formattedPrice}</div>
                        </div>
                    </a>
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

    fetch(`/Project-Web-Programming/backend/api/products/\${productId}`)
        .then(response => response.json())
        .then(result => {
            const product = result.data;
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