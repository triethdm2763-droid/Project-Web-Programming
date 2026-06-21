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
<body class="bg-slate-50">
    <div class="flex">
        <?php include '../../components/sidebar.php'; ?>
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">Quản lý người dùng</h1>
                <p class="text-slate-500 mt-2">Danh sách tài khoản trong hệ thống</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <input id="user-search-input" type="text" placeholder="Tìm kiếm người dùng (Tên/Email)..." 
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                           oninput="filterUsers()">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-600 text-sm">
                            <tr>
                                <th class="p-4">Tên</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Vai trò</th>
                                <th class="p-4 text-center">Trạng thái</th>
                                <th class="p-4 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="users-table" class="divide-y divide-slate-100">
                            <tr><td colspan="5" class="p-8 text-center text-slate-400">Đang tải danh sách...</td></tr>
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
            } catch (e) { document.getElementById("users-table").innerHTML = '<tr><td colspan="5" class="p-8 text-center text-red-500">Lỗi tải dữ liệu</td></tr>'; }
        }

        function renderUsers(users) {
            const tbody = document.getElementById("users-table");
            if (!users.length) { tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-400">Không tìm thấy người dùng.</td></tr>'; return; }
            
            tbody.innerHTML = users.map(user => {
                const isBanned = (user.Status || 'active') === 'banned';
                const statusBadge = isBanned 
                    ? '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">Bị khóa</span>' 
                    : '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Hoạt động</span>';
                
                const actionButton = user.Role === 'admin' 
                    ? '<span class="text-xs text-slate-300">—</span>'
                    : (isBanned 
                        ? `<button onclick="toggleUserStatus(${user.ID}, 'active')" class="text-emerald-600 font-bold hover:underline text-xs">Mở khóa</button>`
                        : `<button onclick="toggleUserStatus(${user.ID}, 'banned')" class="text-red-600 font-bold hover:underline text-xs">Khóa</button>`);

                return `<tr class="hover:bg-slate-50">
                    <td class="p-4">${escapeHtml(user.Username)}</td>
                    <td class="p-4 text-slate-600">${escapeHtml(user.Email)}</td>
                    <td class="p-4">${user.Role === 'admin' ? 'Admin' : 'Người dùng'}</td>
                    <td class="p-4 text-center">${statusBadge}</td>
                    <td class="p-4 text-center">${actionButton}</td>
                </tr>`;
            }).join('');
        }

        async function toggleUserStatus(id, newStatus) {
            if (!confirm(newStatus === 'banned' ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?')) return;
            const res = await fetch("/Project-Web-Programming/backend/public/index.php/api/admin/users/update-status", {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id, status: newStatus })
            });
            if ((await res.json()).success) loadUsers();
            else alert("Cập nhật thất bại!");
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