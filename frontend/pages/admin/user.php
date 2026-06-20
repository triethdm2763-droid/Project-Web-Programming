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
    <title>Quản lý người dùng</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-slate-50">

<div class="flex">

    <?php include '../../components/sidebar.php'; ?>

    <main class="flex-1 p-8">

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-800">
                Quản lý người dùng
            </h1>

            <p class="text-slate-500 mt-2">
                Danh sách tài khoản trong hệ thống
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border overflow-hidden">

            <div class="p-6 border-b">
                <input
                    id="user-search-input"
                    type="text"
                    placeholder="Tìm kiếm người dùng..."
                    class="w-full border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                    oninput="filterUsers()"
                >
            </div>

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-4 text-left">Tên</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Vai trò</th>
                            <th class="p-4 text-center">Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody
                        id="users-table"
                        class="divide-y"
                    >
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">
                                Đang tải danh sách người dùng...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

<script>
    let allUsers = [];

    function escapeHtml(text) {
        return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
    }

    function renderUsers(users) {
        const tbody = document.getElementById("users-table");

        if (!users || users.length === 0) {
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

        users.forEach(user => {

            const role =
                user.Role === "admin"
                    ? "Quản trị viên"
                    : "Người dùng";

            html += `
                <tr class="border-b hover:bg-slate-50">

                    <td class="p-4 font-medium">
                        ${escapeHtml(user.Username)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(user.Email)}
                    </td>

                    <td class="p-4">
                        ${role}
                    </td>

                    <td class="p-4 text-center">
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                            Hoạt động
                        </span>
                    </td>

                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function filterUsers() {
        const keyword = document.getElementById("user-search-input").value.trim().toLowerCase();

        if (!keyword) {
            renderUsers(allUsers);
            return;
        }

        const filtered = allUsers.filter(user =>
            (user.Username || '').toLowerCase().includes(keyword) ||
            (user.Email || '').toLowerCase().includes(keyword)
        );

        renderUsers(filtered);
    }

    async function loadUsers() {
        try {
            const res = await fetch(
                "/Project-Web-Programming/backend/public/index.php/api/admin/users"
            );

            allUsers = await res.json();

            renderUsers(allUsers);

        } catch (error) {
            console.error(error);

            document.getElementById("users-table").innerHTML = `
                <tr>
                    <td colspan="4" class="p-8 text-center text-red-500">
                        Không tải được danh sách người dùng
                    </td>
                </tr>
            `;
        }
    }

    loadUsers();
</script>

</body>
</html>
