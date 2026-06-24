<?php
session_start();
require_once '../../../backend/src/config/Database.php';
use App\Config\Database;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$conn = Database::getInstance()->getConnection();

$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $conn->query("SELECT IFNULL(SUM(Amount),0) FROM payments WHERE Status = 'success'")->fetchColumn();
$pendingProducts = $conn->query("SELECT COUNT(*) FROM products WHERE Status = 'pending'")->fetchColumn();

$pendingProductsList = $conn->query("
    SELECT p.ID, p.Name, p.Image, p.Price, c.Name AS CategoryName, u.Fullname AS SellerName
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
    <title>Bảng Điều Khiển Admin | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-slate-50 font-body-md text-on-surface">
    <div class="flex min-h-screen">
        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">Tổng quan quản trị</h1>
                <p class="text-slate-500 mt-2">Dữ liệu thống kê hoạt động của hệ thống thời gian thực</p>
            </div>

            <!-- Stats Grid (Glassmorphism Cards) -->
            <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng Doanh Thu</p>
                        <h3 class="text-2xl font-bold text-primary mt-1"><?= number_format($totalRevenue) ?> đ</h3>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]">payments</span>
                    </div>
                </div>

                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Đơn Hàng Mới</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $totalOrders ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-outlined text-[28px]">shopping_cart</span>
                    </div>
                </div>

                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Người Dùng Mới</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $totalUsers ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center text-purple-600">
                        <span class="material-symbols-outlined text-[28px]">group</span>
                    </div>
                </div>

                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Chờ Phê Duyệt</p>
                        <h3 class="text-2xl font-bold text-red-600 mt-1"><?= $pendingProducts ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center text-red-600">
                        <span class="material-symbols-outlined text-[28px]">pending_actions</span>
                    </div>
                </div>
            </section>

            <!-- Pending Products Table -->
            <section class="bg-white/60 backdrop-blur-md rounded-2xl border border-outline-variant/10 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-lg text-slate-800">Sản phẩm chờ duyệt gần đây</h3>
                    <a href="/Project-Web-Programming/frontend/pages/admin/products.php?status=pending" class="text-xs text-primary font-semibold hover:underline flex items-center gap-1">
                        Xem tất cả <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                <?php if (empty($pendingProductsList)): ?>
                    <div class="text-center py-8 text-slate-400 text-sm">Hiện không có sản phẩm nào cần phê duyệt.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-slate-400 uppercase border-b border-slate-100">
                                <tr>
                                    <th class="pb-3 font-semibold">Sản phẩm</th>
                                    <th class="pb-3 font-semibold">Danh mục</th>
                                    <th class="pb-3 font-semibold">Người bán</th>
                                    <th class="pb-3 font-semibold">Giá bán</th>
                                    <th class="pb-3 font-semibold text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php foreach ($pendingProductsList as $p): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 flex items-center gap-3">
                                        <img src="<?= $p['Image'] ? '/Project-Web-Programming/backend/uploads/products/'.$p['Image'] : '/Project-Web-Programming/frontend/assets/images/placeholder.png' ?>" class="w-12 h-12 rounded-lg object-contain border bg-slate-50 flex-shrink-0" onerror="this.src='/Project-Web-Programming/frontend/assets/images/placeholder.png'">
                                        <span class="font-medium text-sm text-slate-800 line-clamp-1"><?= htmlspecialchars($p['Name']) ?></span>
                                    </td>
                                    <td class="py-3.5 text-sm text-slate-600"><?= htmlspecialchars($p['CategoryName']) ?></td>
                                    <td class="py-3.5 text-sm text-slate-600"><?= htmlspecialchars($p['SellerName']) ?></td>
                                    <td class="py-3.5 text-sm font-bold text-slate-800"><?= number_format($p['Price']) ?> đ</td>
                                    <td class="py-3.5 text-right">
                                        <button onclick="updateProductStatus(<?= $p['ID'] ?>, 'active')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm shadow-emerald-600/10 hover:scale-[1.02] active:scale-95 transition-all">Duyệt</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        async function updateProductStatus(id, status) {
            if (!confirm("Xác nhận phê duyệt sản phẩm này?")) return;
            try {
                const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/admin/products/update-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, status })
                });
                const result = await res.json();
                if (result.success) {
                    showToast("✅ Đã duyệt sản phẩm thành công!", "success");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert("Duyệt thất bại", result.error || "Không thể phê duyệt", "error");
                }
            } catch (err) {
                console.error(err);
                showAlert("Lỗi hệ thống", "Không thể kết nối đến máy chủ.", "error");
            }
        }
    </script>
</body>
</html>