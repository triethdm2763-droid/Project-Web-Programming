<?php
session_start();

require_once '../../../backend/src/config/Database.php';

use App\Config\Database;

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Kết nối database
$database = Database::getInstance();
$conn = $database->getConnection();

// Thống kê
$totalUsers = $conn->query("
    SELECT COUNT(*)
    FROM users
")->fetchColumn();

$totalProducts = $conn->query("
    SELECT COUNT(*)
    FROM products
")->fetchColumn();

$totalOrders = $conn->query("
    SELECT COUNT(*)
    FROM orders
")->fetchColumn();

$totalRevenue = $conn->query("
    SELECT IFNULL(SUM(Amount),0)
    FROM payments
    WHERE Status = 'paid'
")->fetchColumn();

$pendingProducts = $conn->query("
    SELECT COUNT(*)
    FROM products
    WHERE Status = 'pending'
")->fetchColumn();
?>
<?php
    $pendingProductsList = $conn->query("
    SELECT
        p.ID,
        p.Name,
        p.Image,
        p.Price,
        c.Name AS CategoryName,
        u.Fullname AS SellerName
    FROM products p
    JOIN categories c ON p.Category_ID = c.ID
    JOIN users u ON p.Seller_ID = u.ID
    WHERE p.Status = 'pending'
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();
 ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Admin - Chợ Cũ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="/Project-Web-Programming/frontend/assets/js/ui-helpers.js?v=20260618-2"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <div class="flex min-h-screen">
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 p-4 shrink-0">
            <div class="mb-8 px-2">
                <h1 class="text-xl font-bold text-blue-600">Kênh Quản Trị</h1>
                <p class="text-xs text-slate-400">Chào mừng trở lại</p>
            </div>
            
            <nav class="space-y-1 flex-1">
                <a href="/Project-Web-Programming/frontend/pages/admin/dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium">
                    <span class="material-symbols-outlined">dashboard</span> Tổng quan
                </a>
                <a href="/Project-Web-Programming/frontend/pages/admin/products.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">inventory_2</span> Sản phẩm
                </a>
                <a href="/Project-Web-Programming/frontend/pages/admin/order.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">shopping_cart</span> Đơn hàng
                </a>
                <a href="/Project-Web-Programming/frontend/pages/admin/user.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">group</span> Người dùng
                </a>
                <a href="/Project-Web-Programming/frontend/pages/admin/wallet.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">account_balance_wallet</span> Ví tiền
                </a>
                <a href="/Project-Web-Programming/frontend/pages/admin/report.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">analytics</span> Báo cáo
                </a>
            </nav>

            <div class="border-t border-slate-100 pt-4 flex items-center justify-between px-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600">A</div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700">Admin</h4>
                        <span class="text-[10px] uppercase font-bold text-red-500 tracking-wider">Super Admin</span>
                    </div>
                </div>
                <button onclick="logout()" class="text-slate-400 hover:text-red-500 transition-colors" title="Đăng xuất">
                    <span class="material-symbols-outlined">logout</span>
                </button>
            </div>
        </aside>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto max-w-7xl mx-auto w-full">
            
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Bảng Điều Khiển</h2>
                    <p class="text-sm text-slate-500">Thống kê hoạt động toàn sàn hôm nay, <span id="current-date">--/--/----</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                        <input type="text" placeholder="Tìm kiếm nhanh..." class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <button class="bg-blue-600 text-white font-medium px-4 py-2 rounded-xl text-sm hover:bg-blue-700 transition-colors flex items-center gap-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-sm">export_notes</span> Xuất báo cáo
                    </button>
                </div>
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

                <!-- Tổng doanh thu -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-400 uppercase mb-2">
                            Tổng Doanh Thu
                        </p>

                        <h3 class="text-2xl font-bold text-slate-900">
                            <?= number_format($totalRevenue) ?>  đ
                        </h3>
                    </div>

                    <div class="p-3 bg-blue-50 rounded-xl">
                        <span class="material-symbols-outlined text-blue-600">
                            payments
                        </span>
                    </div>
                </div>

                <!-- Đơn hàng mới -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-400 uppercase mb-2">
                            Đơn Hàng Mới
                        </p>

                        <h3 class="text-2xl font-bold text-slate-900">
                            <?= $totalOrders ?>
                        </h3>
                    </div>

                    <div class="p-3 bg-orange-50 rounded-xl">
                        <span class="material-symbols-outlined text-orange-600">
                            shopping_basket
                        </span>
                    </div>
                </div>

                <!-- Người dùng mới -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-400 uppercase mb-2">
                            Người Dùng Mới
                        </p>

                        <h3 class="text-2xl font-bold text-slate-900">
                            <?= $totalUsers ?>
                        </h3>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl">
                        <span class="material-symbols-outlined text-emerald-600">
                            person_add
                        </span>
                    </div>
                </div>

                <!-- Chờ phê duyệt -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
                    <div>
                        <p class="text-xs text-slate-400 uppercase mb-2">
                            Chờ Phê Duyệt
                        </p>

                        <h3 class="text-2xl font-bold text-red-600">
                            <?= $pendingProducts ?>
                        </h3>
                    </div>

                    <div class="p-3 bg-red-50 rounded-xl">
                        <span class="material-symbols-outlined text-red-600">
                            gavel
                        </span>
                    </div>
                </div>

            </section>

            <section class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-5 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-lg">Sản phẩm chờ duyệt</h3>
                    <a href="#" class="text-xs font-semibold text-blue-600 hover:underline">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="pb-3 font-medium">Sản phẩm</th>
                                <th class="pb-3 font-medium">Danh mục</th>
                                <th class="pb-3 font-medium">Người đăng</th>
                                <th class="pb-3 font-medium">Giá tiền</th>
                                <th class="pb-3 font-medium text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">

                            <?php if (count($pendingProductsList) > 0): ?>

                                <?php foreach ($pendingProductsList as $product): ?>

                                    <tr class="hover:bg-slate-50">

                                        <td class="py-4 flex items-center gap-3">
                                            <img
                                                src="<?= $product['Image'] ?: 'https://placehold.co/50' ?>"
                                                class="w-12 h-12 rounded-lg object-cover border"
                                            >

                                            <span class="font-medium">
                                                <?= htmlspecialchars($product['Name']) ?>
                                            </span>
                                        </td>

                                        <td class="py-4">
                                            <?= htmlspecialchars($product['CategoryName']) ?>
                                        </td>

                                        <td class="py-4">
                                            <?= htmlspecialchars($product['SellerName']) ?>
                                        </td>

                                        <td class="py-4 text-blue-600 font-semibold">
                                            <?= number_format($product['Price']) ?> đ
                                        </td>

                                        <td class="py-4 text-center">
                                            <button
                                                onclick="updateProductStatus(<?= (int)$product['ID'] ?>, 'active')"
                                                class="px-3 py-1 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                                            >
                                                Duyệt
                                            </button>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Không có sản phẩm chờ duyệt
                                </td>
                            </tr>

                            <?php endif; ?>

                            </tbody>

                    </table>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                    <h3 class="font-bold text-slate-900 text-base mb-4">Người dùng mới</h3>
                    <div id="admin-users-list" class="space-y-4">
                        </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm">
                    <h3 class="font-bold text-slate-900 text-base mb-4">Giao dịch gần đây</h3>
                    <div id="admin-orders-list" class="space-y-3">
                        </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('current-date').innerText = new Date().toLocaleDateString('vi-VN');
            
            // Tải toàn bộ dữ liệu khi trang vừa load
            
        });

    
        async function updateProductStatus(id, newStatus) {
            if (!confirm(`Bạn có chắc chắn muốn chuyển trạng thái sản phẩm này sang: ${newStatus}?`)) return;

            try {
                const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/admin/products/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: newStatus })
                });

                const result = await res.json();
                if (result.success) {
                    showToast("Cập nhật thành công!", "success");
                    // Reload bảng để cập nhật danh sách sản phẩm chờ duyệt
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(result.message || "Lỗi cập nhật.", "error");
                }
            } catch (error) {
                showToast("Không thể kết nối máy chủ.", "error");
            }
        }
    </script>
</body>
</html>