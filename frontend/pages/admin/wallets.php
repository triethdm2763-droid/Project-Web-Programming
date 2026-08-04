<?php
require_once __DIR__ . '/../../components/session.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Quản lý ví tiền</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-slate-50 font-body-md text-on-surface">

    <div class="flex min-h-screen">

        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">
                    Quản lý ví tiền
                </h1>

                <p class="text-slate-500 mt-2">
                    Theo dõi số dư ví của người dùng trong hệ thống
                </p>
            </div>

            <!-- Lưu ý: hệ thống ví chưa được triển khai đầy đủ ở backend (chưa có cột số dư riêng
             trong bảng users), nên số dư hiện tại luôn hiển thị 0đ cho đến khi tính năng nạp/rút
             tiền được bổ sung. -->
            <div class="bg-amber-50/80 backdrop-blur-md border border-amber-200/50 text-amber-800 rounded-2xl p-4 mb-6 text-sm flex items-start gap-3 shadow-sm">
                <span class="material-symbols-outlined text-amber-600">info</span>
                <span>
                    <strong>Lưu ý:</strong> Tính năng ví tiền đang trong giai đoạn phát triển. Số dư hiển thị bên dưới hiện là 0đ cho mọi tài khoản cho đến khi backend bổ sung cơ chế nạp/rút tiền.
                </span>
            </div>

            <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden">

                <div class="p-5 border-b border-slate-100/60 relative">
                    <span class="material-symbols-outlined absolute left-8 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input
                        id="wallet-search-input"
                        type="text"
                        placeholder="Tìm kiếm ví theo tên đăng nhập hoặc email..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm"
                        oninput="filterWallets()">
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-slate-100/50 text-slate-500 text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="p-4 font-semibold">Tên người dùng</th>
                                <th class="p-4 font-semibold">Địa chỉ Email</th>
                                <th class="p-4 font-semibold">Vai trò</th>
                                <th class="p-4 text-right font-semibold">Số dư ví</th>
                            </tr>
                        </thead>

                        <tbody
                            id="wallets-table"
                            class="divide-y divide-slate-100/50">
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400">
                                    Đang tải danh sách ví...
                                </td>
                            </tr>
                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

    <script>
        let allWallets = [];

        function escapeHtml(text) {
            return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }

        function renderWallets(wallets) {
            const tbody = document.getElementById("wallets-table");

            if (!wallets || wallets.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400">
                        Không tìm thấy ví của người dùng nào phù hợp.
                    </td>
                </tr>
            `;
                return;
            }

            let html = "";

            wallets.forEach(wallet => {
                const roleBadge = wallet.Role === "admin"
                    ? '<span class="px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold">Quản trị viên</span>'
                    : '<span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold">Thành viên</span>';

                html += `
                <tr class="hover:bg-slate-50/50 transition-colors">

                    <td class="p-4 font-medium text-sm text-slate-800">
                        ${escapeHtml(wallet.Username)}
                    </td>

                    <td class="p-4 text-sm text-slate-600">
                        ${escapeHtml(wallet.Email)}
                    </td>

                    <td class="p-4 text-sm">
                        ${roleBadge}
                    </td>

                    <td class="p-4 text-right font-semibold text-primary text-sm">
                        ${Number(wallet.Balance || 0).toLocaleString("vi-VN")} đ
                    </td>

                </tr>
            `;
            });

            tbody.innerHTML = html;
        }

        function filterWallets() {
            const keyword = document.getElementById("wallet-search-input").value.trim().toLowerCase();

            if (!keyword) {
                renderWallets(allWallets);
                return;
            }

            const filtered = allWallets.filter(wallet =>
                (wallet.Username || '').toLowerCase().includes(keyword) ||
                (wallet.Email || '').toLowerCase().includes(keyword)
            );

            renderWallets(filtered);
        }

        async function loadWallets() {
            try {
                const res = await fetch(
                    "/backend/public/index.php/api/admin/wallets"
                );

                allWallets = await res.json();
                renderWallets(allWallets);

            } catch (error) {
                console.error(error);

                document.getElementById("wallets-table").innerHTML = `
                <tr>
                    <td colspan="4" class="p-8 text-center text-red-500">
                        Không tải được danh sách ví
                    </td>
                </tr>
            `;
            }
        }

        loadWallets();
    </script>

</body>

</html>