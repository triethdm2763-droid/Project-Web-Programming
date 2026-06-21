<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine current path so we can highlight active nav link
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

function nav_class($pathFragment, $currentPath)
{
    if ($pathFragment === '/' && ($currentPath === '/' || $currentPath === '/Project-Web-Programming' || $currentPath === '/Project-Web-Programming/')) {
        return 'text-primary border-b-2 border-primary pb-1';
    }
    return (strpos($currentPath, $pathFragment) !== false) ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary transition-colors duration-200';
}
?>

<header class="bg-surface/80 backdrop-blur-md font-body-md text-body-md docked full-width top-0 sticky z-50 shadow-sm border-b border-outline-variant/30">
    <div class="flex justify-between items-center px-gutter py-3 max-w-container-max mx-auto w-full">

        <div class="flex items-center gap-8">
            <a class="font-headline-md text-headline-md font-bold text-primary tracking-tight" href="/Project-Web-Programming/frontend/pages/home/index.php">Chợ Cũ</a>
            <nav class="hidden md:flex gap-6 font-medium text-[16px]">
                <a class="<?php echo nav_class('/frontend/pages/home/index.php', $currentPath); ?>" href="/Project-Web-Programming/frontend/pages/home/index.php">Trang chủ</a>
                <a class="<?php echo nav_class('/frontend/pages/products/category.php', $currentPath); ?>" href="/Project-Web-Programming/frontend/pages/products/category.php" data-navigate="1">Danh mục</a>
            </nav>
        </div>

        <div class="flex-1 max-w-xl mx-8 hidden md:block">
            <div class="relative group">
                <span id="search-btn" class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors cursor-pointer hover:text-primary">search</span>
                <input id="search-input" class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-full border-none focus:ring-2 focus:ring-primary/20 transition-all text-[15px]" placeholder="Tìm kiếm sản phẩm đồ cũ..." type="text" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="/Project-Web-Programming/frontend/pages/cart/index.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative block">
                shopping_cart
                <span id="nav-cart-badge" class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
            </a>

            <?php
            $isLoggedIn = isset($_SESSION['user_id']);
            ?>

            <?php if ($isLoggedIn): ?>
                <div class="flex items-center gap-2">
                    <!-- Notification Icon & Dropdown -->
                    <div class="relative">
                        <button id="nav-btn-notifications" onclick="toggleNavNotificationsDropdown(event)" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative block cursor-pointer" title="Thông báo">
                            notifications
                            <span id="nav-notification-badge" class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
                        </button>

                        <!-- Dropdown Container -->
                        <div id="nav-notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white border border-outline-variant/40 rounded-xl shadow-lg overflow-hidden z-[100] hidden">
                            <div class="p-3 border-b border-outline-variant/20 flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-800">Thông báo</span>
                                <button onclick="markAllNavNotificationsAsRead(event)" class="text-xs text-primary font-semibold hover:underline">Đọc tất cả</button>
                            </div>
                            <div id="nav-notifications-list" class="max-h-80 overflow-y-auto divide-y divide-slate-100 p-2 space-y-1">
                                <div class="text-center py-6 text-slate-400 text-xs">Đang tải thông báo...</div>
                            </div>
                        </div>
                    </div>

                    <a href="<?php echo ($_SESSION['role'] === 'admin') ? '/Project-Web-Programming/frontend/pages/admin/dashboard.php' : '/Project-Web-Programming/frontend/pages/user/dashboard.php'; ?>" class="flex items-center gap-1.5 p-1.5 hover:bg-surface-container rounded-full transition-colors text-on-surface-variant hover:text-primary">
                        <span class="material-symbols-outlined">account_circle</span>
                        <span class="text-sm font-semibold max-w-[100px] truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </a>
                    <button onclick="logout()" class="material-symbols-outlined p-2 text-on-surface-variant hover:text-error hover:bg-surface-container rounded-full transition-colors" title="Đăng xuất">
                        logout
                    </button>
                </div>
            <?php else: ?>
                <a href="/Project-Web-Programming/frontend/pages/auth/login.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors block" title="Đăng nhập">
                    account_circle
                </a>
            <?php endif; ?>

            <a id="btn-create-post" data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>"
                href="/Project-Web-Programming/frontend/pages/seller/post-ad.php"
                class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 active:scale-95 transition-all shadow-sm text-[15px] cursor-pointer inline-block text-center">
                Đăng tin
            </a>
        </div>

    </div>
</header>

