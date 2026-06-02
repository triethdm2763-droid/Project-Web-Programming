// frontend/assets/js/products.js

// Đường dẫn gốc tới các cổng API của Backend 
const API_BASE_URL = '/Project-Web-Programming/backend/api/products'; 

document.addEventListener('DOMContentLoaded', function() {
    // Gọi hàm lấy sản phẩm lần đầu khi vừa tải xong trang
    fetchProducts();

    // BẮT SỰ KIỆN TÌM KIẾM 
    const searchInput = document.querySelector('input[placeholder="Tìm kiếm sản phẩm đồ cũ..."]');
    if (searchInput) {
        let typingTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            // Đợi người dùng gõ xong 500ms mới gọi API để tránh quá tải server (Debounce)
            typingTimer = setTimeout(() => {
                const query = searchInput.value.trim();
                fetchProducts(query, '');
            }, 500);
        });
    }

    // BẮT SỰ KIỆN LỌC THEO DANH MỤC 
    // Giả sử các thẻ danh mục ngoài Trang chủ có class 'category-btn' và thuộc tính 'data-id'
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-id');
            fetchProducts('', categoryId);
        });
    });
});

/**
 * Hàm gọi API lấy danh sách sản phẩm công khai và đổ lên lưới HTML
 */
function fetchProducts(searchQuery = '', categoryId = '') {
    // Tạo chuỗi Query Parameters cho URL công khai (GET /api/products)
    let url = API_BASE_URL;
    let params = [];
    if (searchQuery) params.push(`search=${encodeURIComponent(searchQuery)}`);
    if (categoryId) params.push(`category=${categoryId}`);
    if (params.length > 0) url += `?${params.join('&')}`;

    const productGrid = document.querySelector('.grid-cols-2.md:\\(grid-cols-4\\)'); // Lưới sản phẩm trang chủ
    if (!productGrid) return;

    // Hiển thị trạng thái đang tải (Loading)
    productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-on-surface-variant">Đang tải sản phẩm...</div>`;

    // Thực hiện hàm Fetch kết nối API
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Không thể kết nối API Backend');
            return response.json();
        })
        .then(result => {
            // Giả sử định dạng JSON Backend trả về có dạng { status: true, data: [...] }
            const products = result.data || [];
            
            if (products.length === 0) {
                productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-outline">Không tìm thấy sản phẩm nào phù hợp.</div>`;
                return;
            }

            // Xóa sạch lưới cũ để chuẩn bị render dữ liệu động thật
            productGrid.innerHTML = '';

            // Vòng lặp duyệt qua mảng sản phẩm đổ lên giao diện (Task 1 của Triết)
            products.forEach(product => {
                // Định dạng giá tiền VND đẹp mắt
                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price);
                // Xử lý ảnh mặc định nếu sản phẩm chưa có ảnh upload
                const productImage = product.image ? `/Project-Web-Programming/backend/uploads/products/${product.image}` : '';
                
                const cardHTML = `
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-outline-variant/10 flex flex-col group">
                        <div class="aspect-square bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden">
                            <span class="absolute top-3 left-3 bg-tertiary text-white font-semibold text-[11px] px-2 py-1 rounded shadow-sm z-10 uppercase tracking-wider">Độc Bản (SL=1)</span>
                            ${productImage ? 
                                `<img src="${productImage}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">` : 
                                `<span class="material-symbols-outlined text-5xl select-none">image</span>`
                            }
                        </div>
                        <div class="p-4 flex flex-col flex-grow space-y-2">
                            <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${product.id}" class="font-semibold text-[15px] line-clamp-2 hover:text-primary transition-colors h-11 block leading-snug">
                                ${escapeHtml(product.name)}
                            </a>
                            <div class="text-primary font-bold text-lg">${formattedPrice}</div>
                        </div>
                    </div>
                `;
                productGrid.insertAdjacentHTML('beforeend', cardHTML);
            });
        })
        .catch(error => {
            console.error('Lỗi:', error);
            productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-error">Có lỗi xảy ra khi tải dữ liệu từ máy chủ.</div>`;
        });
}

