// ==========================================================================
// CART.JS - Logic giỏ hàng (phong cách Shopee)
// ==========================================================================

// --- 1. TIỆN ÍCH ---
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
};

const PRODUCT_IMAGE_BASE = '/Project-Web-Programming/backend/uploads/products/';
const PRODUCTS_API_URL = '/Project-Web-Programming/backend/public/index.php/api/products';
const CATEGORIES_API_URL = '/Project-Web-Programming/backend/public/index.php/api/categories';

function resolveImageUrl(imageValue) {
    const value = (imageValue || '').toString().trim();
    if (!value) return 'https://placehold.co/200x200?text=No+Image';
    if (value.startsWith('http://') || value.startsWith('https://')) return value;
    return PRODUCT_IMAGE_BASE + value;
}

function escapeHtmlCart(text) {
    return (text === null || text === undefined) ? '' : String(text)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;")
        .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Lưu lại index các sản phẩm đang được TICK chọn để thanh toán (mặc định chọn hết)
let selectedCartIndexes = new Set();
let cartSelectionInitialized = false; // chỉ tự động tick chọn hết 1 lần khi vào trang

// --- 2. ĐỌC & CHUẨN HÓA DỮ LIỆU GIỎ HÀNG TỪ LOCALSTORAGE ---
function loadCartFromStorage() {
    let cart = [];
    try {
        cart = JSON.parse(localStorage.getItem("cart")) || [];
        if (!Array.isArray(cart)) cart = [];
    } catch (e) {
        console.error('Dữ liệu giỏ hàng trong localStorage bị lỗi, sẽ đặt lại về rỗng.', e);
        cart = [];
    }

    // Tự dọn các sản phẩm không hợp lệ (không có ID, hoặc là object lỗi {error: ...})
    const validCart = cart.filter(item => item && typeof item === 'object' && (item.ID || item.id) && !item.error);

    // Đảm bảo mỗi sản phẩm có trường số lượng (Quantity) hợp lệ, mặc định 1
    validCart.forEach(item => {
        let qty = parseInt(item.Quantity ?? item.quantity, 10);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;
        item.Quantity = qty;
    });

    if (validCart.length !== cart.length || cart.some(i => i && (i.Quantity === undefined))) {
        localStorage.setItem("cart", JSON.stringify(validCart));
    }

    return validCart;
}

function saveCartToStorage(cart) {
    localStorage.setItem("cart", JSON.stringify(cart));
}

// --- 3. RENDER GIỎ HÀNG ---
function renderCart() {
    const cartList = document.getElementById("cart-list");
    if (!cartList) return; // không phải trang giỏ hàng, bỏ qua

    if (typeof updateNavbarCartBadge === 'function') updateNavbarCartBadge();

    const emptyView = document.getElementById("empty-cart-view");
    const errorView = document.getElementById("cart-error-view");
    const contentView = document.getElementById("cart-content-view");
    const tableHeader = document.getElementById("cart-table-header");
    const mobileBar = document.getElementById("cart-mobile-bar");

    try {
        const cart = loadCartFromStorage();
        const itemCountEl = document.getElementById("cart-item-count");

        // Ẩn view lỗi mỗi lần render lại thành công
        if (errorView) { errorView.classList.add("hidden"); errorView.classList.remove("flex"); }

        if (cart.length === 0) {
            // Giỏ hàng trống
            if (emptyView) { emptyView.classList.remove("hidden"); emptyView.classList.add("flex"); }
            if (contentView) contentView.classList.add("hidden");
            if (tableHeader) tableHeader.classList.add("hidden");
            if (mobileBar) mobileBar.classList.add("hidden");
            if (itemCountEl) itemCountEl.innerText = "Giỏ hàng của bạn hiện đang trống.";
            renderRecommendations(cart);
            return;
        }

        // Mặc định: CHỈ tick chọn hết 1 lần đầu khi vào trang
        if (!cartSelectionInitialized) {
            selectedCartIndexes = new Set(cart.map((_, index) => index));
            cartSelectionInitialized = true;
        } else {
            // Loại bỏ các index không còn tồn tại (phòng trường hợp giỏ hàng thay đổi từ tab khác)
            selectedCartIndexes = new Set([...selectedCartIndexes].filter(i => i < cart.length));
        }

        if (emptyView) emptyView.classList.add("hidden");
        if (contentView) contentView.classList.remove("hidden");
        if (tableHeader) tableHeader.classList.remove("hidden");
        if (mobileBar) { mobileBar.classList.remove("hidden"); mobileBar.classList.add("flex"); }
        if (itemCountEl) itemCountEl.innerText = `Bạn đang có ${cart.length} sản phẩm trong giỏ hàng`;

        const rowsHtml = cart.map((item, index) => renderCartItemRow(item, index)).join('');
        cartList.innerHTML = rowsHtml;

        syncSelectAllCheckboxes(cart);
        updateCartSummary(cart);
        renderRecommendations(cart);

    } catch (error) {
        console.error('Lỗi khi hiển thị giỏ hàng:', error);
        if (contentView) contentView.classList.add("hidden");
        if (emptyView) { emptyView.classList.add("hidden"); emptyView.classList.remove("flex"); }
        if (tableHeader) tableHeader.classList.add("hidden");
        if (mobileBar) mobileBar.classList.add("hidden");
        if (errorView) { errorView.classList.remove("hidden"); errorView.classList.add("flex"); }
    }
}

// Dựng HTML cho 1 dòng sản phẩm trong giỏ hàng
function renderCartItemRow(item, index) {
    const price = Number(item.Price ?? item.price) || 0;
    const name = escapeHtmlCart(item.Name || item.name || 'Sản phẩm');
    const category = escapeHtmlCart(item.CategoryName || item.category_name || item.category || 'Chưa phân loại');
    const imgUrl = resolveImageUrl(item.Image || item.image);
    const isChecked = selectedCartIndexes.has(index);
    const quantity = parseInt(item.Quantity, 10) || 1;
    const maxStock = Math.max(1, parseInt(item.Stock_quantity ?? item.stock_quantity, 10) || 1);
    const lineTotal = price * quantity;

    return `
    <div class="p-4 sm:p-5">
        <div class="flex items-start sm:items-center gap-3 sm:gap-4">

            <!-- checkbox -->
            <div class="shrink-0 flex items-center self-center">
                <input
                    type="checkbox"
                    ${isChecked ? 'checked' : ''}
                    onchange="toggleItemSelection(${index}, this.checked)"
                    class="w-5 h-5 accent-[#0066cc] cursor-pointer"
                    aria-label="Chọn sản phẩm ${name}">
            </div>

            <!-- ảnh -->
            <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${encodeURIComponent(item.ID ?? item.id)}" class="shrink-0">
                <img
                    src="${imgUrl}"
                    onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';"
                    class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-slate-200 bg-slate-50"
                    alt="${name}">
            </a>

            <!-- thông tin + giá (mobile gộp chung) -->
            <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">

                <div class="flex-1 min-w-0">
                    <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${encodeURIComponent(item.ID ?? item.id)}"
                       class="font-semibold text-slate-900 line-clamp-2 hover:text-[#0066cc] transition-colors">
                        ${name}
                    </a>
                    <div class="text-xs sm:text-sm text-slate-500 mt-1">
                        Phân loại: ${category}
                    </div>
                    <button
                        onclick="removeFromCart(${index})"
                        class="mt-2 inline-flex items-center gap-1 text-red-500 hover:text-red-700 text-xs sm:text-sm font-medium sm:hidden">
                        <span class="material-symbols-outlined text-[16px]">delete</span> Xóa
                    </button>
                </div>

                <!-- đơn giá -->
                <div class="hidden sm:block sm:w-28 text-center text-slate-700 font-medium shrink-0">
                    ${formatCurrency(price)}
                </div>

                <!-- số lượng -->
                <div class="flex sm:w-32 sm:justify-center items-center gap-2 shrink-0">
                    <span class="text-xs text-slate-400 sm:hidden">SL:</span>
                    <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden">
                        <button type="button"
                            onclick="changeItemQuantity(${index}, -1)"
                            ${quantity <= 1 ? 'disabled' : ''}
                            class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:text-slate-300 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                            aria-label="Giảm số lượng">
                            <span class="material-symbols-outlined text-[16px]">remove</span>
                        </button>
                        <span class="w-9 h-8 flex items-center justify-center text-sm font-medium border-x border-slate-200 select-none">
                            ${quantity}
                        </span>
                        <button type="button"
                            onclick="changeItemQuantity(${index}, 1)"
                            ${quantity >= maxStock ? 'disabled' : ''}
                            class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:text-slate-300 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                            aria-label="Tăng số lượng">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                        </button>
                    </div>
                </div>

                <!-- thành tiền + xóa (desktop) -->
                <div class="hidden sm:flex sm:w-32 flex-col items-end shrink-0 gap-2">
                    <div class="text-[#0066cc] font-bold">
                        ${formatCurrency(lineTotal)}
                    </div>
                    <button
                        onclick="removeFromCart(${index})"
                        class="inline-flex items-center gap-1 text-red-500 hover:text-red-700 text-xs font-medium">
                        <span class="material-symbols-outlined text-[15px]">delete</span> Xóa
                    </button>
                </div>

                <!-- thành tiền (mobile, hiện riêng vì đã có nút xóa ở trên) -->
                <div class="flex sm:hidden items-center justify-between mt-1">
                    <span class="text-xs text-slate-400">Thành tiền</span>
                    <span class="text-[#0066cc] font-bold">${formatCurrency(lineTotal)}</span>
                </div>
            </div>
        </div>
    </div>
    `;
}

// Đồng bộ trạng thái 3 checkbox "Chọn tất cả" (header desktop / mobile bar / thanh dưới)
function syncSelectAllCheckboxes(cart) {
    const allSelected = cart.length > 0 && selectedCartIndexes.size === cart.length;
    ['select-all-checkbox', 'select-all-checkbox-mobile', 'select-all-checkbox-bottom'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.checked = allSelected;
    });
}

