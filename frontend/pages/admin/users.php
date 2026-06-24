<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Quản lý người dùng</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-slate-50 font-body-md text-on-surface">
    <div class="flex min-h-screen">
        <?php include '../../components/sidebar.php'; ?>
        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">Quản lý người dùng</h1>
                <p class="text-slate-500 mt-2">Danh sách các tài khoản khách hàng và người bán trong hệ thống</p>
            </div>

            <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden">
                <div class="p-5 border-b border-slate-100/60 relative">
                    <span class="material-symbols-outlined absolute left-8 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input id="user-search-input" type="text" placeholder="Tìm kiếm người dùng bằng Tên đăng nhập hoặc Email..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm"
                           oninput="filterUsers()">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-100/50 text-slate-500 text-xs uppercase border-b border-slate-100">
                            <tr>
                                <th class="p-4 font-semibold">Tên đăng nhập</th>
                                <th class="p-4 font-semibold">Địa chỉ Email</th>
                                <th class="p-4 font-semibold">Vai trò</th>
                                <th class="p-4 text-center font-semibold">Trạng thái</th>
                                <th class="p-4 text-center font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="users-table" class="divide-y divide-slate-100/50">
                            <tr><td colspan="5" class="p-8 text-center text-slate-400">Đang tải danh sách tài khoản...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let allUsers = [];
        async function loadUsers() {
            try {
                const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/admin/users", { credentials: 'same-origin' });
                allUsers = await res.json();
                renderUsers(allUsers);
            } catch (e) { 
                document.getElementById("users-table").innerHTML = '<tr><td colspan="5" class="p-8 text-center text-red-500">Lỗi tải dữ liệu người dùng</td></tr>'; 
            }
        }

        function renderUsers(users) {
            const tbody = document.getElementById("users-table");
            if (!users.length) { tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-400">Không tìm thấy tài khoản phù hợp.</td></tr>'; return; }
            
            tbody.innerHTML = users.map(user => {
                const isBanned = (user.Status || 'active') === 'banned';
                const statusBadge = isBanned 
                    ? '<span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-semibold border border-red-100">Bị khóa</span>' 
                    : '<span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-semibold border border-green-100">Hoạt động</span>';
                
                const actionButton = user.Role === 'admin' 
                    ? '<span class="text-xs text-slate-300">—</span>'
                    : (isBanned 
                        ? `<button onclick="toggleUserStatus(${user.ID}, 'active')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors">Mở khóa</button>`
                        : `<button onclick="toggleUserStatus(${user.ID}, 'banned')" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-semibold transition-colors">Khóa tài khoản</button>`);

                return `<tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="p-4 font-medium text-sm text-slate-800">${escapeHtml(user.Username)}</td>
                    <td class="p-4 text-sm text-slate-600">${escapeHtml(user.Email)}</td>
                    <td class="p-4 text-sm text-slate-600">${user.Role === 'admin' ? 'Quản trị viên (Admin)' : 'Thành viên (C2C)'}</td>
                    <td class="p-4 text-center">${statusBadge}</td>
                    <td class="p-4 text-center">${actionButton}</td>
                </tr>`;
            }).join('');
        }

        async function toggleUserStatus(id, newStatus) {
            const confirmMsg = newStatus === 'banned' 
                ? 'Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập hoặc giao dịch.' 
                : 'Bạn muốn mở khóa hoạt động cho tài khoản này?';
            if (!confirm(confirmMsg)) return;

            try {
                const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/admin/users/update-status", {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id, status: newStatus })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(newStatus === 'banned' ? "🔒 Đã khóa tài khoản thành công!" : "🔓 Đã mở khóa tài khoản thành công!", "success");
                    loadUsers();
                } else {
                    showAlert("Thất bại", result.error || "Không thể cập nhật trạng thái", "error");
                }
            } catch (err) {
                console.error(err);
                showAlert("Lỗi kết nối", "Không thể cập nhật trạng thái tài khoản.", "error");
            }
        }

        function filterUsers() {
            const keyword = document.getElementById("user-search-input").value.toLowerCase();
            renderUsers(allUsers.filter(u => u.Username.toLowerCase().includes(keyword) || u.Email.toLowerCase().includes(keyword)));
        }

        function escapeHtml(text) { return text ? String(text).replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m])) : ''; }

        loadUsers();
    </script>
</body>
</html>