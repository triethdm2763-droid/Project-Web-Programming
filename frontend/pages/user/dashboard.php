<?php
require_once __DIR__ . '/../../components/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('/frontend/pages/auth/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Tài khoản của tôi | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow max-w-7xl mx-auto px-gutter py-8 w-full">
        <div class="flex flex-col md:flex-row gap-8">

            <!-- THANH DỌC (SIDEBAR) - CHỈ GIỮ THÔNG TIN CÁ NHÂN KHÁI QUÁT -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm space-y-6">

                    <!-- Phần thông tin cá nhân chỉ giữ lại ở dọc -->
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant/20">
                        <img id="sidebar-avatar" src="https://placehold.co/100x100" alt="Avatar" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h3 id="sidebar-fullname" class="font-medium text-on-surface">Đang tải...</h3>
                            <p id="sidebar-created" class="text-xs text-outline-variant">Thành viên từ: ...</p>
                        </div>
                    </div>

                    <!-- Các tab điều hướng tính năng dọc -->
                    <ul class="space-y-2" id="dashboard-menu">
                        <li>
                            <button onclick="switchTab('profile')" id="btn-profile" class="w-full text-left px-4 py-2.5 rounded-xl font-medium text-primary bg-primary/10 transition-colors">
                                Thông tin cá nhân
                            </button>
                        </li>
                        <li>
                            <button onclick="switchTab('purchase')" id="btn-purchase" class="w-full text-left px-4 py-2.5 rounded-xl font-medium text-on-surface-variant hover:bg-outline-variant/10 transition-colors">
                                Lịch sử mua hàng
                            </button>
                        </li>
                        <li>
                            <button onclick="switchTab('sales')" id="btn-sales" class="w-full text-left px-4 py-2.5 rounded-xl font-medium text-on-surface-variant hover:bg-outline-variant/10 transition-colors">
                                Thông tin bán hàng
                            </button>
                        </li>
                        <li>
                            <button onclick="switchTab('notifications')" id="btn-notifications" class="w-full text-left px-4 py-2.5 rounded-xl font-medium text-on-surface-variant hover:bg-outline-variant/10 transition-colors flex justify-between items-center">
                                <span>Thông báo</span>
                                <span id="notification-badge" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full hidden">0</span>
                            </button>
                        </li>
                        <li>
                            <a href="/frontend/pages/seller/my-store.php" class="w-full block text-left px-4 py-2.5 rounded-xl font-medium text-on-surface-variant hover:bg-outline-variant/10 transition-colors">
                                Quản lí bán hàng
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- KHU VỰC HIỂN THỊ NỘI DUNG CHI TIẾT THEO TAB -->
            <section class="flex-grow">
                <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm">

                    <!-- TAB 1: THÔNG TIN CÁ NHÂN CHI TIẾT -->
                    <div id="tab-profile" class="tab-panel block">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-headline-sm font-bold text-on-surface">Thông tin cơ bản</h2>
                            <button type="submit" form="form-profile-update" class="px-5 py-2 bg-primary text-white rounded-xl hover:opacity-90 transition-all text-sm font-medium shadow-sm cursor-pointer">Lưu thay đổi</button>
                        </div>

                        <form id="form-profile-update" class="flex flex-col lg:flex-row gap-8">
                            <div class="flex flex-col items-center text-center space-y-2 flex-shrink-0">
                                <div class="relative w-32 h-32 rounded-full border border-outline-variant/40 overflow-hidden bg-surface">
                                    <img id="profile-avatar" src="https://placehold.co/150x150" alt="Avatar Edit" class="w-full h-full object-cover">
                                    <label class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-[10px] py-1 cursor-pointer text-center">
                                        Thay đổi
                                        <input type="file" id="input-avatar" name="avatar" accept="image/*" class="hidden">
                                    </label>
                                </div>
                            </div>

                            <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Họ và tên</label>
                                    <input type="text" id="input-fullname" name="fullname" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Email</label>
                                    <input type="email" id="input-email" class="w-full px-4 py-2.5 bg-surface-variant/20 border border-outline-variant/40 rounded-xl outline-none text-outline" readonly>
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại</label>
                                    <input type="tel" id="input-phone" name="phone" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none">
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ mặc định</label>
                                    <input type="text" id="input-address" name="address" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: LỊCH SỬ MUA HÀNG -->
                    <div id="tab-purchase" class="tab-panel hidden">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-6">Lịch sử mua hàng</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-outline-variant/40 text-label-sm text-outline-variant">
                                        <th class="py-3 px-4 font-semibold">Mã đơn</th>
                                        <th class="py-3 px-4 font-semibold">Sản phẩm</th>
                                        <th class="py-3 px-4 font-semibold">Người bán</th>
                                        <th class="py-3 px-4 font-semibold">Giá trị</th>
                                        <th class="py-3 px-4 font-semibold">Ngày mua</th>
                                        <th class="py-3 px-4 font-semibold">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="purchase-history-rows" class="text-body-md divide-y divide-outline-variant/20">
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-outline-variant">Đang tải lịch sử mua hàng...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: THÔNG TIN BÁN HÀNG -->
                    <div id="tab-sales" class="tab-panel hidden">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-6">Thông tin bán hàng</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-outline-variant/40 text-label-sm text-outline-variant">
                                        <th class="py-3 px-4 font-semibold">Mã đơn</th>
                                        <th class="py-3 px-4 font-semibold">Sản phẩm</th>
                                        <th class="py-3 px-4 font-semibold">Người mua</th>
                                        <th class="py-3 px-4 font-semibold">Giá trị</th>
                                        <th class="py-3 px-4 font-semibold">Ngày đặt</th>
                                        <th class="py-3 px-4 font-semibold">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="sales-history-rows" class="text-body-md divide-y divide-outline-variant/20">
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-outline-variant">Đang tải lịch sử bán hàng...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: THÔNG BÁO -->
                    <div id="tab-notifications" class="tab-panel hidden">
                        <div class="flex justify-between items-center mb-6 border-b border-outline-variant/20 pb-4">
                            <h2 class="text-headline-sm font-bold text-on-surface">Thông báo của tôi</h2>
                            <button onclick="markAllNotificationsAsRead()" class="text-sm text-primary font-semibold hover:underline">Đánh dấu tất cả đã đọc</button>
                        </div>
                        <div id="notifications-list" class="space-y-4 max-h-[500px] overflow-y-auto pr-2 divide-y divide-slate-100">
                            <div class="text-center py-8 text-outline-variant">Đang tải thông báo...</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden');
                panel.classList.remove('block');
            });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            document.getElementById('tab-' + tabId).classList.add('block');

            document.querySelectorAll('#dashboard-menu button').forEach(btn => {
                btn.classList.remove('text-primary', 'bg-primary/10');
                btn.classList.add('text-on-surface-variant');
            });
            document.getElementById('btn-' + tabId).classList.add('text-primary', 'bg-primary/10');
            document.getElementById('btn-' + tabId).classList.remove('text-on-surface-variant');

            if (tabId === 'notifications' && typeof loadNotifications === 'function') {
                loadNotifications();
            }
        }

        // Load user profile information
        async function loadUserProfile() {
            try {
                let res = await fetch("/backend/public/index.php/api/auth/me");
                if (res.ok) {
                    let data = await res.json();
                    let user = data.user;

                    // Sidebar details
                    document.getElementById('sidebar-fullname').textContent = user.Fullname || user.Username;
                    document.getElementById('sidebar-created').textContent = "Thành viên từ: " + new Date(user.created_at).toLocaleDateString('vi-VN');

                    let avatarUrl = 'https://placehold.co/150x150';
                    if (user && user.Avatar) {
                        avatarUrl = user.Avatar.startsWith('http') 
                            ? user.Avatar 
                            : (user.Avatar.startsWith('/') ? user.Avatar : '/backend/uploads/avatars/' + user.Avatar);
                    }
                    document.getElementById('sidebar-avatar').src = avatarUrl;
                    document.getElementById('profile-avatar').src = avatarUrl;
                    document.getElementById('input-avatar').value = '';

                    // Input fields
                    document.getElementById('input-fullname').value = user.Fullname || '';
                    document.getElementById('input-email').value = user.Email || '';
                    document.getElementById('input-phone').value = user.Phone || '';
                    document.getElementById('input-address').value = user.Address || '';
                } else {
                    console.error("Failed to load user profile");
                }
            } catch (error) {
                console.error("Error loading profile:", error);
            }
        }

        // Load purchase history
        async function loadPurchaseHistory() {
            try {
                let res = await fetch("/backend/public/index.php/api/orders/buyer");
                let rowsHtml = '';
                if (res.ok) {
                    let orders = await res.json();
                    if (orders && orders.length > 0) {
                        orders.forEach(order => {
                            let statusColor = getStatusClass(order.Status);
                            let priceFormatted = new Intl.NumberFormat('vi-VN').format(order.Total_price) + 'đ';
                            let orderDate = new Date(order.created_at).toLocaleDateString('vi-VN');

                            rowsHtml += `
                            <tr>
                                <td class="py-4 px-4 font-medium text-[#0066cc]"><a href="/frontend/pages/payment/track.php?id=${encodeURIComponent(order.Order_Code)}" class="hover:underline text-[#0066cc]" title="Tra cứu đơn hàng">${order.Order_Code}</a></td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        ${order.ProductImage ? `<img src="${order.ProductImage.split(',')[0].startsWith('http') ? order.ProductImage.split(',')[0] : '/backend/uploads/products/' + order.ProductImage.split(',')[0]}" class="w-8 h-8 rounded object-cover">` : ''}
                                        <span class="line-clamp-2">${order.ProductName}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">${order.SellerName}</td>
                                <td class="py-4 px-4 font-semibold">${priceFormatted}</td>
                                <td class="py-4 px-4 text-outline-variant">${orderDate}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="${statusColor} px-2.5 py-1 rounded-full text-xs font-medium">${translateStatus(order.Status)}</span>
                                        ${order.Status.toLowerCase() === 'pending' ? `
                                            <button onclick="cancelOrder(${order.ID})" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-xs font-semibold rounded-lg transition-colors border border-red-200/50" title="Hủy đơn hàng này">
                                                Hủy đơn
                                            </button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                        });
                    } else {
                        rowsHtml = `<tr><td colspan="6" class="text-center py-8 text-outline-variant">Chưa có dữ liệu lịch sử mua hàng.</td></tr>`;
                    }
                } else {
                    rowsHtml = `<tr><td colspan="6" class="text-center py-8 text-error">Lỗi khi tải lịch sử mua hàng.</td></tr>`;
                }
                document.getElementById('purchase-history-rows').innerHTML = rowsHtml;
            } catch (error) {
                console.error("Error loading purchase history:", error);
            }
        }

        // Cancel order
        async function cancelOrder(orderId) {
            if (!await showConfirm("Hủy đơn hàng", "Bạn có chắc chắn muốn hủy đơn hàng này không?")) {
                return;
            }

            try {
                let res = await fetch("/backend/public/index.php/api/orders/cancel", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });

                let data = await res.json();
                if (res.ok) {
                    showToast(data.message || "Hủy đơn hàng thành công!", "success");
                    loadPurchaseHistory(); // Reload purchase tab
                    loadSalesHistory(); // Reload sales tab
                } else {
                    showAlert("Thất bại", data.error || "Hủy đơn hàng thất bại.", "error");
                }
            } catch (error) {
                console.error("Cancel Order Error:", error);
                showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
            }
        }

        // Load sales history
        async function loadSalesHistory() {
            try {
                let res = await fetch("/backend/public/index.php/api/orders/seller");
                let rowsHtml = '';
                if (res.ok) {
                    let orders = await res.json();
                    if (orders && orders.length > 0) {
                        orders.forEach(order => {
                            let statusColor = getStatusClass(order.Status);
                            let priceFormatted = new Intl.NumberFormat('vi-VN').format(order.Total_price) + 'đ';
                            let orderDate = new Date(order.created_at).toLocaleDateString('vi-VN');

                            let actionButton = '';
                            const statusLower = order.Status.toLowerCase();
                            if (statusLower === 'pending') {
                                actionButton = `
                                    <button onclick="updateOrderStatus(${order.ID}, 'confirmed')" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200/50">
                                        Xác nhận
                                    </button>
                                    <button onclick="updateOrderStatus(${order.ID}, 'cancelled')" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-xs font-semibold rounded-lg transition-colors border border-red-200/50">
                                        Từ chối
                                    </button>
                                `;
                            } else if (statusLower === 'confirmed') {
                                actionButton = `
                                    <button onclick="updateOrderStatus(${order.ID}, 'completed')" class="px-2.5 py-1 bg-green-50 hover:bg-green-100 text-green-600 hover:text-green-700 text-xs font-semibold rounded-lg transition-colors border border-green-200/50">
                                        Hoàn thành
                                    </button>
                                    <button onclick="updateOrderStatus(${order.ID}, 'cancelled')" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-xs font-semibold rounded-lg transition-colors border border-red-200/50">
                                        Hủy đơn
                                    </button>
                                `;
                            }

                            rowsHtml += `
                            <tr>
                                <td class="py-4 px-4 font-medium text-[#0066cc]"><a href="/frontend/pages/payment/track.php?id=${encodeURIComponent(order.Order_Code)}" class="hover:underline text-[#0066cc]" title="Tra cứu đơn hàng">${order.Order_Code}</a></td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        ${order.ProductImage ? `<img src="${order.ProductImage.split(',')[0].startsWith('http') ? order.ProductImage.split(',')[0] : '/backend/uploads/products/' + order.ProductImage.split(',')[0]}" class="w-8 h-8 rounded object-cover">` : ''}
                                        <span class="line-clamp-2">${order.ProductName}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">${order.BuyerName}</td>
                                <td class="py-4 px-4 font-semibold">${priceFormatted}</td>
                                <td class="py-4 px-4 text-outline-variant">${orderDate}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="${statusColor} px-2.5 py-1 rounded-full text-xs font-medium">${translateStatus(order.Status)}</span>
                                        ${actionButton}
                                    </div>
                                </td>
                            </tr>
                        `;
                        });
                    } else {
                        rowsHtml = `<tr><td colspan="6" class="text-center py-8 text-outline-variant">Chưa có dữ liệu lịch sử bán hàng.</td></tr>`;
                    }
                } else {
                    rowsHtml = `<tr><td colspan="6" class="text-center py-8 text-error">Lỗi khi tải lịch sử bán hàng.</td></tr>`;
                }
                document.getElementById('sales-history-rows').innerHTML = rowsHtml;
            } catch (error) {
                console.error("Error loading sales history:", error);
            }
        }

        async function updateOrderStatus(orderId, newStatus) {
            let confirmMsg = "Bạn có chắc chắn muốn xác nhận đơn hàng này không?";
            if (newStatus === 'completed') {
                confirmMsg = "Xác nhận đơn hàng đã giao thành công và hoàn thành?";
            } else if (newStatus === 'cancelled') {
                confirmMsg = "Bạn có chắc chắn muốn hủy/từ chối đơn hàng này không?";
            }
            if (!await showConfirm("Cập nhật đơn hàng", confirmMsg, newStatus === 'completed' ? 'success' : (newStatus === 'cancelled' ? 'warning' : 'info'))) {
                return;
            }

            try {
                let res = await fetch("/backend/public/index.php/api/orders/status", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: newStatus
                    })
                });

                let data = await res.json();
                if (res.ok) {
                    showToast(data.message || "Cập nhật đơn hàng thành công!", "success");
                    loadSalesHistory();
                } else {
                    showAlert("Thất bại", data.error || "Cập nhật thất bại.", "error");
                }
            } catch (error) {
                console.error("Update Order Status Error:", error);
                showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
            }
        }

        function getStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'pending':
                    return 'bg-amber-100 text-amber-800';
                case 'confirmed':
                    return 'bg-blue-100 text-blue-800';
                case 'completed':
                    return 'bg-emerald-100 text-emerald-800';
                case 'cancelled':
                    return 'bg-rose-100 text-rose-800';
                default:
                    return 'bg-slate-100 text-slate-800';
            }
        }

        function translateStatus(status) {
            switch (status.toLowerCase()) {
                case 'pending':
                    return 'Chờ xử lý';
                case 'confirmed':
                    return 'Đã xác nhận';
                case 'completed':
                    return 'Hoàn thành';
                case 'cancelled':
                    return 'Đã hủy';
                default:
                    return status;
            }
        }

        // Instant avatar preview when file selected
        document.getElementById('input-avatar').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-avatar').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Hàm nén và chỉnh kích thước ảnh bằng HTML5 Canvas phía client
        function compressImage(file, maxDimension, quality = 0.8) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (blob) {
                                const originalName = file.name;
                                const dotIndex = originalName.lastIndexOf('.');
                                const baseName = dotIndex !== -1 ? originalName.substring(0, dotIndex) : originalName;
                                resolve(new File([blob], `${baseName}.jpg`, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                }));
                            } else {
                                reject(new Error('Canvas toBlob failed'));
                            }
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = (err) => reject(err);
                };
                reader.onerror = (err) => reject(err);
            });
        }

        // Form profile submit handler
        document.getElementById('form-profile-update').addEventListener('submit', async function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            const avatarInput = document.getElementById('input-avatar');
            if (avatarInput.files && avatarInput.files[0]) {
                const file = avatarInput.files[0];
                if (file.type.startsWith('image/')) {
                    try {
                        const compressedFile = await compressImage(file, 200, 0.8);
                        formData.set('avatar', compressedFile);
                    } catch (e) {
                        console.warn("Lỗi nén avatar phía client, gửi file gốc:", e);
                    }
                }
            }

            try {
                let res = await fetch("/backend/public/index.php/api/auth/profile/update", {
                    method: "POST",
                    body: formData
                });

                let result = await res.json();
                if (res.ok) {
                    showToast(result.message || "Cập nhật thông tin thành công!", "success");
                    loadUserProfile();
                } else {
                    showAlert("Thất bại", result.error || "Cập nhật thất bại. Vui lòng kiểm tra lại thông tin.", "error");
                    if (result.errors) {
                        console.error("Validation errors:", result.errors);
                    }
                }
            } catch (error) {
                console.error("Error updating profile:", error);
                showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
            }
        });

        // Load everything on DOMContentLoaded
        document.addEventListener("DOMContentLoaded", () => {
            loadUserProfile();
            loadPurchaseHistory();
            loadSalesHistory();
            loadNotifications();
        });

        let notificationsData = [];

        // Load notifications
        async function loadNotifications() {
            try {
                let res = await fetch("/backend/public/index.php/api/notifications");
                if (res.ok) {
                    let notifications = await res.json();
                    notificationsData = notifications || [];

                    // Count unread
                    let unread = notificationsData.filter(n => parseInt(n.Is_read || n.is_read || 0) === 0);
                    let badge = document.getElementById("notification-badge");
                    if (badge) {
                        if (unread.length > 0) {
                            badge.innerText = unread.length;
                            badge.classList.remove("hidden");
                        } else {
                            badge.classList.add("hidden");
                        }
                    }

                    // Render list
                    let listEl = document.getElementById("notifications-list");
                    if (listEl) {
                        if (notificationsData.length === 0) {
                            listEl.innerHTML = `<div class="text-center py-12 text-slate-400 text-sm">Bạn chưa có thông báo nào.</div>`;
                            return;
                        }

                        listEl.innerHTML = notificationsData.map(n => {
                            const isUnread = parseInt(n.Is_read || n.is_read || 0) === 0;
                            const createdDate = new Date(n.created_at).toLocaleString('vi-VN');
                            return `
                                <div class="p-4 rounded-xl flex items-start justify-between gap-4 transition-all ${isUnread ? 'bg-blue-50/50 border-l-4 border-blue-500' : 'bg-transparent'}">
                                    <div class="space-y-1">
                                        <h4 class="font-semibold text-slate-800 text-sm ${isUnread ? 'text-blue-900 font-bold' : ''}">${escapeHtml(n.Title)}</h4>
                                        <p class="text-xs text-slate-500">${escapeHtml(n.Content)}</p>
                                        <span class="text-[10px] text-slate-400 block">${createdDate}</span>
                                    </div>
                                    ${isUnread ? `
                                        <button onclick="markNotificationAsRead(${n.ID})" class="px-2.5 py-1 bg-white hover:bg-slate-50 text-blue-600 border border-blue-100 rounded-lg text-xs font-semibold shadow-sm transition-colors whitespace-nowrap">
                                            Đã đọc
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                        }).join('');
                    }
                }
            } catch (err) {
                console.error("Lỗi khi tải thông báo:", err);
            }
        }

        async function markNotificationAsRead(id) {
            try {
                let res = await fetch("/backend/public/index.php/api/notifications/read", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: id
                    })
                });
                if (res.ok) {
                    loadNotifications();
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function markAllNotificationsAsRead() {
            let unread = notificationsData.filter(n => parseInt(n.Is_read || n.is_read || 0) === 0);
            if (unread.length === 0) return;

            try {
                for (let n of unread) {
                    await fetch("/backend/public/index.php/api/notifications/read", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: n.ID
                        })
                    });
                }
                showToast("Đã đánh dấu tất cả thông báo là đã đọc!", "success");
                loadNotifications();
            } catch (err) {
                console.error(err);
            }
        }

        function escapeHtml(text) {
            return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }
    </script>
</body>

</html>