// Tính lại Tạm tính / Tổng thanh toán dựa trên các sản phẩm ĐANG ĐƯỢC TICK chọn
function updateCartSummary(cart) {
    const summaryTotal = document.getElementById("summary-total");
    const summaryCount = document.getElementById("summary-count");
    const selectedCountEl = document.getElementById("selected-count");
    const checkoutBtn = document.getElementById("btn-checkout");

    let total = 0;
    let count = 0;
    cart.forEach((item, index) => {
        if (selectedCartIndexes.has(index)) {
            const price = Number(item.Price ?? item.price) || 0;
            const quantity = parseInt(item.Quantity, 10) || 1;
            total += price * quantity;
            count += 1;
        }
    });

    if (summaryTotal) summaryTotal.innerText = formatCurrency(total);
    if (summaryCount) summaryCount.innerText = count;
    if (selectedCountEl) selectedCountEl.innerText = count;

    if (checkoutBtn) {
        checkoutBtn.disabled = count === 0;
        checkoutBtn.classList.toggle('opacity-50', count === 0);
        checkoutBtn.classList.toggle('cursor-not-allowed', count === 0);
    }
}

// Tick / bỏ tick MỘT sản phẩm trong giỏ
function toggleItemSelection(index, checked) {
    if (checked) {
        selectedCartIndexes.add(index);
    } else {
        selectedCartIndexes.delete(index);
    }
    const cart = loadCartFromStorage();
    syncSelectAllCheckboxes(cart);
    updateCartSummary(cart);
}