// Hàm chống mã độc XSS khi render chuỗi text do người dùng nhập
function escapeHtml(text) {
    return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
}
// HÀM XỬ LÝ RIÊNG CHO TRANG CHI TIẾT SẢN PHẨM (detail.php)
function loadProductDetail() {
    // 1. Bốc mã ID sản phẩm từ thanh URL (Ví dụ: detail.php?id=3)
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    // Nếu không phải đang ở trang chi tiết hoặc không có ID trên URL thì dừng lại
    if (!productId || !document.getElementById('product-detail-container')) return;

    // Đường dẫn API lấy chi tiết 1 món đồ cụ thể (Task BE1 Tuần 2)
    const DETAIL_API_URL = `/Project-Web-Programming/backend/api/products/${productId}`;

    // 2. Tiến hành gọi kết nối API thật từ Backend
    fetch(DETAIL_API_URL)
        .then(response => {
            if (!response.ok) throw new Error('Không thể lấy thông tin chi tiết sản phẩm');
            return response.json();
        })
        .then(result => {
            const product = result.data; // Dữ liệu sản phẩm thật từ DB bốc lên
            
            if (!product) {
                document.getElementById('product-detail-container').innerHTML = 
                    `<div class="text-center py-12 text-error">Sản phẩm không tồn tại hoặc đã bị xóa.</div>`;
                return;
            }

            // 3. Đổ dữ liệu động thật vào các phần tử HTML trên giao diện của FE2
            
            // Đổ tên sản phẩm
            const nameElem = document.getElementById('product-name');
            if (nameElem) nameElem.innerText = product.name;

            // Đổ giá tiền (định dạng VND)
            const priceElem = document.getElementById('product-price');
            if (priceElem) {
                priceElem.innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price);
            }

            // Đổ mô tả tình trạng đồ cũ
            const descElem = document.getElementById('product-description');
            if (descElem) descElem.innerText = product.description || 'Không có mô tả chi tiết cho sản phẩm này.';

            // Đổ tên Danh mục
            const categoryElem = document.getElementById('product-category');
            if (categoryElem) categoryElem.innerText = product.danh_muc || 'Đồ cũ';

            // Đổ tên Người bán (Seller)
            const sellerElem = document.getElementById('product-seller');
            if (sellerElem) sellerElem.innerText = product.nguoi_ban || 'Thành viên Chợ Cũ';

            // Xử lý hình ảnh sản phẩm thật
            const imgContainer = document.getElementById('product-image-container');
            if (imgContainer) {
                if (product.image) {
                    imgContainer.innerHTML = `<img src="/Project-Web-Programming/backend/uploads/products/${product.image}" 
                        alt="${product.name}" class="w-full h-full object-contain rounded-2xl">`;
                } else {
                    imgContainer.innerHTML = `<span class="material-symbols-outlined text-6xl text-outline/50">image</span>`;
                }
            }

            // 4. KIỂM TRA TRẠNG THÁI ĐỂ BẬT/TẮT NÚT MUA (Task nâng cao)
            const buyBtn = document.getElementById('btn-buy-now');
            if (buyBtn) {
                if (product.status === 'sold') {
                    buyBtn.innerText = 'ĐÃ BÁN ĐỨT';
                    buyBtn.disabled = true;
                    buyBtn.className = "w-full bg-outline text-white py-4 rounded-full font-bold cursor-not-allowed opacity-50 text-[16px]";
                } else {
                    // Gắn mã ID sản phẩm vào nút mua để bạn FE2 xử lý Giỏ hàng / Checkout ở task kế tiếp
                    buyBtn.setAttribute('data-product-id', product.id);
                }
            }
        })
        .catch(error => {
            console.error('Lỗi trang chi tiết:', error);
            document.getElementById('product-detail-container').innerHTML = 
                `<div class="text-center py-12 text-error">Có lỗi xảy ra khi tải thông tin sản phẩm.</div>`;
        });
}

// Bổ sung lệnh chạy hàm này vào trình bắt sự kiện DOMContentLoaded có sẵn của Triết
document.addEventListener('DOMContentLoaded', function() {
    // Các lệnh bắt sự kiện Tìm kiếm / Bộ lọc cũ của Triết giữ nguyên ở đây...
    
    // Chạy thêm hàm này để kiểm tra xem có cần load chi tiết sản phẩm không
    loadProductDetail();
});