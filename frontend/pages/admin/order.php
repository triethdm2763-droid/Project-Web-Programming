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
    <title>Quản lý đơn hàng</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-slate-50">

    <div class="flex">s

        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-1 p-8">

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">
                    Quản lý đơn hàng
                </h1>

                <p class="text-slate-500 mt-2">
                    Theo dõi trạng thái giao dịch của người dùng
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border overflow-hidden">

                <div class="p-6 border-b flex justify-between items-center">
                    <input
                        id="order-search-input"
                        type="text"
                        placeholder="Tìm kiếm theo khách hàng hoặc sản phẩm..."
                        class="border rounded-xl px-4 py-3 w-80 outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="filterOrders()">

                    <button
                        onclick="showToast('Tính năng xuất Excel đang được phát triển.', 'info')"
                        class="bg-blue-600 text-white px-5 py-3 rounded-xl font-semibold hover:bg-blue-700">
                        Xuất Excel
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">

                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-4 text-left">Mã đơn</th>
                                <th class="p-4 text-left">Khách hàng</th>
                                <th class="p-4 text-left">Sản phẩm</th>
                                <th class="p-4 text-left">Tổng tiền</th>
                                <th class="p-4 text-center">Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody
                            id="orders-table"
                            class="divide-y">
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    Đang tải danh sách đơn hàng...
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>

        </main>

    </div>

    <script>
        let allOrders = [];

        function escapeHtml(text) {
            return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }

        function renderOrderStatus(status) {
            if (status === 'pending') {
                return `<span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm">Chờ xử lý</span>`;
            }
            if (status === 'success' || status === 'completed') {
                return `<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Hoàn tất</span>`;
            }
            if (status === 'cancelled') {
                return `<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Đã hủy</span>`;
            }
            return `<span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm">${escapeHtml(status)}</span>`;
        }

        function renderOrders(orders) {
            const tbody = document.getElementById("orders-table");

            if (!orders || orders.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400">
                        Không tìm thấy đơn hàng nào
                    </td>
                </tr>
            `;
                return;
            }

            let html = "";

            orders.forEach(order => {
                html += `
                <tr class="hover:bg-slate-50">

                    <td class="p-4 font-medium">
                        #${order.ID}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(order.BuyerName)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(order.ProductName)}
                    </td>

                    <td class="p-4 font-semibold text-blue-600">
                        ${Number(order.Total_price).toLocaleString("vi-VN")} đ
                    </td>

                    <td class="p-4 text-center">
                        ${renderOrderStatus(order.Status)}
                    </td>

                </tr>
            `;
            });

            tbody.innerHTML = html;
        }

        function filterOrders() {
            const keyword = document.getElementById("order-search-input").value.trim().toLowerCase();

            if (!keyword) {
                renderOrders(allOrders);
                return;
            }

            const filtered = allOrders.filter(order =>
                (order.BuyerName || '').toLowerCase().includes(keyword) ||
                (order.ProductName || '').toLowerCase().includes(keyword)
            );

            renderOrders(filtered);
        }

        async function loadOrders() {
            try {
                const res = await fetch(
                    "/Project-Web-Programming/backend/public/index.php/api/admin/orders"
                );

                allOrders = await res.json();

                renderOrders(allOrders);

            } catch (error) {
                console.error(error);

                document.getElementById("orders-table").innerHTML = `
                <tr>
                    <td colspan="5" class="p-8 text-center text-red-500">
                        Không tải được danh sách đơn hàng
                    </td>
                </tr>
            `;
            }
        }

        loadOrders();
    </script>

</body>

</html>