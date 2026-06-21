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

// Doanh thu hôm nay (các giao dịch đã thanh toán thành công trong ngày hôm nay)
$todayRevenue = $conn->query("
    SELECT IFNULL(SUM(Amount), 0)
    FROM payments
    WHERE Status = 'success'
        AND DATE(created_at) = CURDATE()
")->fetchColumn();

// Tổng số giao dịch (đơn hàng) trong toàn hệ thống
$totalOrders = $conn->query("
    SELECT COUNT(*)
    FROM orders
")->fetchColumn();

// Doanh thu trong tháng hiện tại
$monthRevenue = $conn->query("
    SELECT IFNULL(SUM(Amount), 0)
    FROM payments
    WHERE Status = 'success'
        AND MONTH(created_at) = MONTH(CURDATE())
        AND YEAR(created_at) = YEAR(CURDATE())
")->fetchColumn();

// Doanh thu theo từng ngày trong 7 ngày gần nhất (dùng để vẽ biểu đồ)
$revenueByDayStmt = $conn->query("
    SELECT
        DATE(created_at) AS revenue_date,
        IFNULL(SUM(Amount), 0) AS revenue_amount
    FROM payments
    WHERE Status = 'success'
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
");
$revenueByDayRaw = $revenueByDayStmt->fetchAll();

// Gộp dữ liệu vào đủ 7 ngày liên tiếp (kể cả ngày không có giao dịch nào -> doanh thu = 0)
$revenueMap = [];
foreach ($revenueByDayRaw as $row) {
    $revenueMap[$row['revenue_date']] = (float) $row['revenue_amount'];
}

$chartLabels = [];
$chartValues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} day"));
    $chartLabels[] = date('d/m', strtotime($date));
    $chartValues[] = $revenueMap[$date] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Báo cáo doanh thu</title>
    <?php include '../../components/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>

<body class="bg-slate-50">

    <div class="flex">

        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-1 p-8">

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">
                    Báo cáo doanh thu
                </h1>

                <p class="text-slate-500 mt-2">
                    Thống kê doanh thu và giao dịch hệ thống
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-8">

                <div class="bg-blue-600 p-6 rounded-3xl text-white">
                    <p class="text-blue-100">
                        Doanh thu hôm nay
                    </p>

                    <h2
                        id="todayRevenue"
                        class="text-4xl font-black mt-3">
                        <?= number_format((float) $todayRevenue) ?>đ
                    </h2>
                </div>

                <div class="bg-green-600 p-6 rounded-3xl text-white">
                    <p class="text-green-100">
                        Tổng giao dịch
                    </p>

                    <h2
                        id="totalOrders"
                        class="text-4xl font-black mt-3">
                        <?= (int) $totalOrders ?>
                    </h2>
                </div>

                <div class="bg-orange-600 p-6 rounded-3xl text-white">
                    <p class="text-orange-100">
                        Doanh thu tháng
                    </p>

                    <h2
                        id="monthRevenue"
                        class="text-4xl font-black mt-3">
                        <?= number_format((float) $monthRevenue) ?>đ
                    </h2>
                </div>

            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border">
                <h3 class="font-bold text-slate-900 text-lg mb-4">
                    Doanh thu 7 ngày gần nhất
                </h3>
                <canvas id="revenueChart"></canvas>
            </div>

        </main>

    </div>

    <script>
        // Dữ liệu doanh thu 7 ngày gần nhất được PHP tính sẵn từ server
        const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
        const chartValues = <?= json_encode($chartValues) ?>;

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: chartValues,
                    backgroundColor: '#2563eb',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => Number(value).toLocaleString('vi-VN') + 'đ'
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>