// Tick / bỏ tick TOÀN BỘ sản phẩm trong giỏ (nút "Chọn tất cả")
function toggleSelectAll(checked) {
    const cart = loadCartFromStorage();
    selectedCartIndexes = checked ? new Set(cart.map((_, index) => index)) : new Set();
    syncSelectAllCheckboxes(cart);
    updateCartSummary(cart);
    // Cập nhật lại các checkbox từng dòng sản phẩm không cần render lại toàn bộ HTML
    document.querySelectorAll('#cart-list input[type="checkbox"]').forEach(cb => { cb.checked = checked; });
}

// Tăng / giảm số lượng một sản phẩm (delta = +1 hoặc -1)
function changeItemQuantity(index, delta) {
    const cart = loadCartFromStorage();
    const item = cart[index];
    if (!item) return;

    const maxStock = Math.max(1, parseInt(item.Stock_quantity ?? item.stock_quantity, 10) || 1);
    let newQty = (parseInt(item.Quantity, 10) || 1) + delta;

    if (newQty < 1) newQty = 1;
    if (newQty > maxStock) {
        newQty = maxStock;
        showToast(`Sản phẩm này chỉ còn ${maxStock} sản phẩm trong kho.`, "warning");
    }

    item.Quantity = newQty;
    saveCartToStorage(cart);
    renderCart();
}

