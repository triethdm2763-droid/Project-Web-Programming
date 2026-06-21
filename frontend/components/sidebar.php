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

        <a href="/Project-Web-Programming/frontend/pages/admin/order.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'order.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">shopping_cart</span>
            Đơn hàng
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/user.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'user.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">group</span>
            Người dùng
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/wallet.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'wallet.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            Ví tiền
        </a>

        <a href="/Project-Web-Programming/frontend/pages/admin/report.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
           <?= $currentPage == 'report.php'
                ? 'bg-blue-600 text-white shadow-md'
                : 'text-slate-600 hover:bg-slate-100'; ?>">
            <span class="material-symbols-outlined">analytics</span>
            Báo cáo
        </a>

    </nav>

</aside>