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
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body class="bg-slate-50">
    <div class="flex">
        <?php include '../../components/sidebar.php'; ?>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Quản lý sản phẩm</h1>
            
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <p class="text-slate-500 text-sm">Tổng sản phẩm</p>
                    <h2 class="text-3xl font-bold text-blue-600"><?= (int)$totalProducts ?></h2>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <p class="text-slate-500 text-sm">Đang bán</p>
                    <h2 class="text-3xl font-bold text-green-600"><?= (int)$activeProducts ?></h2>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 flex flex-col sm:flex-row gap-4 border border-slate-200">
                <input id="product-search-input" type="text" placeholder="Tìm theo tên sản phẩm hoặc người bán..." class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500" oninput="filterProducts()">
                <select id="product-status-filter" class="border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500" onchange="filterProducts()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang bán</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="sold">Đã bán</option>
                    <option value="rejected">Bị từ chối</option>
                </select>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                <table class="w-full text-left">
                    <thead class="bg-slate-100 text-slate-600 text-sm">
                        <tr>
                            <th class="p-4">Hình ảnh</th>
                            <th class="p-4">Tên sản phẩm</th>
                            <th class="p-4">Danh mục</th>
                            <th class="p-4">Người bán</th>
                            <th class="p-4">Giá</th>
                            <th class="p-4 text-center">Trạng thái</th>
                            <th class="p-4 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="productTable" class="divide-y divide-slate-100">
                        <tr><td colspan="7" class="p-8 text-center text-slate-400">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        let allProducts = [];
        async function loadProducts() {
            try {
                const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/admin/products", { credentials: 'same-origin' });
                allProducts = await res.json();
                applyInitialFilterFromUrl();
                filterProducts();
            } catch (e) { document.getElementById("productTable").innerHTML = '<tr><td colspan="7" class="p-8 text-center text-red-500">Lỗi tải dữ liệu</td></tr>'; }
        }

        function applyInitialFilterFromUrl() {
            const search = new URLSearchParams(window.location.search).get('search');
            if (search) document.getElementById('product-search-input').value = search;
        }

        function filterProducts() {
            const keyword = document.getElementById("product-search-input").value.toLowerCase();
            const status = document.getElementById("product-status-filter").value;
            let filtered = allProducts.filter(p => 
                (p.Name?.toLowerCase().includes(keyword) || p.SellerName?.toLowerCase().includes(keyword)) &&
                (!status || (status === 'active' ? (p.Status === 'active' || p.Status === 'available') : p.Status === status))
            );
            renderProducts(filtered);
        }

        function renderProducts(products) {
            const tbody = document.getElementById("productTable");
            if (!products.length) { tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-slate-400">Không có sản phẩm</td></tr>'; return; }
            tbody.innerHTML = products.map(p => {
                let statusLabel = p.Status === 'active' ? '<span class="text-green-600 bg-green-50 px-2 py-1 rounded-full text-xs">Đang bán</span>' : (p.Status === 'pending' ? '<span class="text-amber-600 bg-amber-50 px-2 py-1 rounded-full text-xs">Chờ duyệt</span>' : '<span>'+p.Status+'</span>');
                let actions = p.Status === 'pending' ? `<button onclick="updateProductStatus(${p.ID}, 'active')" class="text-green-600 font-bold mr-2">Duyệt</button><button onclick="updateProductStatus(${p.ID}, 'rejected')" class="text-red-600 font-bold">Từ chối</button>` : `<button onclick="updateProductStatus(${p.ID}, 'rejected')" class="text-red-600">Gỡ</button>`;
                return `<tr class="hover:bg-slate-50">
                    <td class="p-4"><img src="${p.Image ? '/Project-Web-Programming/backend/uploads/products/'+p.Image : 'https://placehold.co/50'}" class="w-12 h-12 rounded-lg object-cover"></td>
                    <td class="p-4">${p.Name}</td>
                    <td class="p-4">${p.CategoryName}</td>
                    <td class="p-4">${p.SellerName}</td>
                    <td class="p-4">${Number(p.Price).toLocaleString('vi-VN')} đ</td>
                    <td class="p-4 text-center">${statusLabel}</td>
                    <td class="p-4 text-center">${actions}</td>
                </tr>`;
            }).join('');
        }

        async function updateProductStatus(id, newStatus) {
            if (!confirm("Xác nhận hành động này?")) return;
            const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/admin/products/update-status`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id, status: newStatus })
            });
            if ((await res.json()).success) loadProducts();
        }

        loadProducts();
    </script>
</body>
</html>