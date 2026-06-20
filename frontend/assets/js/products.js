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
                // ĐÃ SỬA: Thay alert bằng showToast dạng cảnh báo (warning)
                showToast('Bạn cần phải đăng nhập tài khoản trước khi thực hiện chức năng đăng tin thanh lý đồ cũ!', 'warning');
                setTimeout(() => {
                    window.location.href = '/Project-Web-Programming/frontend/pages/auth/login.php';
                }, 1500); // Trì hoãn 1.5 giây để user kịp nhìn thấy thông báo đẹp
            }
        });
    }

    // --- KHU VỰC 5: TỰ ĐỘNG CHUYỂN ĐỔI 3 ẢNH NỀN BANNER ---
    const slides = document.querySelectorAll('.banner-slide');
    if (slides.length > 0) {
        let currentSlideIndex = 0;
        const slideInterval = 4000; // Cứ mỗi 4 giây (4000ms) sẽ tự động đổi ảnh 1 lần

        setInterval(function() {
            // 1. Ẩn tấm ảnh hiện tại đi (Xóa opacity-100, thêm opacity-0)
            slides[currentSlideIndex].classList.remove('opacity-100');
            slides[currentSlideIndex].classList.add('opacity-0');

            // 2. Tính toán vị trí của tấm ảnh tiếp theo (Vòng lặp từ 0 -> 1 -> 2 -> Quay lại 0)
            currentSlideIndex = (currentSlideIndex + 1) % slides.length;

            // 3. Hiện tấm ảnh tiếp theo lên (Xóa opacity-0, thêm opacity-100)
            slides[currentSlideIndex].classList.remove('opacity-0');
            slides[currentSlideIndex].classList.add('opacity-100');
        }, slideInterval);
    }
    

    // --- KHU VỰC 2: TỰ ĐỘNG GỌI SẢN PHẨM HOẶC TRANG CHI TIẾT VÀ KÊNH NGƯỜI BÁN ---
    if (document.getElementById('product-detail-container')) {
        // Nếu thấy container chi tiết -> Chạy hàm load trang chi tiết
        loadProductDetail();
    } else if (document.getElementById('seller-products-list')) {
        // KÍCH HOẠT CHO TRANG KÊNH NGƯỜI BÁN CỦA TRIẾT: Tự động chạy Tab đang bán đầu tiên
        switchSellerTab('available');
        loadSellerStats();
    } else {
        // Nếu không -> Mặc định gọi danh sách sản phẩm ngoài Trang chủ
        fetchProducts();
    }
    } else if (document.getElementById('categoryProducts')) {
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
    const categoryFilterButtons = document.querySelectorAll('.category-btn:not([data-navigate="1"])');
    categoryFilterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-id');
            fetchProducts('', categoryId);
        });
    });

}); // KẾT THÚC HÀM DOMContentLoaded


