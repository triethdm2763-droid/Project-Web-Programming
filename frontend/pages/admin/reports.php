<?php
require_once __DIR__ . '/../../components/session.php';

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
    <title>Báo cáo doanh thu | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>

<body class="bg-slate-50 font-body-md text-on-surface">

    <div class="flex min-h-screen">

        <?php include '../../components/sidebar.php'; ?>

        <main class="flex-grow p-8 max-w-7xl mx-auto w-full">

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800">
                    Báo cáo doanh thu
                </h1>

                <p class="text-slate-500 mt-2">
                    Thống kê doanh thu và giao dịch hệ thống thời gian thực
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                <!-- Doanh thu hôm nay -->
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Doanh thu hôm nay
                        </p>
                        <h2 id="todayRevenue" class="text-2xl font-bold text-primary mt-1">
                            <?= number_format((float) $todayRevenue) ?> đ
                        </h2>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]">payments</span>
                    </div>
                </div>

                <!-- Tổng giao dịch -->
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Tổng giao dịch (đơn hàng)
                        </p>
                        <h2 id="totalOrders" class="text-2xl font-bold text-slate-800 mt-1">
                            <?= (int) $totalOrders ?>
                        </h2>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-outlined text-[28px]">shopping_cart</span>
                    </div>
                </div>

                <!-- Doanh thu tháng -->
                <div class="bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-outline-variant/10 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Doanh thu tháng này
                        </p>
                        <h2 id="monthRevenue" class="text-2xl font-bold text-tertiary mt-1">
                            <?= number_format((float) $monthRevenue) ?> đ
                        </h2>
                    </div>
                    <div class="w-12 h-12 bg-orange-500/10 rounded-xl flex items-center justify-center text-tertiary">
                        <span class="material-symbols-outlined text-[28px]">trending_up</span>
                    </div>
                </div>

            </div>

            <div class="bg-white/60 backdrop-blur-md p-8 rounded-2xl border border-outline-variant/10 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-6">
                    Doanh thu 7 ngày gần nhất
                </h3>
                <div class="w-full max-h-[400px]">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

        </main>

    </div>

    <script>
        // Dữ liệu doanh thu 7 ngày gần nhất được PHP tính sẵn từ server
        const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
        const chartValues = <?= json_encode($chartValues) ?>;

        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Cấu hình style màu sắc cho biểu đồ phù hợp theme
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#004ac6';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: chartValues,
                    backgroundColor: primaryColor,
                    hoverBackgroundColor: primaryColor + 'cc',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: {
                            family: 'Inter',
                            size: 13,
                            weight: '600'
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return ' Doanh thu: ' + Number(context.raw).toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 12
                            },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(241, 245, 249, 0.8)'
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 12
                            },
                            color: '#64748b',
                            callback: (value) => Number(value).toLocaleString('vi-VN') + ' đ'
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>