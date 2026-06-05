<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Admin - Chợ Cũ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <div class="flex min-h-screen">
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 p-4 shrink-0">
            <div class="mb-8 px-2">
                <h1 class="text-xl font-bold text-blue-600">Kênh Quản Trị</h1>
                <p class="text-xs text-slate-400">Chào mừng trở lại</p>
            </div>
            
            <nav class="space-y-1 flex-1">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium">
                    <span class="material-symbols-outlined">dashboard</span> Tổng quan
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">inventory_2</span> Sản phẩm
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">shopping_cart</span> Đơn hàng
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">group</span> Người dùng
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">account_balance_wallet</span> Ví tiền
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <span class="material-symbols-outlined">analytics</span> Báo cáo
                </a>
            </nav>

            <div class="border-t border-slate-100 pt-4 flex items-center justify-between px-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600">A</div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700">Admin-01</h4>
                        <span class="text-[10px] uppercase font-bold text-red-500 tracking-wider">Super Admin</span>
                    </div>
                </div>
                <button class="text-slate-400 hover:text-red-500 transition-colors">
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
                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start justify-between">
                    <div class="space-y-2">
                        <span class="text-xs font-medium text-slate-400 uppercase">Tổng Doanh Thu</span>
                        <h3 class="text-2xl font-bold text-slate-900" id="stat-revenue">0k</h3>
                    </div>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl material-symbols-outlined">payments</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start justify-between">
                    <div class="space-y-2">
                        <span class="text-xs font-medium text-slate-400 uppercase">Đơn Hàng Mới</span>
                        <h3 class="text-2xl font-bold text-slate-900" id="stat-orders">0</h3>
                    </div>
                    <span class="p-2 bg-orange-50 text-orange-600 rounded-xl material-symbols-outlined">shopping_basket</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start justify-between">
                    <div class="space-y-2">
                        <span class="text-xs font-medium text-slate-400 uppercase">Người Dùng Mới</span>
                        <h3 class="text-2xl font-bold text-slate-900" id="stat-users">0</h3>
                    </div>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl material-symbols-outlined">person_add</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start justify-between">
                    <div class="space-y-2">
                        <span class="text-xs font-medium text-slate-400 uppercase">Chờ Phê Duyệt</span>
                        <h3 class="text-2xl font-bold text-red-600" id="stat-pending">0</h3>
                    </div>
                    <span class="p-2 bg-red-50 text-red-600 rounded-xl material-symbols-outlined">gavel</span>
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
                        <tbody id="admin-pending-table" class="text-sm divide-y divide-slate-100">
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
            // Đổ ngày thực tế tự động
            document.getElementById('current-date').innerText = new Date().toLocaleDateString('vi-VN');
            
            // Gọi các hàm tải dữ liệu động
            loadAdminStats();
            loadPendingProducts();
        });

        function loadAdminStats() {
            // Triết viết fetch gọi API lấy số liệu tổng hợp tại đây
            // Giả lập dữ liệu hiển thị tĩnh theo ảnh mẫu trước:
            document.getElementById('stat-revenue').innerText = '128.450k';
            document.getElementById('stat-orders').innerText = '1.240';
            document.getElementById('stat-users').innerText = '856';
            document.getElementById('stat-pending').innerText = '42';
        }

        function loadPendingProducts() {
            const tableBody = document.getElementById('admin-pending-table');
            // Dữ liệu mẫu khớp nối với Database của Triết ở tuần trước
            const samplePending = [
                { id: 1, name: 'MacBook Pro M1 2020 16/256GB', cat: 'Đồ điện tử', seller: 'Thành Nam', price: 18500000, img: 'macbook.jpg' },
                { id: 2, name: 'Nike Air Jordan 1 Retro High', cat: 'Thời trang', seller: 'Hoàng Minh', price: 3200000, img: 'nike_aj1.jpg' }
            ];

            tableBody.innerHTML = samplePending.map(p => `
                <tr class="group hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl overflow-hidden shrink-0 border border-slate-100">
                            <img src="/Project-Web-Programming/backend/uploads/products/${p.img}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'><rect width=\'40\' height=\'40\' fill=\'%23f1f5f9\'/><text x=\'50%\' y=\'55%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23cbd5e1\' font-size=\'10\'>IMAGE</text></svg>'">
                        </div>
                        <span class="font-medium text-slate-900 truncate max-w-[200px]">${p.name}</span>
                    </td>
                    <td class="py-3.5 text-slate-500">${p.cat}</td>
                    <td class="py-3.5 text-slate-600 font-medium">${p.seller}</td>
                    <td class="py-3.5 text-blue-600 font-bold">${new Intl.NumberFormat('vi-VN', {style:'currency', currency:'VND'}).format(p.price)}</td>
                    <td class="py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="approveProduct(${p.id}, 'approve')" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-all flex items-center justify-center" title="Phê duyệt">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            </button>
                            <button onclick="approveProduct(${p.id}, 'reject')" class="p-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-all flex items-center justify-center" title="Từ chối">
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function approveProduct(id, action) {
            // Kết nối trực tiếp đến API PUT của BE2: /api/admin/approve/{id}
            alert(`Gửi yêu cầu xử lý lệnh: ${action.toUpperCase()} cho sản phẩm mang ID = ${id}`);
        }
    </script>
</body>
</html>