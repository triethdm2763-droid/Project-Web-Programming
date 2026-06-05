<?php
// Giả định ông đã có data từ Controller/DB đổ vào các biến này:
// $user_profile = [...];
// $purchase_history = [...];
// $sales_history = [...];
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
                        <img src="<?= !empty($user_profile['avatar']) ? htmlspecialchars($user_profile['avatar']) : 'https://placehold.co/100x100' ?>" alt="Avatar" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h3 class="font-medium text-on-surface"><?= htmlspecialchars($user_profile['fullname'] ?? '') ?></h3>
                            <p class="text-xs text-outline-variant">Thành viên từ: <?= htmlspecialchars($user_profile['created_at'] ?? '') ?></p>
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
                            <button type="submit" form="form-profile-update" class="px-5 py-2 bg-primary text-on-primary rounded-xl hover:opacity-90 transition-all text-sm font-medium shadow-sm">Lưu thay đổi</button>
                        </div>

                        <form id="form-profile-update" class="flex flex-col lg:flex-row gap-8">
                            <div class="flex flex-col items-center text-center space-y-2 flex-shrink-0">
                                <div class="relative w-32 h-32 rounded-full border border-outline-variant/40 overflow-hidden bg-surface">
                                    <img src="<?= !empty($user_profile['avatar']) ? htmlspecialchars($user_profile['avatar']) : 'https://placehold.co/150x150' ?>" alt="Avatar Edit" class="w-full h-full object-cover">
                                    <label class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-[10px] py-1 cursor-pointer text-center">
                                        Thay đổi
                                        <input type="file" name="avatar" class="hidden">
                                    </label>
                                </div>
                            </div>

                            <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Họ và tên</label>
                                    <input type="text" name="fullname" value="<?= htmlspecialchars($user_profile['fullname'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none">
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Email</label>
                                    <input type="email" value="<?= htmlspecialchars($user_profile['email'] ?? '') ?>" class="w-full px-4 py-2.5 bg-surface-variant/20 border border-outline-variant/40 rounded-xl outline-none text-outline" readonly>
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại</label>
                                    <input type="tel" name="phone" value="<?= htmlspecialchars($user_profile['phone'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none">
                                </div>
                                <div>
                                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ mặc định</label>
                                    <input type="text" name="address" value="<?= htmlspecialchars($user_profile['address'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none">
                                </div>
                            </form>
                        </div>
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
                                        <th class="py-3 px-4 font-semibold">Giá trị</th>
                                        <th class="py-3 px-4 font-semibold">Ngày mua</th>
                                        <th class="py-3 px-4 font-semibold">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="text-body-md divide-y divide-outline-variant/20">
                                    <?php if (!empty($purchase_history)): ?>
                                        <?php foreach ($purchase_history as $order): ?>
                                        <tr>
                                            <td class="py-4 px-4 font-medium text-primary">#<?= htmlspecialchars($order['id'] ?? '') ?></td>
                                            <td class="py-4 px-4"><?= htmlspecialchars($order['product_name'] ?? '') ?></td>
                                            <td class="py-4 px-4 font-semibold"><?= number_format($order['price'] ?? 0, 0, ',', '.') ?>đ</td>
                                            <td class="py-4 px-4 text-outline-variant"><?= htmlspecialchars($order['order_date'] ?? '') ?></td>
                                            <td class="py-4 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium"><?= htmlspecialchars($order['status'] ?? '') ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-8 text-outline-variant">Chưa có dữ liệu lịch sử mua hàng.</td></tr>
                                    <?php endif; ?>
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
                                        <th class="py-3 px-4 font-semibold">Mã tin</th>
                                        <th class="py-3 px-4 font-semibold">Sản phẩm</th>
                                        <th class="py-3 px-4 font-semibold">Giá đăng</th>
                                        <th class="py-3 px-4 font-semibold">Ngày đăng</th>
                                        <th class="py-3 px-4 font-semibold">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="text-body-md divide-y divide-outline-variant/20">
                                    <?php if (!empty($sales_history)): ?>
                                        <?php foreach ($sales_history as $product): ?>
                                        <tr>
                                            <td class="py-4 px-4 font-medium text-primary">#<?= htmlspecialchars($product['id'] ?? '') ?></td>
                                            <td class="py-4 px-4"><?= htmlspecialchars($product['name'] ?? '') ?></td>
                                            <td class="py-4 px-4 font-semibold"><?= number_format($product['price'] ?? 0, 0, ',', '.') ?>đ</td>
                                            <td class="py-4 px-4 text-outline-variant"><?= htmlspecialchars($product['created_at'] ?? '') ?></td>
                                            <td class="py-4 px-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium"><?= htmlspecialchars($product['status'] ?? '') ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-8 text-outline-variant">Chưa có dữ liệu lịch sử bán hàng.</td></tr>
                                    <?php endif; ?>
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
    </script>
</body>
</html>