// --- 4. GỢI Ý SẢN PHẨM KHÁC (lấy một vài sản phẩm từ MỖI danh mục) ---
async function renderRecommendations(cart) {
    const section = document.getElementById('recommendations-section');
    const list = document.getElementById('recommendations-list');
    if (!section || !list) return;

    section.classList.remove('hidden');
    list.innerHTML = `<div class="col-span-full text-center py-10 text-slate-400">Đang tải gợi ý sản phẩm...</div>`;

    const idsInCart = new Set(cart.map(item => String(item.ID ?? item.id)));

    try {
        // 1) Lấy danh sách danh mục
        const categoriesRes = await fetch(CATEGORIES_API_URL, { headers: { Accept: 'application/json' } });
        if (!categoriesRes.ok) throw new Error('Không thể tải danh mục');
        const categoriesData = await categoriesRes.json();
        const categories = Array.isArray(categoriesData) ? categoriesData : (categoriesData.data || []);

        let products = [];

        if (Array.isArray(categories) && categories.length > 0) {
            // 2) Với mỗi danh mục, lấy một vài sản phẩm (tối đa 2 sản phẩm / danh mục)
            const perCategoryRequests = categories.map(cat => {
                const catId = cat.ID ?? cat.id;
                return fetch(`${PRODUCTS_API_URL}?category_id=${encodeURIComponent(catId)}`, { headers: { Accept: 'application/json' } })
                    .then(res => res.ok ? res.json() : [])
                    .then(data => {
                        const items = Array.isArray(data) ? data : (data.data || []);
                        return items.filter(p => !idsInCart.has(String(p.ID ?? p.id))).slice(0, 2);
                    })
                    .catch(() => []);
            });

            const resultsByCategory = await Promise.all(perCategoryRequests);
            products = resultsByCategory.flat();
        }

        // 3) Nếu vẫn không có gợi ý nào (ví dụ chưa có danh mục), thử lấy toàn bộ sản phẩm làm phương án dự phòng
        if (products.length === 0) {
            const fallbackRes = await fetch(PRODUCTS_API_URL, { headers: { Accept: 'application/json' } });
            const fallbackData = fallbackRes.ok ? await fallbackRes.json() : [];
            const fallbackItems = Array.isArray(fallbackData) ? fallbackData : (fallbackData.data || []);
            products = fallbackItems.filter(p => !idsInCart.has(String(p.ID ?? p.id)));
        }

        if (products.length === 0) {
            list.innerHTML = `<div class="col-span-full text-center py-10 text-slate-400">Không có gợi ý nào phù hợp lúc này.</div>`;
            return;
        }

        // Xáo trộn nhẹ để gợi ý đa dạng hơn mỗi lần tải, rồi giới hạn hiển thị
        products = shuffleArray(products).slice(0, 8);

        list.innerHTML = products.map(renderRecommendationCard).join('');

    } catch (error) {
        console.error('Lỗi khi tải gợi ý sản phẩm:', error);
        list.innerHTML = `<div class="col-span-full text-center py-10 text-red-500">Không thể tải gợi ý sản phẩm. Vui lòng thử lại sau.</div>`;
    }
}

