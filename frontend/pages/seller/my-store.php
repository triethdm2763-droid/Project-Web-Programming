<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /Project-Web-Programming/frontend/pages/auth/login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Người dùng');
$role = $_SESSION['role'] ?? 'user';
$avatarText = strtoupper(substr($username, 0, 2));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Kênh Người Bán | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">
        <div class="max-w-5xl mx-auto space-y-6">

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$storeName = "Cửa Hàng " . ($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Thành Viên');
$initials = '';
if (!empty($_SESSION['fullname'])) {
    $words = explode(' ', $_SESSION['fullname']);
    $initials .= mb_substr($words[0], 0, 1, 'UTF-8');
    if (count($words) > 1) {
        $initials .= mb_substr($words[count($words) - 1], 0, 1, 'UTF-8');
    }
} else {
    $initials = mb_substr($_SESSION['username'] ?? 'TV', 0, 2, 'UTF-8');
}
$initials = mb_strtoupper($initials, 'UTF-8');
?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                    <div class="w-16 h-16 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-md shadow-blue-100 shrink-0">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 justify-center sm:justify-start">
                            <h2 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($storeName); ?></h2>
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Seller Pro</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1 flex items-center justify-center sm:justify-start gap-1">
                            <span class="material-symbols-outlined text-xs">storefront</span> Hệ thống bán hàng & thanh lý chính chủ
                        </p>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        <?php echo $avatarText; ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold"><?php echo $username; ?></h2>
                        <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-full uppercase font-bold tracking-wider">
                            <?php echo $role === 'admin' ? 'Quản trị viên' : 'Người bán chuyên nghiệp'; ?>
                        </span>
                    </div>
                </div>
                <button onclick="window.location.href='/Project-Web-Programming/frontend/pages/seller/post-ad.php'" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">add</span> Đăng tin mới
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block">Doanh Thu Tạm Tính</span>
                        <h4 id="seller-revenue" class="text-2xl font-bold text-slate-900 tracking-tight">0đ</h4>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block">Đơn Hàng Đã Giao</span>
                        <h4 id="seller-delivered-orders" class="text-2xl font-bold text-slate-900 tracking-tight">0 đơn</h4>
                    </div>
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 uppercase font-bold">Tổng tin đăng</span>
                    <h4 class="text-2xl font-bold mt-1" id="count-total">0</h4>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 uppercase font-bold">Đã bán thành công</span>
                    <h4 class="text-2xl font-bold mt-1" id="count-sold">0</h4>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="flex border-b border-slate-100 bg-slate-50/50 px-4">
                    <button onclick="switchSellerTab('available')" id="tab-btn-available" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition-all">
                        Đang bán (<span id="count-available">0</span>)
                    </button>
                    <button onclick="switchSellerTab('pending')" id="tab-btn-pending" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-800 transition-all">
                        Chờ duyệt (<span id="count-pending">0</span>)
                    </button>
                    <button onclick="switchSellerTab('sold')" id="tab-btn-sold" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-800 transition-all">
                        Đã bán (<span id="count-sold">0</span>)
                    </button>
                <div class="flex border-b border-slate-100 px-4">
                    <button onclick="switchSellerTab('available')" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-blue-600 text-blue-600">Đang bán</button>
                    <button onclick="switchSellerTab('pending')" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400">Chờ duyệt</button>
                    <button onclick="switchSellerTab('sold')" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400">Đã bán</button>
                </div>
                <div id="seller-products-list" class="p-4 min-h-[200px]">
                    <p class="text-center text-slate-400 py-10">Đang tải dữ liệu...</p>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script src="/Project-Web-Programming/frontend/assets/js/products.js?v=20260621-1"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Lấy TOÀN BỘ tin đăng của CHÍNH người bán đang đăng nhập (mọi trạng thái) để tính thống kê.
            // (switchSellerTab('available') đã được products.js tự gọi sẵn khi phát hiện #seller-products-list)
            fetch(`/Project-Web-Programming/backend/public/index.php/api/products/mine`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    const prods = Array.isArray(data) ? data : (data.data || []);
                    document.getElementById('count-total').innerText = prods.length;
                    document.getElementById('count-sold').innerText = prods.filter(p => (p.Status || p.status) === 'sold').length;
                })
                .catch(() => {});
        });
    </script>
</body>
</html>