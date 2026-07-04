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
    <title>Quản lý đơn hàng</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-slate-50 font-body-md text-on-surface">
    <div class="flex min-h-screen">
        <?php include '../../components/sidebar.php'; ?>
        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">Quản lý đơn hàng</h1>
                <p class="text-slate-500 mt-2">Theo dõi và giám sát toàn bộ trạng thái giao dịch của người dùng trên sàn</p>
            </div>

            <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden">
                <div class="p-5 border-b border-slate-100/60 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="relative w-full sm:w-80">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                        <input
                            id="order-search-input"
                            type="text"
                            placeholder="Tìm khách hàng hoặc sản phẩm..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm"
                            oninput="filterOrders()">
                    </div>

                    <button
                        onclick="showToast('Tính năng xuất Excel đang được phát triển.', 'info')"
                        class="w-full sm:w-auto bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition-all hover:scale-[1.02] active:scale-95">
                        Xuất Excel
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-100/50 text-slate-500 text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="p-4 font-semibold">Mã đơn</th>
                                <th class="p-4 font-semibold">Khách mua hàng</th>
                                <th class="p-4 font-semibold">Tên sản phẩm</th>
                                <th class="p-4 font-semibold">Tổng tiền thanh toán</th>
                                <th class="p-4 text-center font-semibold">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table" class="divide-y divide-slate-100/50">
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
                return `<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold border border-amber-100">Chờ xử lý</span>`;
            }
            if (status === 'success' || status === 'completed') {
                return `<span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-semibold border border-green-100">Hoàn tất</span>`;
            }
            if (status === 'cancelled') {
                return `<span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-semibold border border-red-100">Đã hủy</span>`;
            }
            return `<span class="px-2.5 py-1 bg-slate-50 text-slate-700 rounded-full text-xs font-semibold border border-slate-100">${escapeHtml(status)}</span>`;
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
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="p-4 font-semibold text-sm text-slate-800">
                        <a href="/frontend/pages/payment/track.php?id=${encodeURIComponent(order.Order_Code)}" class="hover:underline text-[#0066cc]" title="Tra cứu đơn hàng">${order.Order_Code}</a>
                    </td>
                    <td class="p-4 text-sm text-slate-600">
                        ${escapeHtml(order.BuyerName)}
                    </td>
                    <td class="p-4 text-sm text-slate-600 font-medium">
                        ${escapeHtml(order.ProductName)}
                    </td>
                    <td class="p-4 text-sm font-bold text-slate-800">
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
                    "/backend/public/index.php/api/admin/orders"
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