function renderRecommendationCard(product) {
    const name = escapeHtmlCart(product.Name || product.name || 'Sản phẩm');
    const price = Number(product.Price ?? product.price) || 0;
    const imgUrl = resolveImageUrl(product.Image || product.image);
    const productId = product.ID ?? product.id;
    return `
        <a href="/Project-Web-Programming/frontend/pages/products/detail.php?id=${encodeURIComponent(productId)}"
           class="group bg-slate-50 rounded-2xl border border-slate-200 p-3 flex flex-col gap-3 hover:shadow-md hover:border-[#0066cc]/40 transition-all">
            <div class="aspect-square overflow-hidden rounded-xl bg-white border border-slate-100">
                <img src="${imgUrl}"
                     onerror="this.onerror=null;this.src='https://placehold.co/300x300?text=No+Image';"
                     alt="${name}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm text-slate-900 line-clamp-2 mb-1">${name}</div>
                <div class="text-[#0066cc] font-bold text-sm">${formatCurrency(price)}</div>
            </div>
        </a>
    `;
}

function shuffleArray(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

// --- 5. XỬ LÝ DỮ LIỆU GIỎ HÀNG ---
function removeFromCart(index) {
    let cart = loadCartFromStorage();
    cart.splice(index, 1);
    saveCartToStorage(cart);
    selectedCartIndexes = new Set(); // reset lựa chọn vì index các sản phẩm còn lại đã đổi
    cartSelectionInitialized = false; // để renderCart() tự tick chọn lại toàn bộ giỏ hàng mới
    renderCart();
    showToast("Đã xóa sản phẩm khỏi giỏ hàng!", "info");
}

function clearCart() {
    if (!confirm('Bạn có chắc muốn xóa toàn bộ sản phẩm trong giỏ hàng?')) return;
    localStorage.removeItem("cart");
    selectedCartIndexes = new Set();
    cartSelectionInitialized = false;
    renderCart();
    showToast("Đã xóa toàn bộ giỏ hàng!", "success");
}

// --- 6. CHUYỂN HƯỚNG SANG TRANG PAYMENT ---
// Thay vì đặt hàng ngay tại giỏ, ta chuyển sang trang Payment
function goToCheckout() {
    let cart = loadCartFromStorage();
    if (cart.length === 0) {
        showToast("Giỏ hàng của bạn đang trống!", "warning");
        return;
    }

    // Chỉ lấy các sản phẩm đang được TICK chọn ở giỏ hàng
    const selectedItems = cart.filter((_, index) => selectedCartIndexes.has(index));
    if (selectedItems.length === 0) {
        showToast("Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!", "warning");
        return;
    }

    // Backend hiện tại (API /api/orders) chỉ xử lý 1 sản phẩm/đơn hàng,
    // nên nếu chọn nhiều sản phẩm, hệ thống sẽ thanh toán sản phẩm đầu tiên trong danh sách đã chọn.
    if (selectedItems.length > 1) {
        showToast("Hệ thống hiện chỉ hỗ trợ thanh toán 1 sản phẩm/lần. Sản phẩm đầu tiên trong danh sách đã chọn sẽ được thanh toán trước.", "info");
    }

    const productId = selectedItems[0].ID ?? selectedItems[0].id;

    // Chuyển sang trang thanh toán kèm ID sản phẩm để trang đó tự gọi API lấy thông tin
    window.location.href = `../payment/index.php?id=${encodeURIComponent(productId)}`;
}
