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
    <meta charset="UTF-8">
    <title>Bảng Điều Khiển Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body class="bg-slate-50 text-slate-800 font-sans">
    <div class="flex min-h-screen">
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 p-4">
            <h1 class="text-xl font-bold text-blue-600 mb-8 px-2">Kênh Quản Trị</h1>
            <nav class="space-y-1 flex-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium"><span class="material-symbols-outlined">dashboard</span> Tổng quan</a>
                <a href="products.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl"><span class="material-symbols-outlined">inventory_2</span> Sản phẩm</a>
                </nav>
            <button onclick="logout()" class="text-slate-400 hover:text-red-500 flex items-center gap-2"><span class="material-symbols-outlined">logout</span> Đăng xuất</button>
        </aside>

        <main class="flex-1 p-8">
            <section class="grid grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-400 uppercase">Tổng Doanh Thu</p>
                    <h3 class="text-2xl font-bold"><?= number_format($totalRevenue) ?> đ</h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-400 uppercase">Đơn Hàng Mới</p>
                    <h3 class="text-2xl font-bold"><?= $totalOrders ?></h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-400 uppercase">Người Dùng Mới</p>
                    <h3 class="text-2xl font-bold"><?= $totalUsers ?></h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-400 uppercase text-red-600">Chờ Phê Duyệt</p>
                    <h3 class="text-2xl font-bold text-red-600"><?= $pendingProducts ?></h3>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-lg mb-4">Sản phẩm chờ duyệt</h3>
                <table class="w-full text-left">
                    <tbody class="divide-y">
                        <?php foreach ($pendingProductsList as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-4 flex items-center gap-3">
                                <img src="<?= $p['Image'] ? '/Project-Web-Programming/backend/uploads/products/'.$p['Image'] : 'https://placehold.co/50' ?>" class="w-12 h-12 rounded-lg object-cover border">
                                <span><?= htmlspecialchars($p['Name']) ?></span>
                            </td>
                            <td><?= number_format($p['Price']) ?> đ</td>
                            <td class="text-right">
                                <button onclick="updateProductStatus(<?= $p['ID'] ?>, 'active')" class="px-3 py-1 bg-green-500 text-white rounded-lg">Duyệt</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        async function updateProductStatus(id, status) {
            if (!confirm("Xác nhận hành động?")) return;
            const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/admin/products/update-status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            if ((await res.json()).success) location.reload();
        }

        async function logout() {
            if (!confirm("Đăng xuất?")) return;
            await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/logout", { method: "POST" });
            window.location.href = "../auth/login.php";
        }
    </script>
</body>
</html>