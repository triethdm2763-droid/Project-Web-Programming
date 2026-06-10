<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: /Project-Web-Programming/frontend/pages/auth/login.php");
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
                            <a href="/Project-Web-Programming/frontend/pages/seller/my-store.php" class="w-full block text-left px-4 py-2.5 rounded-xl font-medium text-on-surface-variant hover:bg-outline-variant/10 transition-colors">
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
                            <button type="submit" form="form-profile-update" class="px-5 py-2 bg-primary text-on-primary rounded-xl hover:opacity-90 transition-all text-sm font-medium shadow-sm cursor-pointer">Lưu thay đổi</button>
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
                                    <tr><td colspan="6" class="text-center py-8 text-outline-variant">Đang tải lịch sử mua hàng...</td></tr>
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
                                    <tr><td colspan="6" class="text-center py-8 text-outline-variant">Đang tải lịch sử bán hàng...</td></tr>
                                </tbody>
                            </table>
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
    }

    // Load user profile information
    async function loadUserProfile() {
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/me");
            if (res.ok) {
                let data = await res.json();
                let user = data.user;
                
                // Sidebar details
                document.getElementById('sidebar-fullname').textContent = user.Fullname || user.Username;
                document.getElementById('sidebar-created').textContent = "Thành viên từ: " + new Date(user.created_at).toLocaleDateString('vi-VN');
                
                let avatarUrl = user.Avatar || 'https://placehold.co/150x150';
                document.getElementById('sidebar-avatar').src = avatarUrl;
                document.getElementById('profile-avatar').src = avatarUrl;

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
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/orders/buyer");
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
                                <td class="py-4 px-4 font-medium text-primary">#${order.ID}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        ${order.ProductImage ? `<img src="/Project-Web-Programming/backend/uploads/products/${order.ProductImage}" class="w-8 h-8 rounded object-cover">` : ''}
                                        <span>${order.ProductName}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">${order.SellerName}</td>
                                <td class="py-4 px-4 font-semibold">${priceFormatted}</td>
                                <td class="py-4 px-4 text-outline-variant">${orderDate}</td>
                                <td class="py-4 px-4"><span class="${statusColor} px-2.5 py-1 rounded-full text-xs font-medium">${translateStatus(order.Status)}</span></td>
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

    // Load sales history
    async function loadSalesHistory() {
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/orders/seller");
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
                                <td class="py-4 px-4 font-medium text-primary">#${order.ID}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        ${order.ProductImage ? `<img src="/Project-Web-Programming/backend/uploads/products/${order.ProductImage}" class="w-8 h-8 rounded object-cover">` : ''}
                                        <span>${order.ProductName}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">${order.BuyerName}</td>
                                <td class="py-4 px-4 font-semibold">${priceFormatted}</td>
                                <td class="py-4 px-4 text-outline-variant">${orderDate}</td>
                                <td class="py-4 px-4"><span class="${statusColor} px-2.5 py-1 rounded-full text-xs font-medium">${translateStatus(order.Status)}</span></td>
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

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'pending': return 'bg-amber-100 text-amber-800';
            case 'confirmed': return 'bg-blue-100 text-blue-800';
            case 'completed': return 'bg-emerald-100 text-emerald-800';
            case 'cancelled': return 'bg-rose-100 text-rose-800';
            default: return 'bg-slate-100 text-slate-800';
        }
    }

    function translateStatus(status) {
        switch (status.toLowerCase()) {
            case 'pending': return 'Chờ xử lý';
            case 'confirmed': return 'Đã xác nhận';
            case 'completed': return 'Hoàn thành';
            case 'cancelled': return 'Đã hủy';
            default: return status;
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

    // Form profile submit handler
    document.getElementById('form-profile-update').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/profile/update", {
                method: "POST",
                body: formData
            });

            let result = await res.json();
            if (res.ok) {
                alert(result.message || "Cập nhật thông tin thành công!");
                loadUserProfile();
            } else {
                alert(result.error || "Cập nhật thất bại. Vui lòng kiểm tra lại thông tin.");
                if (result.errors) {
                    console.error("Validation errors:", result.errors);
                }
            }
        } catch (error) {
            console.error("Error updating profile:", error);
            alert("Lỗi kết nối đến máy chủ.");
        }
    });

    // Load everything on DOMContentLoaded
    document.addEventListener("DOMContentLoaded", () => {
        loadUserProfile();
        loadPurchaseHistory();
        loadSalesHistory();
    });
    </script>
</body>
</html>