<script>
    // Notification JS logic
    let navNotificationsData = [];

    async function loadNavNotifications() {
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/notifications");
            if (res.ok) {
                let notifications = await res.json();
                navNotificationsData = notifications || [];

                // Count unread
                let unread = navNotificationsData.filter(n => parseInt(n.Is_read || n.is_read || 0) === 0);
                let badge = document.getElementById("nav-notification-badge");
                if (badge) {
                    if (unread.length > 0) {
                        badge.innerText = unread.length;
                        badge.classList.remove("hidden");
                    } else {
                        badge.classList.add("hidden");
                    }
                }

                // Render list
                let listEl = document.getElementById("nav-notifications-list");
                if (listEl) {
                    if (navNotificationsData.length === 0) {
                        listEl.innerHTML = `<div class="text-center py-8 text-slate-400 text-xs">Bạn chưa có thông báo nào.</div>`;
                        return;
                    }

                    listEl.innerHTML = navNotificationsData.map(n => {
                        const isUnread = parseInt(n.Is_read || n.is_read || 0) === 0;
                        const createdDate = new Date(n.created_at).toLocaleString('vi-VN');
                        return `
                        <div class="p-3 rounded-lg flex flex-col gap-1 transition-all text-left ${isUnread ? 'bg-blue-50/50 border-l-4 border-blue-500' : 'bg-transparent'}">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-semibold text-slate-800 text-xs ${isUnread ? 'text-blue-900 font-bold' : ''}">${escapeHtmlNav(n.Title || n.title || '')}</h4>
                                ${isUnread ? `
                                    <button onclick="markNavNotificationAsRead(event, ${n.ID || n.id})" class="px-2 py-0.5 bg-white hover:bg-slate-50 text-blue-600 border border-blue-100 rounded-md text-[10px] font-bold shadow-sm transition-colors whitespace-nowrap">
                                        Đã đọc
                                    </button>
                                ` : ''}
                            </div>
                            <p class="text-xs text-slate-500 leading-normal">${escapeHtmlNav(n.Content || n.content || '')}</p>
                            <span class="text-[9px] text-slate-400 block">${createdDate}</span>
                        </div>
                    `;
                    }).join('');
                }
            }
        } catch (err) {
            console.error("Lỗi khi tải thông báo navbar:", err);
        }
    }

    function escapeHtmlNav(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng (badge đỏ)
    function updateNavbarCartBadge() {
        const badge = document.getElementById('navbar-cart-badge');
        const targetBadge = badge || document.getElementById('nav-cart-badge');
        if (!targetBadge) return;
        try {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const count = Array.isArray(cart) ? cart.length : 0;
            if (count > 0) {
                targetBadge.innerText = count > 99 ? '99+' : count;
                targetBadge.classList.remove('hidden');
            } else {
                targetBadge.classList.add('hidden');
            }
        } catch (e) {
            targetBadge.classList.add('hidden');
        }
    }

    function updateNavCartBadge() {
        updateNavbarCartBadge();
    }

    // Hàm thực hiện hành động tìm kiếm toàn cục
    function executeGlobalSearch() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            const keyword = searchInput.value.trim();
            if (keyword) {
                // Chuyển hướng sang trang danh mục sản phẩm kèm tham số tìm kiếm (?search=...)
                window.location.href = "/Project-Web-Programming/frontend/pages/products/category.php?search=" + encodeURIComponent(keyword);
            }
        }
    }

    function toggleNavNotificationsDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById("nav-notifications-dropdown");
        if (dropdown) {
            dropdown.classList.toggle("hidden");
            if (!dropdown.classList.contains("hidden")) {
                loadNavNotifications();
            }
        }
    }

    async function markNavNotificationAsRead(event, id) {
        event.stopPropagation();
        try {
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/notifications/read", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: id
                })
            });
            if (res.ok) {
                loadNavNotifications();
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function markAllNavNotificationsAsRead(event) {
        event.stopPropagation();
        let unread = navNotificationsData.filter(n => parseInt(n.Is_read || n.is_read || 0) === 0);
        if (unread.length === 0) return;

        try {
            for (let n of unread) {
                await fetch("/Project-Web-Programming/backend/public/index.php/api/notifications/read", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: n.ID || n.id
                    })
                });
            }
            if (typeof showToast === 'function') {
                showToast("Đã đánh dấu tất cả thông báo là đã đọc!", "success");
            }
            loadNavNotifications();
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function logout() {
        if (await showConfirm("Đăng xuất", "Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?", "warning")) {
            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/logout", {
                    method: "POST"
                });
                if (res.ok) {
                    showToast("Đăng xuất thành công!", "success");
                    setTimeout(() => {
                        window.location.href = "/Project-Web-Programming/frontend/pages/home/index.php";
                    }, 1200);
                } else {
                    showAlert("Thất bại", "Đăng xuất thất bại.", "error");
                }
            } catch (error) {
                console.error("Logout error:", error);
                showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
            }
        }
    }

    // Chờ giao diện tải xong để bắt các sự kiện click / gõ phím và khởi tạo
    document.addEventListener("DOMContentLoaded", function() {
        updateNavbarCartBadge();

        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');

        // 1. Xử lý khi người dùng nhấn phím Enter trong ô tìm kiếm
        searchInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeGlobalSearch();
            }
        });

        // 2. Xử lý khi người dùng click chuột trực tiếp vào kính lúp
        searchBtn?.addEventListener('click', function() {
            executeGlobalSearch();
        });

        // Close dropdown on click outside
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("nav-notifications-dropdown");
            const trigger = document.getElementById("nav-btn-notifications");
            if (dropdown && !dropdown.classList.contains("hidden") && !dropdown.contains(event.target) && event.target !== trigger) {
                dropdown.classList.add("hidden");
            }
        });

        // Auto-run notification checker if logged in
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        if (isLoggedIn) {
            loadNavNotifications();
            setInterval(loadNavNotifications, 30000);
        }
    });
</script>