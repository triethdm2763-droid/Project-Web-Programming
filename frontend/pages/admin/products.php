<?php
require_once __DIR__ . '/../../components/session.php';
require_once '../../../backend/src/config/Database.php';
use App\Config\Database;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
$conn = Database::getInstance()->getConnection();
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = $conn->query("SELECT COUNT(*) FROM products WHERE Status IN ('active', 'available')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Quản lý sản phẩm | Admin Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-slate-50 font-body-md text-on-surface">
    <div class="flex min-h-screen">
        <?php include '../../components/sidebar.php'; ?>
        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">Quản lý sản phẩm</h1>
                <p class="text-slate-500 mt-2">Duyệt tin đăng, gỡ tin đăng vi phạm chính sách của sàn</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-outline-variant/10 flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tổng sản phẩm</p>
                        <h2 class="text-3xl font-bold text-primary mt-1"><?= (int)$totalProducts ?></h2>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]">inventory_2</span>
                    </div>
                </div>
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-outline-variant/10 flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Đang hiển thị bán</p>
                        <h2 class="text-3xl font-bold text-emerald-600 mt-1"><?= (int)$activeProducts ?></h2>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-outlined text-[28px]">check_circle</span>
                    </div>
                </div>
            </div>

            <!-- Bộ lọc và tìm kiếm -->
            <div class="bg-white/60 backdrop-blur-md p-4 rounded-2xl shadow-sm mb-6 flex flex-col sm:flex-row gap-4 border border-outline-variant/10">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input id="product-search-input" type="text" placeholder="Tìm theo tên sản phẩm hoặc người bán..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm" oninput="filterProducts()">
                </div>
                <select id="product-status-filter" class="border border-outline-variant/30 bg-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm cursor-pointer" onchange="filterProducts()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang bán</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="sold">Đã bán</option>
                    <option value="rejected">Bị từ chối</option>
                </select>
            </div>

            <!-- Bảng dữ liệu dạng gương -->
            <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm overflow-hidden border border-outline-variant/10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-100/50 text-slate-500 text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="p-4 font-semibold">Hình ảnh</th>
                                <th class="p-4 font-semibold">Tên sản phẩm</th>
                                <th class="p-4 font-semibold">Danh mục</th>
                                <th class="p-4 font-semibold">Người bán</th>
                                <th class="p-4 font-semibold">Giá</th>
                                <th class="p-4 text-center font-semibold whitespace-nowrap">Trạng thái</th>
                                <th class="p-4 text-center font-semibold whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="productTable" class="divide-y divide-slate-100/50">
                            <tr><td colspan="7" class="p-8 text-center text-slate-400">Đang tải danh sách...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Xem Chi Tiết Sản Phẩm (Admin Preview) -->
            <div id="product-detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300" onclick="if(event.target === this) closeProductDetailModal()">
                <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-slate-100 transform scale-95 transition-all duration-300" onclick="event.stopPropagation()">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 text-base">Chi tiết tin đăng sản phẩm</h3>
                        <button onclick="closeProductDetailModal()" class="w-8 h-8 rounded-full hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-6 overflow-y-auto space-y-6 text-sm text-slate-600">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Image Left -->
                            <div class="w-full aspect-square bg-slate-50 border border-slate-100 rounded-xl overflow-hidden flex items-center justify-center">
                                <img id="modal-product-image" src="" alt="Ảnh sản phẩm" class="w-full h-full object-contain">
                            </div>

                            <!-- Info Right -->
                            <div class="space-y-4">
                                <div id="modal-product-status-badge"></div>
                                <h2 id="modal-product-name" class="font-bold text-slate-800 text-base leading-snug"></h2>
                                <div class="text-primary font-black text-xl" id="modal-product-price"></div>
                                
                                <div class="space-y-2 pt-4 border-t border-slate-100 text-xs">
                                    <div><span class="text-slate-400 font-medium">Danh mục:</span> <span id="modal-product-category" class="font-semibold text-slate-700"></span></div>
                                    <div><span class="text-slate-400 font-medium">Người bán:</span> <span id="modal-product-seller" class="font-semibold text-slate-700"></span></div>
                                    <div><span class="text-slate-400 font-medium">Liên hệ:</span> <span id="modal-product-contact" class="font-semibold text-slate-700"></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Details -->
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <h4 class="font-bold text-slate-800 text-sm">Mô tả chi tiết</h4>
                            <p id="modal-product-description" class="whitespace-pre-line text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100/50 max-h-[180px] overflow-y-auto"></p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0" id="modal-product-footer-actions">
                        <!-- Nút hành động render động -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let allProducts = [];
        function escapeHtml(text) {
            return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }

        async function loadProducts() {
            try {
                const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/admin/products", { credentials: 'same-origin' });
                allProducts = await res.json();
                filterProducts();
            } catch (e) { 
                document.getElementById("productTable").innerHTML = '<tr><td colspan="7" class="p-8 text-center text-red-500">Lỗi tải dữ liệu sản phẩm</td></tr>'; 
            }
        }

        function applyInitialFilterFromUrl() {
            const search = new URLSearchParams(window.location.search).get('search');
            const status = new URLSearchParams(window.location.search).get('status');
            if (search) document.getElementById('product-search-input').value = search;
            if (status) document.getElementById('product-status-filter').value = status;
        }

        function filterProducts() {
            const keyword = document.getElementById("product-search-input").value;
            const status = document.getElementById("product-status-filter").value;

            // Update URL search parameters to keep URL and UI in perfect sync
            const url = new URL(window.location);
            if (keyword) {
                url.searchParams.set('search', keyword);
            } else {
                url.searchParams.delete('search');
            }
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.history.replaceState({}, '', url);

            const keywordLower = keyword.toLowerCase();
            let filtered = allProducts.filter(p => 
                ((p.Name || '').toLowerCase().includes(keywordLower) || (p.SellerName || '').toLowerCase().includes(keywordLower)) &&
                (!status || (status === 'active' ? (p.Status === 'active' || p.Status === 'available') : p.Status === status))
            );
            renderProducts(filtered);
        }

        function renderProducts(products) {
            const tbody = document.getElementById("productTable");
            if (!products.length) { tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-slate-400">Không tìm thấy sản phẩm phù hợp.</td></tr>'; return; }
            tbody.innerHTML = products.map(p => {
                let statusLabel = '';
                if (p.Status === 'active' || p.Status === 'available') {
                    statusLabel = '<span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-semibold border border-green-100">Đang bán</span>';
                } else if (p.Status === 'pending') {
                    statusLabel = '<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold border border-amber-100">Chờ duyệt</span>';
                } else if (p.Status === 'sold') {
                    statusLabel = '<span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold border border-blue-100">Đã bán</span>';
                } else {
                    statusLabel = `<span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-semibold border border-red-100">${escapeHtml(p.Status)}</span>`;
                }

                let actions = '';
                if (p.Status === 'pending') {
                    actions = `
                        <button onclick="updateProductStatus(${p.ID}, 'active')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold mr-1.5 transition-colors">Duyệt</button>
                        <button onclick="updateProductStatus(${p.ID}, 'rejected')" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-semibold transition-colors">Từ chối</button>
                    `;
                } else if (p.Status === 'active' || p.Status === 'available') {
                    actions = `<button onclick="updateProductStatus(${p.ID}, 'rejected')" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-semibold transition-colors">Gỡ tin</button>`;
                } else {
                    actions = `<span class="text-xs text-slate-300">—</span>`;
                }

                const imgSrc = p.Image 
                    ? (p.Image.startsWith('http') ? p.Image : '/Project-Web-Programming/backend/uploads/products/' + p.Image)
                    : '/Project-Web-Programming/frontend/assets/images/placeholder.png';

                return `<tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="p-4"><img src="${imgSrc}" class="w-12 h-12 rounded-lg object-contain border bg-slate-50 flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity" onerror="this.src='/Project-Web-Programming/frontend/assets/images/placeholder.png'" onclick="showProductDetailModal(${p.ID})"></td>
                    <td class="p-4 font-medium text-sm text-slate-800"><span class="cursor-pointer text-slate-800 hover:text-primary hover:underline font-semibold" onclick="showProductDetailModal(${p.ID})">${escapeHtml(p.Name)}</span></td>
                    <td class="p-4 text-sm text-slate-600">${escapeHtml(p.CategoryName)}</td>
                    <td class="p-4 text-sm text-slate-600">${escapeHtml(p.SellerName)}</td>
                    <td class="p-4 text-sm font-bold text-slate-800">${Number(p.Price).toLocaleString('vi-VN')} đ</td>
                    <td class="p-4 text-center whitespace-nowrap">${statusLabel}</td>
                    <td class="p-4 text-center whitespace-nowrap">${actions}</td>
                </tr>`;
            }).join('');
        }

        async function updateProductStatus(id, newStatus) {
            const confirmMsg = newStatus === 'active' ? "Xác nhận duyệt cho phép hiển thị sản phẩm này?" : "Xác nhận gỡ/từ chối sản phẩm này?";
            if (!await showConfirm("Cập nhật sản phẩm", confirmMsg)) return;
            try {
                const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/admin/products/update-status`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id, status: newStatus })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(newStatus === 'active' ? "✅ Phê duyệt thành công!" : "❌ Đã từ chối/gỡ sản phẩm thành công!", "success");
                    loadProducts();
                } else {
                    showAlert("Thất bại", result.error || "Không thể thực hiện hành động", "error");
                }
            } catch (err) {
                console.error(err);
                showAlert("Lỗi hệ thống", "Không thể kết nối đến máy chủ.", "error");
            }
        }

        // Modal Controls
        function showProductDetailModal(productId) {
            const product = allProducts.find(p => p.ID === productId);
            if (!product) return;

            // Map image
            const imgSrc = product.Image 
                ? (product.Image.startsWith('http') ? product.Image : '/Project-Web-Programming/backend/uploads/products/' + product.Image)
                : '/Project-Web-Programming/frontend/assets/images/placeholder.png';
            document.getElementById('modal-product-image').src = imgSrc;

            // Map status badge
            let badgeHtml = '';
            if (product.Status === 'active' || product.Status === 'available') {
                badgeHtml = '<span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-semibold border border-green-100">Đang bán</span>';
            } else if (product.Status === 'pending') {
                badgeHtml = '<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold border border-amber-100">Chờ duyệt</span>';
            } else if (product.Status === 'sold') {
                badgeHtml = '<span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold border border-blue-100">Đã bán</span>';
            } else {
                badgeHtml = `<span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-semibold border border-red-100">${escapeHtml(product.Status)}</span>`;
            }
            document.getElementById('modal-product-status-badge').innerHTML = badgeHtml;

            // Map text details
            document.getElementById('modal-product-name').textContent = product.Name;
            document.getElementById('modal-product-price').textContent = Number(product.Price).toLocaleString('vi-VN') + ' đ';
            document.getElementById('modal-product-category').textContent = product.CategoryName || 'Khác';
            document.getElementById('modal-product-seller').textContent = product.SellerName || 'Ẩn danh';
            
            // Map seller contacts
            const contactPhone = product.SellerPhone || '';
            const contactEmail = product.SellerEmail || '';
            document.getElementById('modal-product-contact').textContent = [contactPhone, contactEmail].filter(Boolean).join(' / ') || 'Chưa cung cấp';

            // Map description
            document.getElementById('modal-product-description').textContent = product.Description || 'Chưa có mô tả chi tiết cho sản phẩm này.';

            // Map footer actions dynamically
            const footerActionsEl = document.getElementById('modal-product-footer-actions');
            let footerHtml = `<button onclick="closeProductDetailModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors cursor-pointer border border-slate-200/50">Đóng</button>`;
            
            if (product.Status === 'pending') {
                footerHtml = `
                    <button onclick="updateProductStatusAndClose(${product.ID}, 'active')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer shadow-sm">Duyệt sản phẩm</button>
                    <button onclick="updateProductStatusAndClose(${product.ID}, 'rejected')" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-650 rounded-xl text-xs font-semibold transition-colors cursor-pointer border border-red-200/40">Từ chối duyệt</button>
                ` + footerHtml;
            } else if (product.Status === 'active' || product.Status === 'available') {
                footerHtml = `
                    <button onclick="updateProductStatusAndClose(${product.ID}, 'rejected')" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-650 rounded-xl text-xs font-semibold transition-colors cursor-pointer border border-red-200/40">Gỡ tin đăng</button>
                ` + footerHtml;
            }
            footerActionsEl.innerHTML = footerHtml;

            // Show Modal with animation
            const modal = document.getElementById('product-detail-modal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.firstElementChild.classList.remove('scale-95');
            modal.firstElementChild.classList.add('scale-100');
        }

        function closeProductDetailModal() {
            const modal = document.getElementById('product-detail-modal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.firstElementChild.classList.remove('scale-100');
            modal.firstElementChild.classList.add('scale-95');
        }

        async function updateProductStatusAndClose(id, status) {
            closeProductDetailModal();
            await updateProductStatus(id, status);
        }

        applyInitialFilterFromUrl();
        loadProducts();
    </script>
</body>
</html>