<?php
session_start();
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
                                <th class="p-4 text-center font-semibold">Trạng thái</th>
                                <th class="p-4 text-center font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="productTable" class="divide-y divide-slate-100/50">
                            <tr><td colspan="7" class="p-8 text-center text-slate-400">Đang tải danh sách...</td></tr>
                        </tbody>
                    </table>
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
                applyInitialFilterFromUrl();
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
            const keyword = document.getElementById("product-search-input").value.toLowerCase();
            const status = document.getElementById("product-status-filter").value;
            let filtered = allProducts.filter(p => 
                (escapeHtml(p.Name)?.toLowerCase().includes(keyword) || escapeHtml(p.SellerName)?.toLowerCase().includes(keyword)) &&
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

                return `<tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="p-4"><img src="${p.Image ? '/Project-Web-Programming/backend/uploads/products/'+p.Image : '/Project-Web-Programming/frontend/assets/images/placeholder.png'}" class="w-12 h-12 rounded-lg object-contain border bg-slate-50 flex-shrink-0" onerror="this.src='/Project-Web-Programming/frontend/assets/images/placeholder.png'"></td>
                    <td class="p-4 font-medium text-sm text-slate-800">${escapeHtml(p.Name)}</td>
                    <td class="p-4 text-sm text-slate-600">${escapeHtml(p.CategoryName)}</td>
                    <td class="p-4 text-sm text-slate-600">${escapeHtml(p.SellerName)}</td>
                    <td class="p-4 text-sm font-bold text-slate-800">${Number(p.Price).toLocaleString('vi-VN')} đ</td>
                    <td class="p-4 text-center">${statusLabel}</td>
                    <td class="p-4 text-center">${actions}</td>
                </tr>`;
            }).join('');
        }

        async function updateProductStatus(id, newStatus) {
            const confirmMsg = newStatus === 'active' ? "Xác nhận duyệt cho phép hiển thị sản phẩm này?" : "Xác nhận gỡ/từ chối sản phẩm này?";
            if (!confirm(confirmMsg)) return;
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

        loadProducts();
    </script>
</body>
</html>