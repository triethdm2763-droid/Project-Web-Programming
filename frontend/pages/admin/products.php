<?php
session_start();

require_once '../../../backend/src/config/Database.php';

use App\Config\Database;

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Kết nối database
$database = Database::getInstance();
$conn = $database->getConnection();

// Thống kê nhanh cho 2 thẻ ở đầu trang
$totalProducts = $conn->query("
    SELECT COUNT(*)
    FROM products
")->fetchColumn();

$activeProducts = $conn->query("
    SELECT COUNT(*)
    FROM products
    WHERE Status IN ('active', 'available')
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Quản lý sản phẩm</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-slate-50">

    <div class="flex">

        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-1 p-8">

            <h1 class="text-3xl font-bold mb-8">
                Quản lý sản phẩm
            </h1>

            <!-- Card thống kê -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">

                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <p class="text-slate-500">Tổng sản phẩm</p>
                    <h2 class="text-3xl font-bold text-blue-600">
                        <?= (int) $totalProducts ?>
                    </h2>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm">
                    <p class="text-slate-500">Đang bán</p>
                    <h2 class="text-3xl font-bold text-green-600">
                        <?= (int) $activeProducts ?>
                    </h2>
                </div>

            </div>

            <!-- Bảng -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

                <table class="w-full">

                    <thead class="bg-slate-100">

                        <tr>
                            <th class="p-4 text-left">Hình ảnh</th>
                            <th class="p-4 text-left">Tên sản phẩm</th>
                            <th class="p-4 text-left">Danh mục</th>
                            <th class="p-4 text-left">Người bán</th>
                            <th class="p-4 text-left">Giá</th>
                            <th class="p-4 text-center">Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody id="productTable">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Đang tải danh sách sản phẩm...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </main>

    </div>

    <script>
        let allProducts = [];

        async function loadProducts() {
            try {

                const res = await fetch(
                    "/Project-Web-Programming/backend/public/index.php/api/admin/products"
                );

                allProducts = await res.json();

                renderProducts(allProducts);

            } catch (error) {
                console.error(error);

                document.getElementById("productTable").innerHTML = `
                <tr>
                    <td colspan="6"
                        class="p-8 text-center text-red-500">
                        Không tải được danh sách sản phẩm
                    </td>
                </tr>
            `;
            }
        }

        function escapeHtml(text) {
            return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }

        function renderProducts(products) {

            if (!products || products.length === 0) {
                document.getElementById("productTable").innerHTML = `
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400">
                        Chưa có sản phẩm nào trong hệ thống
                    </td>
                </tr>
            `;
                return;
            }

            let html = "";

            products.forEach(product => {

                const image =
                    product.Image ?
                    `/Project-Web-Programming/backend/uploads/products/${product.Image}` :
                    "https://placehold.co/100x100?text=No+Image";

                const status =
                    (product.Status === "active" || product.Status === "available") ?
                    `
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                            Đang bán
                        </span>
                    ` :
                    product.Status === "sold" ?
                    `
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                            Đã bán
                        </span>
                    ` :
                    product.Status === "pending" ?
                    `
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm">
                            Chờ duyệt
                        </span>
                    ` :
                    `
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                            Ngừng bán
                        </span>
                    `;

                html += `
                <tr class="hover:bg-slate-50">

                    <td class="p-4">
                        <img
                            src="${image}"
                            onerror="this.onerror=null;this.src='https://placehold.co/100x100?text=No+Image';"
                            class="w-16 h-16 object-cover rounded-xl border"
                        >
                    </td>

                    <td class="p-4 font-medium">
                        ${escapeHtml(product.Name)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(product.CategoryName)}
                    </td>

                    <td class="p-4 text-slate-600">
                        ${escapeHtml(product.SellerName)}
                    </td>

                    <td class="p-4 font-semibold text-blue-600">
                        ${Number(product.Price)
                            .toLocaleString("vi-VN")} đ
                    </td>

                    <td class="p-4 text-center">
                        ${status}
                    </td>

                </tr>
            `;
            });

            document.getElementById("productTable").innerHTML = html;
        }

        loadProducts();
    </script>

</body>

</html>