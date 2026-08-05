<?php
require_once __DIR__ . '/../../components/session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('/frontend/pages/auth/login.php'));
    exit();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Người dùng');
$role = $_SESSION['role'] ?? 'user';
$avatarText = strtoupper(substr($username, 0, 2));

$storeName = "Cửa Hàng " . ($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Thành Viên');
$initials = '';
if (!empty($_SESSION['fullname'])) {
    $words = explode(' ', trim($_SESSION['fullname']));
    $initials .= mb_substr($words[0], 0, 1, 'UTF-8');
    if (count($words) > 1) {
        $initials .= mb_substr($words[count($words) - 1], 0, 1, 'UTF-8');
    }
} else {
    $initials = mb_substr($_SESSION['username'] ?? 'TV', 0, 2, 'UTF-8');
}
$initials = mb_strtoupper($initials, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Kênh Người Bán | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Profile Cửa Hàng -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 hover:shadow-md transition-all duration-300">
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                    <div id="store-avatar-container" class="w-20 h-20 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-3xl shadow-lg shadow-blue-500/20 shrink-0 transform hover:scale-105 transition-transform duration-300 overflow-hidden">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                            <h2 id="store-name-display" class="text-2xl font-extrabold text-slate-800 tracking-tight"><?php echo htmlspecialchars($storeName); ?></h2>
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Seller Pro</span>
                        </div>
                        <p class="text-sm text-slate-500 flex items-center justify-center sm:justify-start gap-1">
                            <span class="material-symbols-outlined text-[16px] text-slate-400">storefront</span>
                            <span>Kênh quản lý sản phẩm thanh lý</span>
                        </p>
                        <p class="text-xs text-slate-400 flex items-center justify-center sm:justify-start gap-1">
                            <span class="material-symbols-outlined text-[14px]">account_circle</span>
                            <span>Tài khoản: <strong><?php echo $username; ?></strong> (<?php echo $role === 'admin' ? 'Quản trị viên' : 'Thành viên chuyên nghiệp'; ?>)</span>
                        </p>
                    </div>
                </div>
                <button onclick="window.location.href='/frontend/pages/seller/post-ad.php'" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3.5 rounded-2xl text-sm transition-all duration-300 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <span class="material-symbols-outlined text-lg">add</span> Đăng tin mới
                </button>
            </div>

            <!-- Khối Thống Kê -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card Doanh Thu -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-500/30 hover:shadow-md transition-all duration-300">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Doanh Thu Tạm Tính</span>
                        <h4 id="seller-revenue" class="text-2xl font-extrabold text-slate-800 tracking-tight">0đ</h4>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                </div>

                <!-- Card Đơn Hàng Đã Giao -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-orange-500/30 hover:shadow-md transition-all duration-300">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Đơn hoàn thành</span>
                        <h4 id="seller-delivered-orders" class="text-2xl font-extrabold text-slate-800 tracking-tight">0 đơn</h4>
                    </div>
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-2xl">local_shipping</span>
                    </div>
                </div>

                <!-- Card Tổng tin đăng -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-emerald-500/30 hover:shadow-md transition-all duration-300">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Tổng tin đã đăng</span>
                        <h4 id="count-total" class="text-2xl font-extrabold text-slate-800 tracking-tight">0</h4>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                </div>

                <!-- Card Đã bán thành công -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-indigo-500/30 hover:shadow-md transition-all duration-300">
                    <div class="space-y-1">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Đã bán thành công</span>
                        <h4 id="count-sold" class="text-2xl font-extrabold text-slate-800 tracking-tight">0</h4>
                    </div>
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-2xl">verified</span>
                    </div>
                </div>
            </div>

            <!-- Danh Sách Sản Phẩm -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="flex border-b border-slate-100 bg-slate-50/50 px-4">
                    <button onclick="switchSellerTab('available')" id="tab-btn-available" class="seller-tab-btn py-4 px-6 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition-all duration-300 flex items-center gap-2">
                        <span>Đang bán</span>
                        <span id="count-tab-available" class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold">0</span>
                    </button>
                    <button onclick="switchSellerTab('pending')" id="tab-btn-pending" class="seller-tab-btn py-4 px-6 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all duration-300 flex items-center gap-2">
                        <span>Chờ duyệt</span>
                        <span id="count-tab-pending" class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full font-semibold">0</span>
                    </button>
                    <button onclick="switchSellerTab('sold')" id="tab-btn-sold" class="seller-tab-btn py-4 px-6 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all duration-300 flex items-center gap-2">
                        <span>Đã bán</span>
                        <span id="count-tab-sold" class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full font-semibold">0</span>
                    </button>
                </div>
                
                <div id="seller-products-list" class="p-6 min-h-[300px]">
                    <p class="text-center text-slate-400 py-12 flex flex-col items-center justify-center gap-2">
                        <span class="material-symbols-outlined animate-spin text-3xl text-blue-500">progress_activity</span>
                        <span>Đang tải dữ liệu...</span>
                    </p>
                </div>
            </div>

        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script src="/frontend/assets/js/products.js?v=20260702-1"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tải dữ liệu thống kê từ API thực tế của backend
            fetchSellerStoreStats();
        });

        function fetchSellerStoreStats() {
            // 1. Tải các số liệu tổng quan (Doanh thu, Đơn hàng đã giao, Tổng tin đăng, Đã bán thành công)
            fetch('/backend/public/index.php/api/seller/stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(result => {
                    if (result && result.data) {
                        const data = result.data;
                        document.getElementById('seller-revenue').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.revenue || 0);
                        document.getElementById('seller-delivered-orders').innerText = (data.delivered_orders || 0) + ' đơn';
                        document.getElementById('count-total').innerText = data.total_products || 0;
                        document.getElementById('count-sold').innerText = data.sold_products || 0;
                    }
                })
                .catch(() => {});

            // 2. Tải toàn bộ danh sách sản phẩm không lọc để đếm số lượng cho từng Tab
            fetch('/backend/public/index.php/api/products/mine', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(products => {
                    const items = Array.isArray(products) ? products : (products.data || []);
                    
                    const availableCount = items.filter(p => {
                        const s = (p.Status || p.status || '').toLowerCase();
                        return s === 'available' || s === 'active';
                    }).length;
                    
                    const pendingCount = items.filter(p => (p.Status || p.status || '').toLowerCase() === 'pending').length;
                    const soldCount = items.filter(p => (p.Status || p.status || '').toLowerCase() === 'sold').length;

                    document.getElementById('count-tab-available').innerText = availableCount;
                    document.getElementById('count-tab-pending').innerText = pendingCount;
                    document.getElementById('count-tab-sold').innerText = soldCount;
                })
                .catch(() => {});

            // 3. Tải thông tin người bán thực tế (Avatar, Fullname) để cập nhật Store Profile
            fetch('/backend/public/index.php/api/auth/me', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data && data.user) {
                        const user = data.user;
                        const fullname = user.Fullname || user.Username || 'Thành Viên';
                        document.getElementById('store-name-display').innerText = "Cửa Hàng " + fullname;
                        
                        // Cập nhật Avatar nếu có hình ảnh
                        const avatarContainer = document.getElementById('store-avatar-container');
                        if (user.Avatar) {
                            avatarContainer.innerHTML = `<img src="${user.Avatar}" class="w-full h-full object-cover" alt="Store Avatar">`;
                        } else {
                            // Tạo initials từ fullname mới nhất
                            let words = fullname.trim().split(' ');
                            let init = '';
                            if (words.length > 0 && words[0]) {
                                init += words[0].substring(0, 1);
                                if (words.length > 1 && words[words.length - 1]) {
                                    init += words[words.length - 1].substring(0, 1);
                                }
                            } else {
                                init = 'TV';
                            }
                            avatarContainer.innerText = init.toUpperCase();
                        }
                    }
                })
                .catch(() => {});
        }

        // Ghi đè hàm switchSellerTab để hiển thị sản phẩm đẹp mắt hơn dưới dạng các thẻ (cards)
        window.switchSellerTab = function(status) {
            // Cập nhật hoạt động của các nút tab
            const tabs = ['available', 'pending', 'sold'];
            tabs.forEach(t => {
                const btn = document.getElementById(`tab-btn-${t}`);
                if (!btn) return;
                if (t === status) {
                    btn.className = "seller-tab-btn py-4 px-6 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition-all duration-300 flex items-center gap-2";
                } else {
                    btn.className = "seller-tab-btn py-4 px-6 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all duration-300 flex items-center gap-2";
                }
            });

            const container = document.getElementById('seller-products-list');
            container.innerHTML = `
                <div class="text-center py-16 flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined animate-spin text-3xl text-blue-500">progress_activity</span>
                    <span class="text-sm text-slate-500">Đang tải danh sách sản phẩm...</span>
                </div>
            `;
            
            fetch(`/backend/public/index.php/api/products/mine?status=${encodeURIComponent(status)}`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(products => {
                    const items = Array.isArray(products) ? products : (products.data || []);
                    if (!items.length) {
                        container.innerHTML = `
                            <div class="text-center py-16 space-y-3">
                                <span class="material-symbols-outlined text-4xl text-slate-300">inventory_2</span>
                                <p class="text-slate-400 text-sm">Không tìm thấy sản phẩm nào trong mục này.</p>
                            </div>
                        `;
                        return;
                    }

                    // Render danh sách sản phẩm dạng Grid hiện đại
                    container.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            ${items.map(p => {
                                const imgPath = p.Image || p.image;
                                const imgUrl = imgPath ? (imgPath.startsWith('http') ? imgPath : `/backend/uploads/products/${imgPath}`) : '/frontend/assets/images/default-product.png';
                                const formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p.Price || p.price || 0);
                                
                                let statusBadge = '';
                                const currentStatus = (p.Status || p.status || '').toLowerCase();
                                if (currentStatus === 'active' || currentStatus === 'available') {
                                    statusBadge = `<span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">Đang bán</span>`;
                                } else if (currentStatus === 'pending') {
                                    statusBadge = `<span class="bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">Chờ duyệt</span>`;
                                } else if (currentStatus === 'sold') {
                                    statusBadge = `<span class="bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">Đã bán</span>`;
                                } else {
                                    statusBadge = `<span class="bg-slate-50 text-slate-600 border border-slate-100 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">${currentStatus}</span>`;
                                }

                                return `
                                    <div class="bg-white rounded-2xl border border-slate-100 p-4 flex gap-4 hover:shadow-md hover:border-blue-500/20 transition-all duration-300 group">
                                        <div class="w-24 h-24 rounded-xl bg-slate-50 overflow-hidden shrink-0 border border-slate-100 relative">
                                            <img src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="${escapeHtml(p.Name || p.name)}" onerror="this.src='/frontend/assets/images/default-product.png'">
                                        </div>
                                        <div class="flex-grow flex flex-col justify-between py-1 min-w-0">
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    ${statusBadge}
                                                    <span class="text-[11px] text-slate-400 flex items-center gap-0.5">
                                                        <span class="material-symbols-outlined text-[12px]">schedule</span>
                                                        ${new Date(p.created_at || Date.now()).toLocaleDateString('vi-VN')}
                                                    </span>
                                                </div>
                                                <h3 class="font-bold text-slate-800 text-sm line-clamp-1 group-hover:text-blue-600 transition-colors">${escapeHtml(p.Name || p.name)}</h3>
                                                <div class="flex justify-between items-center mt-1">
                                                    <div class="text-blue-600 font-extrabold text-base">${formattedPrice}</div>
                                                    <span class="text-[11px] text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100 font-medium">
                                                        ${parseInt(p.Stock_quantity ?? p.stock_quantity ?? 1) === 1 ? 'Độc bản' : 'SL: ' + (p.Stock_quantity ?? p.stock_quantity ?? 1)}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 border-t border-slate-50 pt-2">
                                                <button onclick="editProduct(${p.ID || p.id})" class="flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-blue-600 py-1 px-3 rounded-lg hover:bg-blue-50 transition-all">
                                                    <span class="material-symbols-outlined text-sm">edit</span>
                                                    <span>Sửa</span>
                                                </button>
                                                <button onclick="deleteProduct(${p.ID || p.id})" class="flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-red-600 py-1 px-3 rounded-lg hover:bg-red-50 transition-all">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                    <span>Xóa</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                })
                .catch(() => {
                    container.innerHTML = `
                        <div class="text-center py-16 text-slate-400">
                            <p>Đã xảy ra lỗi khi tải danh sách sản phẩm.</p>
                        </div>
                    `;
                });
        };

        // Ghi đè hàm xóa sản phẩm để tự động cập nhật lại stats/counts sau khi xóa thành công
        const originalDeleteProduct = window.deleteProduct;
        window.deleteProduct = function(id) {
            if (!confirm("Xác nhận xóa tin này?")) return;
            fetch('/backend/public/index.php/api/products/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(res => {
                if (res.ok) {
                    if (typeof showToast === 'function') {
                        showToast('Đã xóa sản phẩm thành công', 'success');
                    } else {
                        alert('Đã xóa sản phẩm thành công');
                    }
                    // Cập nhật lại số liệu thống kê và danh sách sản phẩm
                    fetchSellerStoreStats();
                    switchSellerTab('available');
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Xóa sản phẩm thất bại', 'error');
                    } else {
                        alert('Xóa sản phẩm thất bại');
                    }
                }
            }).catch(() => {
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra khi kết nối server', 'error');
                }
            });
        };
    </script>
</body>
</html>
