<?php
require_once __DIR__ . '/session.php';

// Determine current path so we can highlight active nav link
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

function nav_class($pathFragment, $currentPath)
{
    if ($pathFragment === '/' && ($currentPath === '/' || $currentPath === '/Project-Web-Programming' || $currentPath === '/')) {
        return 'text-primary border-b-2 border-primary pb-1';
    }
    return (strpos($currentPath, $pathFragment) !== false) ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary transition-colors duration-200';
}
?>

<header class="bg-surface/80 backdrop-blur-md font-body-md text-body-md docked full-width top-0 sticky z-50 shadow-sm border-b border-outline-variant/30">
    <style>
        /* CSS hỗ trợ hiển thị menu danh mục khi rê chuột (hover) */
        .group:hover #nav-categories-dropdown-list {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
        #nav-categories-dropdown-list {
            transform: translateY(8px);
            transition: all 0.2s ease-in-out;
        }
    </style>
    <div class="flex justify-between items-center px-gutter py-3 max-w-container-max mx-auto w-full">

        <div class="flex items-center gap-8">
            <a href="/frontend/pages/home/index.php"
            class="flex items-center">
                <img src="/frontend/assets/images/logo.png"
                    alt="Chợ Thanh Lý"
                    class="h-[70px] w-auto object-contain relative top-[4px]">
            </a>
            <nav class="hidden md:flex gap-6 font-medium text-[16px] items-center">
                <a class="<?php echo nav_class('/frontend/pages/home/index.php', $currentPath); ?>" href="/frontend/pages/home/index.php">Trang chủ</a>
                <div class="relative group py-2">
                    <a class="flex items-center gap-0.5 cursor-pointer <?php echo nav_class('/frontend/pages/products/category.php', $currentPath); ?>" href="/frontend/pages/products/category.php">
                        <span>Danh mục</span>
                        <span class="material-symbols-outlined text-[18px] transition-transform duration-250 group-hover:rotate-180 select-none">keyboard_arrow_down</span>
                    </a>
                    <!-- Hover Dropdown Menu -->
                    <div class="absolute left-0 mt-1 w-64 bg-white/85 backdrop-blur-md border border-outline-variant/30 rounded-2xl shadow-xl overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[100] p-2 space-y-0.5" id="nav-categories-dropdown-list">
                        <div class="text-center py-4 text-xs text-slate-400">Đang tải...</div>
                    </div>
                </div>
                <a class="<?php echo nav_class('/frontend/pages/payment/track.php', $currentPath); ?>" href="/frontend/pages/payment/track.php">Tra cứu đơn hàng</a>
            </nav>
        </div>

        <div class="flex-1 max-w-xl mx-8 hidden md:block relative" id="search-wrapper">
            <div class="relative group">
                <span id="search-btn" class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors cursor-pointer hover:text-primary">search</span>
                <input id="search-input" class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-full border-none focus:ring-2 focus:ring-primary/20 transition-all text-[15px]" placeholder="Tìm kiếm sản phẩm đồ cũ..." type="text" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" autocomplete="off" />
            </div>
            <!-- Search Suggestions Dropdown (Glassmorphism) -->
            <div id="search-suggestions-dropdown" class="absolute left-0 right-0 mt-2 bg-white/90 backdrop-blur-md border border-outline-variant/30 rounded-2xl shadow-xl z-[150] hidden p-2 space-y-1 overflow-hidden">
                <div class="text-center py-4 text-xs text-slate-400">Đang tải gợi ý...</div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="/frontend/pages/cart/index.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative block">
                shopping_cart
                <span id="nav-cart-badge" class="absolute -top-0.5 -right-0.5 bg-[#fd761a] text-white text-[9px] font-bold rounded-full px-1 min-w-[16px] h-4 flex items-center justify-center ring-2 ring-white shadow-sm hidden">0</span>
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
                            <span id="nav-notification-badge" class="absolute -top-0.5 -right-0.5 bg-[#fd761a] text-white text-[9px] font-bold rounded-full px-1 min-w-[16px] h-4 flex items-center justify-center ring-2 ring-white shadow-sm hidden">0</span>
                        </button>

                        <!-- Dropdown Container -->
                        <div id="nav-notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white/85 backdrop-blur-md border border-outline-variant/40 rounded-xl shadow-lg overflow-hidden z-[100] hidden">
                            <div class="p-3 border-b border-outline-variant/20 flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-800">Thông báo</span>
                                <button onclick="markAllNavNotificationsAsRead(event)" class="text-xs text-primary font-semibold hover:underline">Đọc tất cả</button>
                            </div>
                            <div id="nav-notifications-list" class="max-h-80 overflow-y-auto divide-y divide-slate-100 p-2 space-y-1">
                                <div class="text-center py-6 text-slate-400 text-xs">Đang tải thông báo...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Nhóm tài khoản & Đăng xuất Hover Dropdown (Glassmorphism) -->
                    <div class="relative group py-2 flex items-center">
                        <a href="<?php echo ($_SESSION['role'] === 'admin') ? '/frontend/pages/admin/dashboard.php' : '/frontend/pages/user/dashboard.php'; ?>" class="flex items-center gap-1.5 p-1.5 hover:bg-surface-container rounded-full transition-colors text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">account_circle</span>
                            <span class="text-sm font-semibold max-w-[100px] truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        
                        <div class="absolute right-0 top-full mt-1 bg-white/90 backdrop-blur-md border border-outline-variant/30 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] p-1.5 min-w-[130px]">
                            <button onclick="logout()" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-on-surface-variant hover:text-error hover:bg-surface-container rounded-lg transition-colors cursor-pointer" title="Đăng xuất">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                                <span class="font-medium">Đăng xuất</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="/frontend/pages/auth/login.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors block" title="Đăng nhập">
                    account_circle
                </a>
            <?php endif; ?>

            <a href="/frontend/pages/seller/post-ad.php"
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
            let res = await fetch("/backend/public/index.php/api/notifications");
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
                const params = new URLSearchParams(window.location.search);
                const category = params.get('category');
                let redirectUrl = "/frontend/pages/products/category.php?search=" + encodeURIComponent(keyword);
                if (category) {
                    redirectUrl += "&category=" + encodeURIComponent(category);
                }
                window.location.href = redirectUrl;
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
                await fetch("/backend/public/index.php/api/notifications/read", {
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
                let res = await fetch("/backend/public/index.php/api/auth/logout", {
                    method: "POST"
                });
                if (res.ok) {
                    showToast("Đăng xuất thành công!", "success");
                    setTimeout(() => {
                        window.location.href = "/frontend/pages/home/index.php";
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

    async function loadNavbarCategories() {
        const dropdownList = document.getElementById("nav-categories-dropdown-list");
        if (!dropdownList) return;
        try {
            const res = await fetch("/backend/public/index.php/api/categories");
            if (res.ok) {
                const categories = await res.json();
                const items = Array.isArray(categories) ? categories : (categories.data || []);
                if (items.length === 0) {
                    dropdownList.innerHTML = `<div class="text-center py-2 text-xs text-slate-400">Không có danh mục nào</div>`;
                    return;
                }
                dropdownList.innerHTML = items.map(cat => `
                    <a href="/frontend/pages/products/category.php?category=${cat.ID || cat.id}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:text-primary hover:bg-slate-50 rounded-lg transition-colors font-medium text-left">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">${getNavbarCategoryIcon(cat.Name)}</span>
                        <span>${escapeHtmlNav(cat.Name)}</span>
                    </a>
                `).join('');
            }
        } catch (e) {
            console.error("Lỗi tải danh mục navbar:", e);
        }
    }

    function getNavbarCategoryIcon(catName) {
        const name = catName.toLowerCase();
        if (name.includes('điện tử') || name.includes('công nghệ')) return 'devices';
        if (name.includes('điện thoại') || name.includes('máy tính bảng')) return 'smartphone';
        if (name.includes('nam')) return 'male';
        if (name.includes('nữ')) return 'female';
        if (name.includes('sách') || name.includes('tài liệu')) return 'menu_book';
        if (name.includes('gia dụng') || name.includes('nội thất')) return 'home';
        if (name.includes('xe cộ') || name.includes('phụ tùng')) return 'directions_car';
        if (name.includes('thể thao') || name.includes('dã ngoại')) return 'sports_soccer';
        if (name.includes('mẹ') || name.includes('bé')) return 'child_care';
        if (name.includes('nhạc cụ') || name.includes('âm thanh')) return 'music_note';
        return 'category';
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

    // Chờ giao diện tải xong để bắt các sự kiện click / gõ phím và khởi tạo
    document.addEventListener("DOMContentLoaded", function() {
        updateNavbarCartBadge();
        loadNavbarCategories();

        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const suggestionsDropdown = document.getElementById('search-suggestions-dropdown');
        let activeSuggestionIndex = -1;
        let suggestionItems = [];
        let searchDebounceTimer = null;

        // 1. Xử lý khi người dùng nhấn phím Enter hoặc phím điều hướng trong ô tìm kiếm
        searchInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (!suggestionsDropdown || suggestionsDropdown.classList.contains('hidden')) {
                    executeGlobalSearch();
                } else if (activeSuggestionIndex >= 0 && activeSuggestionIndex < suggestionItems.length) {
                    e.preventDefault();
                    suggestionItems[activeSuggestionIndex].click();
                } else {
                    executeGlobalSearch();
                }
            } else if (e.key === 'ArrowDown') {
                if (suggestionsDropdown && !suggestionsDropdown.classList.contains('hidden')) {
                    e.preventDefault();
                    activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestionItems.length;
                    updateSuggestionHighlight();
                }
            } else if (e.key === 'ArrowUp') {
                if (suggestionsDropdown && !suggestionsDropdown.classList.contains('hidden')) {
                    e.preventDefault();
                    activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestionItems.length) % suggestionItems.length;
                    updateSuggestionHighlight();
                }
            } else if (e.key === 'Escape') {
                hideSearchSuggestions();
            }
        });

        function updateSuggestionHighlight() {
            const items = suggestionsDropdown.querySelectorAll('.suggestion-item');
            items.forEach((item, index) => {
                if (index === activeSuggestionIndex) {
                    item.classList.add('bg-primary/10', 'text-primary');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('bg-primary/10', 'text-primary');
                }
            });
        }

        function hideSearchSuggestions() {
            if (suggestionsDropdown) {
                suggestionsDropdown.classList.add('hidden');
                activeSuggestionIndex = -1;
                suggestionItems = [];
            }
        }

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        async function fetchSearchSuggestions(keyword) {
            if (!keyword || !suggestionsDropdown) {
                hideSearchSuggestions();
                return;
            }

            try {
                const res = await fetch(`/backend/public/index.php/api/products?search=${encodeURIComponent(keyword)}&limit=5`);
                const result = await res.json();
                const products = result.data || result || [];

                if (!products.length) {
                    suggestionsDropdown.innerHTML = `<div class="text-center py-4 text-xs text-slate-400">Không tìm thấy sản phẩm gợi ý nào.</div>`;
                    suggestionsDropdown.classList.remove('hidden');
                    return;
                }

                suggestionsDropdown.innerHTML = '';
                suggestionItems = [];

                products.forEach(p => {
                    const img = p.Image ? (p.Image.startsWith('http') ? p.Image : `/backend/uploads/products/${p.Image}`) : '/frontend/assets/images/placeholder.png';
                    const cleanName = escapeHtml(p.Name || p.name || '');
                    
                    const regex = new RegExp(`(${escapeRegExp(keyword)})`, 'gi');
                    const highlightedName = cleanName.replace(regex, `<span class="text-primary font-bold">$1</span>`);
                    
                    const priceFormatted = new Intl.NumberFormat('vi-VN', {style:'currency', currency:'VND'}).format(p.Price || p.price || 0);

                    const itemHtml = `
                        <a href="/frontend/pages/products/detail.php?id=${p.ID || p.id}" class="suggestion-item flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer text-left">
                            <img src="${img}" class="w-10 h-10 object-contain rounded-lg bg-slate-50 border border-slate-100 flex-shrink-0" onerror="this.src='/frontend/assets/images/placeholder.png'">
                            <div class="flex-grow min-w-0">
                                <h4 class="text-sm text-slate-800 font-medium truncate">${highlightedName}</h4>
                                <p class="text-xs text-slate-500 font-semibold mt-0.5">${priceFormatted}</p>
                            </div>
                        </a>
                    `;
                    suggestionsDropdown.insertAdjacentHTML('beforeend', itemHtml);
                });

                const params = new URLSearchParams(window.location.search);
                const category = params.get('category');
                const categoryParam = category ? `&category=${encodeURIComponent(category)}` : '';

                const seeAllHtml = `
                    <a href="/frontend/pages/products/category.php?search=${encodeURIComponent(keyword)}${categoryParam}" class="suggestion-item block text-center py-2.5 text-xs text-primary font-semibold border-t border-slate-100/50 hover:bg-slate-50 transition-colors mt-1">
                        Xem tất cả kết quả cho "${escapeHtml(keyword)}"
                    </a>
                `;
                suggestionsDropdown.insertAdjacentHTML('beforeend', seeAllHtml);

                suggestionItems = suggestionsDropdown.querySelectorAll('.suggestion-item');
                suggestionsDropdown.classList.remove('hidden');
                activeSuggestionIndex = -1;

            } catch (err) {
                console.error(err);
            }
        }

        searchInput?.addEventListener('input', function() {
            const keyword = this.value.trim();
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => fetchSearchSuggestions(keyword), 300);
        });

        searchInput?.addEventListener('focus', function() {
            const keyword = this.value.trim();
            if (keyword) {
                fetchSearchSuggestions(keyword);
            }
        });

        // 2. Xử lý khi người dùng click chuột trực tiếp vào kính lúp
        searchBtn?.addEventListener('click', function() {
            executeGlobalSearch();
        });

        // Close dropdowns on click outside
        document.addEventListener("click", function(event) {
            // Notifications dropdown
            const notificationsDropdown = document.getElementById("nav-notifications-dropdown");
            const notificationsTrigger = document.getElementById("nav-btn-notifications");
            if (notificationsDropdown && !notificationsDropdown.classList.contains("hidden") && !notificationsDropdown.contains(event.target) && event.target !== notificationsTrigger) {
                notificationsDropdown.classList.add("hidden");
            }

            // Suggestions dropdown
            if (suggestionsDropdown && !suggestionsDropdown.contains(event.target) && event.target !== searchInput) {
                hideSearchSuggestions();
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
