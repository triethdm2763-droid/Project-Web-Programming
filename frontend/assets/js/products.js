// frontend/assets/js/products.js

document.addEventListener('DOMContentLoaded', function() {
    // 1. Nút Đăng tin
    const btnCreatePost = document.getElementById('btn-create-post');
    if (btnCreatePost) {
        btnCreatePost.addEventListener('click', function(e) {
            e.preventDefault();
            const isLoggedIn = this.getAttribute('data-logged-in') === 'true';
            if (isLoggedIn) {
                window.location.href = '/Project-Web-Programming/frontend/pages/seller/my-store.php';
            } else {
                showToast('Bạn cần phải đăng nhập tài khoản trước khi thực hiện chức năng đăng tin thanh lý đồ cũ!', 'warning');
                setTimeout(() => { window.location.href = '/Project-Web-Programming/frontend/pages/auth/login.php'; }, 1500);
            }
        });
    }

    // 2. Banner Slide
    const slides = document.querySelectorAll('.banner-slide');
    if (slides.length > 0) {
        let currentSlideIndex = 0;
        setInterval(function() {
            slides[currentSlideIndex].classList.remove('opacity-100');
            slides[currentSlideIndex].classList.add('opacity-0');
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;
            slides[currentSlideIndex].classList.remove('opacity-0');
            slides[currentSlideIndex].classList.add('opacity-100');
        }, 4000);
    }

    // 3. Tự động gọi API theo từng trang
    if (document.getElementById('seller-products-list')) {
        switchSellerTab('available');
        if (typeof loadSellerStats === 'function') loadSellerStats();
    } else {
        fetchProducts();
    }

    // 4. Tìm kiếm & Lọc
    const searchInput = document.querySelector('input[placeholder="Tìm kiếm sản phẩm đồ cũ..."]');
    if (searchInput) {
        let typingTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => fetchProducts(searchInput.value.trim(), ''), 500);
        });
    }
});

// State quản lý phân trang sản phẩm
let currentProductPage = 1;
const productsLimit = 8;
let lastSearchQuery = '';
let lastCategoryId = '';
let lastFilters = {};