// ==========================================================================
// HÀM 1: GỌI API LẤY DANH SÁCH SẢN PHẨM TRANG CHỦ
// ==========================================================================
function fetchProducts(searchQuery = '', categoryId = '', forceRefresh = false) {
    let url = '/Project-Web-Programming/backend/public/index.php/api/products';
    let params = [];
    if (searchQuery) params.push(`search=${encodeURIComponent(searchQuery)}`);
    if (categoryId) params.push(`category_id=${categoryId}`); 
    
    if (params.length > 0) url += `?${params.join('&')}`;

    const productGrid = document.getElementById('categoryProducts') || document.querySelector('.product-grid') || document.querySelector('.grid-cols-2.md\\:grid-cols-4') || document.querySelector('.grid');
    if (!productGrid) return;

    if (productGrid.getAttribute && productGrid.getAttribute('data-server-rendered') === '1' && !forceRefresh) {
        console.debug('[products.js] Server already rendered products; starting poll to detect new items.');

        async function pollNewest() {
            try {
                const res = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                const json = await res.json();
                const arr = Array.isArray(json) ? json : (json.data || []);
                if (arr.length === 0) return;
                const newest = arr[0].created_at || arr[0].Created_at || arr[0].createdAt || null;
                const currentLast = productGrid.getAttribute('data-last-created') || null;
                if (newest && (!currentLast || newest > currentLast)) {
                    console.debug('[products.js] New product detected, reloading product list.');
                    fetchProducts(searchQuery, categoryId, true);
                }
            } catch (e) {
                console.debug('[products.js] Poll error', e);
            }
        }

        if (!productGrid._productsPollIntervalId) {
            productGrid._productsPollIntervalId = setInterval(pollNewest, 15000);
        }
        return;
    }

    productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-on-surface-variant">Đang tải sản phẩm...</div>`;

    fetch(url)
        .then(response => response.json())
        .then(result => {
            let products = Array.isArray(result) ? result : (result.data || []);

            // ================= ĐOẠN CODE: LỌC TÌM KIẾM SẢN PHẨM =================
            const urlParams = new URLSearchParams(window.location.search);
            const searchKeyword = (searchQuery || urlParams.get('search') || '').trim().toLowerCase();

            if (searchKeyword) {
                products = products.filter(product => {
                    const productName = (product.Name || product.name || '').toLowerCase();
                    return productName.includes(searchKeyword);
                });
            }

            if (products.length === 0) {
                productGrid.innerHTML = `<div class="col-span-full text-center py-8 text-outline">Không tìm thấy sản phẩm nào phù hợp.</div>`;
                return;
            }

            productGrid.innerHTML = '';
            products.forEach(product => {
                const name = product.Name || product.name || '';
                const id = product.ID || product.id || '';
                const priceVal = parseFloat(product.Price || product.price || 0) || 0;
                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(priceVal);
                const imageField = product.Image || product.image || '';
                const productImage = imageField ? 
                    (imageField.startsWith('http://') || imageField.startsWith('https://') ? imageField : `/Project-Web-Programming/backend/uploads/products/${imageField}`) 
                    : '';
                const location = product.Location || product.location || 'Quận 1, TP. HCM';

                const cardHTML = `
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-primary transition-all border border-outline-variant/20 flex flex-col group p-3 space-y-3 relative">
                        <div class="aspect-square bg-surface-container flex items-center justify-center relative text-outline/50 overflow-hidden rounded-xl">
                            <span class="absolute top-2 left-2 bg-black/60 text-white font-medium text-[10px] px-2 py-0.5 rounded shadow-sm z-10">Đã qua sử dụng</span>
                            <button class="absolute top-2 right-2 z-10 bg-white/80 p-1.5 rounded-full shadow-sm text-on-surface hover:text-error transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">favorite</span>
                            </button>
                            ${productImage ? 
                                `<img src="${productImage}" alt="${escapeHtml(name)}" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300">` :
                                `<span class="material-symbols-outlined text-5xl select-none">image</span>`
                            }
                        </div>
                        <div class="flex flex-col flex-grow space-y-1.5 px-1">
                            <h3 class="font-medium text-[14px] line-clamp-2 text-on-surface h-10 block leading-snug">
                                ${escapeHtml(name)}
                            </h3>
                            <div class="text-primary font-bold text-[15px]">${formattedPrice}</div>
                            <div class="text-[12px] text-on-surface-variant flex items-center gap-1 pt-1 pb-2">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                <span class="truncate">${escapeHtml(location)}</span>
                            </div>
                            <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${id}" 
                               class="w-full border border-primary text-primary text-center py-2 rounded-xl text-[13px] font-medium hover:bg-primary hover:text-white transition-colors block mt-auto">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                `;
                productGrid.insertAdjacentHTML('beforeend', cardHTML);
            });
            const newestTimestamp = (products[0] && (products[0].created_at || products[0].Created_at || products[0].createdAt)) || null;
            if (newestTimestamp) {
                try {
                    productGrid.setAttribute('data-last-created', newestTimestamp);
                    productGrid.setAttribute('data-server-rendered', '1');
                } catch (e) {}
            }
        })
        .catch(err => {
            console.error(err);
            // Đvector THAY alert bằng showToast lỗi
            showToast('Không thể kết nối đến Server Backend.', 'error');
        });
}

// ==========================================================================
// HÀM 2: GỌI API CHI TIẾT 1 SẢN PHẨM (TRANG DETAIL)
// ==========================================================================
function loadProductDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    if (!productId) return;

    fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`)       
        .then(response => response.json())
        .then(result => {
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
                    `<img src="/Project-Web-Programming/backend/uploads/products/${product.image}" class="w-full h-full object-contain rounded-2xl">` :
                    `<span class="material-symbols-outlined text-6xl text-outline/50">image</span>`;
            }
        });
}


// ==========================================================================
// HÀM NEW 3: THÊM VÀO ĐỂ ĐIỀU HƯỚNG TABS VÀ FETCH DATA THẬT CHO KÊNH NGƯỜI BÁN
// ==========================================================================
function switchSellerTab(status) {
    document.querySelectorAll('.seller-tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-slate-400');
    });
    const activeBtn = document.getElementById(`tab-btn-${status}`);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-slate-400');
        activeBtn.classList.add('border-blue-600', 'text-blue-600');
    }

    const container = document.getElementById('seller-products-list');
    if (!container) return;

    container.innerHTML = `<div class="text-center text-slate-400 py-12 text-sm">Đang tải danh sách sản phẩm...</div>`;

    fetch(`/Project-Web-Programming/backend/public/index.php/api/products?status=${status}`)
        .then(response => response.json())
        .then(result => {
            const products = result.data || result || [];
            
            if (products.length === 0) {
                container.innerHTML = `<div class="text-center text-slate-400 py-12 text-sm">Không có sản phẩm nào trong mục này.</div>`;
                return;
            }

            container.innerHTML = products.map(p => {
                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p.price || 0);
                
                const imgVal = p.image || p.Image || '';
                const imgSrc = imgVal ? (imgVal.startsWith('http://') || imgVal.startsWith('https://') ? imgVal : `/Project-Web-Programming/backend/uploads/products/${imgVal}`) : '';
                const imageHTML = imgSrc 
                    ? `<img src="${imgSrc}" class="w-full h-full object-cover">`
                    : `<span class="material-symbols-outlined text-2xl text-slate-300">image</span>`;

                let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                let statusLabel = 'Đang bán';
                if (status === 'pending') {
                    badgeClass = 'bg-amber-50 text-amber-700 border-amber-100';
                    statusLabel = 'Chờ duyệt';
                } else if (status === 'sold') {
                    badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                    statusLabel = 'Đã bán';
                }

                return `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 gap-4 hover:bg-slate-50/60 transition-all rounded-xl">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                                ${imageHTML}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-semibold text-slate-800 text-sm truncate">${escapeHtml(p.name || p.Name)}</h4>
                                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">schedule</span> Ngày đăng: ${p.created_at || 'Vừa xong'}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-6 shrink-0">
                            <div class="text-left sm:text-right">
                                <span class="text-sm font-bold text-orange-600 block">${formattedPrice}</span>
                                <span class="inline-block text-[10px] font-bold border px-2 py-0.5 rounded-full mt-1 ${badgeClass}">${statusLabel}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="editProduct(${p.id})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors flex items-center" title="Sửa tin">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button onclick="deleteProduct(${p.id})" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors flex items-center" title="Xóa tin">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        })
        .catch(err => {
            console.error("Lỗi đồng bộ Kênh người bán:", err);
            // Đvector THAY alert bằng showToast lỗi
            showToast('Không thể kết nối đến Server Backend.', 'error');
        });
}

function editProduct(id) {
    window.location.href = `/Project-Web-Programming/frontend/pages/seller/edit-product.php?id=${id}`;
}

async function deleteProduct(id) {
    if (await showConfirm("Xóa tin", "Bạn có chắc chắn muốn xóa vĩnh viễn tin thanh lý này không?")) {
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/products/delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ id: id })
            });
            let data = await res.json();
            if (res.ok && !data.error) {
                showToast("🎉 Xóa sản phẩm thành công!", "success");
                const activeTabBtn = document.querySelector('.seller-tab-btn.text-blue-600');
                const activeTab = activeTabBtn ? activeTabBtn.id.replace('tab-btn-', '') : 'available';
                switchSellerTab(activeTab);
                if (typeof loadSellerStats === 'function') {
                    loadSellerStats();
                }
            } else {
                showAlert("Xóa thất bại", data.error || "Không thể xóa sản phẩm", "error");
            }
        } catch (err) {
            console.error(err);
            showAlert("Lỗi hệ thống", "Lỗi kết nối khi gửi yêu cầu xóa sản phẩm.", "error");
        }
// Đvector THAY alert bằng showToast dạng thành công (success)
function deleteProduct(id) {
    if (confirm("Bạn có chắc chắn muốn xóa vĩnh viễn tin thanh lý này không?")) {
        showToast('Đã gửi yêu cầu xóa sản phẩm mang mã ID: ' + id, 'success');
    }
}


// Lưu ý: hàm showToast() dùng chung cho toàn bộ trang web đã được định nghĩa
// sẵn trong frontend/assets/js/ui-helpers.js (được header.php include ở mọi trang),
// nên không định nghĩa lại ở đây để tránh xung đột giao diện thông báo giữa các trang.

// --- CÁC HÀM BỔ TRỢ HỆ THỐNG ---
function escapeHtml(text) {
    return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
}

function formatTimeAgo(timestamp) {
    try {
        const parts = timestamp.split(' ');
        const datePart = parts[0];
        const timePart = parts[1] || '00:00:00';
        const [y, m, d] = datePart.split('-').map(Number);
        const [hh, mm, ss] = timePart.split(':').map(Number);
        const dt = new Date(y, m - 1, d, hh, mm, ss);
        const diff = Date.now() - dt.getTime();
        const sec = Math.floor(diff / 1000);
        if (sec < 60) return 'Vừa xong';
        const min = Math.floor(sec / 60);
        if (min < 60) return `${min} phút trước`;
        const hrs = Math.floor(min / 60);
        if (hrs < 24) return `${hrs} giờ trước`;
        const days = Math.floor(hrs / 24);
        return `${days} ngày trước`;
    } catch (e) {
        return 'Vừa xong';
    }
}

function loadSellerStats() {
    fetch('/Project-Web-Programming/backend/public/index.php/api/seller/stats')
        .then(res => res.json())
        .then(data => {
            if (data && !data.error) {
                const revEl = document.getElementById("seller-revenue");
                if (revEl) {
                    revEl.innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.total_revenue || 0);
                }
                const ordEl = document.getElementById("seller-delivered-orders");
                if (ordEl) {
                    ordEl.innerText = `${data.delivered_orders || 0} đơn`;
                }
                const availableEl = document.getElementById("count-available");
                if (availableEl) availableEl.innerText = data.available_count || 0;

                const pendingEl = document.getElementById("count-pending");
                if (pendingEl) pendingEl.innerText = data.pending_count || 0;

                const soldEl = document.getElementById("count-sold");
                if (soldEl) soldEl.innerText = data.sold_count || 0;
            }
        })
        .catch(err => console.error("Error loading seller stats:", err));
}
}
