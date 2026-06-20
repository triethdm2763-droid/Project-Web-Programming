<?php
session_start();

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

<body class="bg-slate-50">

<div class="flex">

    <?php include '../../components/sidebar.php'; ?>

    <main class="flex-1 p-8">

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
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 mb-6 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined text-amber-600">info</span>
            <span>
                Tính năng ví tiền đang trong giai đoạn phát triển. Số dư hiển thị bên dưới hiện là 0đ
                cho mọi tài khoản cho đến khi backend bổ sung cơ chế nạp/rút tiền.
            </span>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border overflow-hidden">

            <div class="p-6 border-b">
                <input
                    id="wallet-search-input"
                    type="text"
                    placeholder="Tìm kiếm theo tên hoặc email..."
                    class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                    oninput="filterWallets()"
                >
            </div>

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-4 text-left">Tên người dùng</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Vai trò</th>
                            <th class="p-4 text-right">Số dư ví</th>
                        </tr>
                    </thead>

                    <tbody
                        id="wallets-table"
                        class="divide-y"
                    >
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
                        Không tìm thấy người dùng nào
                    </td>
                </tr>
            `;
            return;
        }

        let html = "";

        wallets.forEach(wallet => {

            const role =
                wallet.Role === "admin"
                    ? "Quản trị viên"
                    : "Người dùng";

            html += `
                <tr class="hover:bg-slate-50">

                    <td class="p-4 font-medium">
                        ${escapeHtml(wallet.Username)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(wallet.Email)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${role}
                    </td>

                    <td class="p-4 text-right font-semibold text-blue-600">
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
                "/Project-Web-Programming/backend/public/index.php/api/admin/wallets"
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
