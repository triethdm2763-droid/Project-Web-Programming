<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 p-4 shrink-0 h-screen sticky top-0">

    <div class="mb-8 px-2">
        <h1 class="text-xl font-bold text-blue-600">
            Kênh Quản Trị
        </h1>
    </div>

    <nav class="space-y-1 flex-1">

        <a href="/Project-Web-Programming/frontend/pages/admin/dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'dashboard.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">dashboard</span>
            Tổng quan
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/products.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'products.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">inventory_2</span>
            Sản phẩm
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/orders.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'orders.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">shopping_cart</span>
            Đơn hàng
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/users.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'users.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">group</span>
            Người dùng
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/wallets.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'wallets.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            Ví tiền
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/reports.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'reports.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">analytics</span>
            Báo cáo
        </a>

    </nav>

    <div class="pt-4 border-t border-slate-100">
        <button onclick="adminLogout()" class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-red-500 rounded-xl transition-all font-medium">
            <span class="material-symbols-outlined">logout</span>
            Đăng xuất
        </button>
    </div>

    <script>
        async function adminLogout() {
            if (!await showConfirm("Đăng xuất", "Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?", "warning")) return;
            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/logout", {
                    method: "POST"
                });
                if (res.ok) {
                    window.location.href = "/Project-Web-Programming/frontend/pages/auth/login.php";
                } else {
                    showAlert("Thất bại", "Đăng xuất thất bại.", "error");
                }
            } catch (error) {
                console.error("Logout error:", error);
                showAlert("Lỗi kết nối", "Lỗi kết nối đến máy chủ.", "error");
            }
        }
    </script>

</aside>