// Hàm lấy danh sách sản phẩm
function fetchProducts(searchQuery = '', categoryId = '', forceRefresh = false, filters = {}) {
    // Nếu đổi bộ lọc hoặc từ khóa hoặc danh mục, reset về trang 1
    if (forceRefresh || searchQuery !== lastSearchQuery || categoryId !== lastCategoryId || JSON.stringify(filters) !== JSON.stringify(lastFilters)) {
        currentProductPage = 1;
    }
    
    lastSearchQuery = searchQuery;
    lastCategoryId = categoryId;
    lastFilters = { ...filters };

    let url = `/Project-Web-Programming/backend/public/index.php/api/products?search=${encodeURIComponent(searchQuery)}&category_id=${categoryId !== null && categoryId !== undefined ? categoryId : ''}`;
    
    if (filters.sort) {
        url += `&sort=${encodeURIComponent(filters.sort)}`;
    }
    if (filters.minPrice) {
        url += `&min_price=${encodeURIComponent(filters.minPrice)}`;
    }
    if (filters.maxPrice) {
        url += `&max_price=${encodeURIComponent(filters.maxPrice)}`;
    }
    if (filters.location) {
        url += `&location=${encodeURIComponent(filters.location)}`;
    }
    if (filters.condition_status) {
        url += `&condition_status=${encodeURIComponent(filters.condition_status)}`;
    }

    const productGrid = document.querySelector('.product-grid') || document.querySelector('#categoryProducts');
    if (!productGrid) return;

    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        url += `&limit=${productsLimit}&page=${currentProductPage}`;
    }

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(result => {
            let products = Array.isArray(result) ? result : (result.data || []);
            let total = (result.total !== undefined) ? result.total : products.length;

            if (currentProductPage === 1) {
                productGrid.innerHTML = products.length ? '' : '<div class="col-span-full text-center py-8 text-outline">Không tìm thấy sản phẩm.</div>';
            }
            
            // Cập nhật nhãn số lượng kết quả nếu phần tử tồn tại
            const resultCountLabel = document.getElementById('resultCountLabel');
            if (resultCountLabel) {
                resultCountLabel.textContent = `Tìm thấy ${total} sản phẩm`;
            }
            
            products.forEach(p => {
                const img = p.Image ? (p.Image.startsWith('http') ? p.Image : `/Project-Web-Programming/backend/uploads/products/${p.Image}`) : '';
                const qty = p.Stock_quantity ?? p.stock_quantity ?? 1;
                const qtyBadge = parseInt(qty) === 1 
                    ? `<span class="bg-orange-50 text-orange-600 text-[9px] sm:text-[10px] font-bold px-1.5 py-0.5 rounded border border-orange-100 whitespace-nowrap">Độc bản</span>` 
                    : `<span class="bg-blue-50 text-blue-600 text-[9px] sm:text-[10px] font-bold px-1.5 py-0.5 rounded border border-blue-100 whitespace-nowrap">Còn ${qty}</span>`;

                productGrid.insertAdjacentHTML('beforeend', `
                    <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${p.ID || p.id}" class="bg-white/60 backdrop-blur-md p-2.5 sm:p-3.5 rounded-2xl shadow-sm border border-outline-variant/10 hover:bg-white/90 hover:shadow-md hover:border-primary/30 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-2.5">
                                <img src="${img}" class="w-full h-full object-contain group-hover:scale-[1.03] transition-transform" alt="${escapeHtml(p.Name || p.name)}">
                            </div>
                            <h3 class="font-semibold text-xs sm:text-[14px] line-clamp-2 text-slate-800 group-hover:text-primary transition-colors min-h-[32px] sm:min-h-[40px]">${escapeHtml(p.Name || p.name)}</h3>
                            <div class="mt-2 flex flex-col gap-1 sm:gap-1.5">
                                <div class="text-primary font-bold text-sm sm:text-[16px]">${new Intl.NumberFormat('vi-VN', {style:'currency', currency:'VND'}).format(p.Price || p.price)}</div>
                                <div class="flex items-center justify-start">
                                    ${qtyBadge}
                                </div>
                            </div>
                        </div>
                    </a>
                `);
            });

            // Quản lý hiển thị nút Xem thêm
            if (loadMoreBtn) {
                const loadedCount = productGrid.querySelectorAll('.bg-white').length;
                if (loadedCount < total && products.length > 0) {
                    loadMoreBtn.classList.remove('hidden');
                } else {
                    loadMoreBtn.classList.add('hidden');
                }
            }
        });
}

function loadNextProductPage() {
    currentProductPage++;
    fetchProducts(lastSearchQuery, lastCategoryId, false, lastFilters);
}

// Hàm quản lý Kênh người bán
function switchSellerTab(status) {
    const container = document.getElementById('seller-products-list');
    container.innerHTML = '<div class="text-center py-12">Đang tải...</div>';
    
    fetch(`/Project-Web-Programming/backend/public/index.php/api/products/mine?status=${encodeURIComponent(status)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(products => {
            container.innerHTML = products.length ? products.map(p => `
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="flex items-center gap-4">
                        <img src="/Project-Web-Programming/backend/uploads/products/${p.Image || p.image}" class="w-12 h-12 rounded">
                        <span class="font-bold">${p.Name || p.name}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editProduct(${p.ID || p.id})" class="text-blue-600">Sửa</button>
                        <button onclick="deleteProduct(${p.ID || p.id})" class="text-red-600">Xóa</button>
                    </div>
                </div>
            `).join('') : '<div class="text-center py-8">Không có sản phẩm nào.</div>';
        });
}

function deleteProduct(id) {
    if (!confirm("Xác nhận xóa tin này?")) return;
    fetch('/Project-Web-Programming/backend/public/index.php/api/products/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => {
        showToast('Đã xóa sản phẩm', 'success');
        switchSellerTab('available');
    });
}

function editProduct(id) { window.location.href = `/Project-Web-Programming/frontend/pages/seller/post-ad.php?id=${id}`; }

function escapeHtml(text) { return text ? String(text).replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m])) : ''; }