<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine current path so we can highlight active nav link
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

function nav_class($pathFragment, $currentPath) {
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
        <input id="search-input" class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-full border-none focus:ring-2 focus:ring-primary/20 transition-all text-[15px]" placeholder="Tìm kiếm sản phẩm đồ cũ..." type="text"/>
      </div>
    </div>

    <div class="flex items-center gap-4">
  <a href="/Project-Web-Programming/frontend/pages/cart/index.php" id="navbar-cart-link" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative block">
    shopping_cart
    <span id="navbar-cart-badge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-[18px] text-center font-body-md">0</span>
  </a>
  
  <?php
    $isLoggedIn = isset($_SESSION['user_id']);
  ?>

  <?php if ($isLoggedIn): ?>
    <div class="flex items-center gap-2">
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
  
  <?php if ($isLoggedIn): ?>
        <a href="/Project-Web-Programming/frontend/pages/seller/my-store.php" 
           class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 active:scale-95 transition-all shadow-sm text-[15px] cursor-pointer inline-block text-center">
            Đăng tin
        </a>
      <?php else: ?>
        <button id="btn-create-post" data-logged-in="false" type="button"
        
                class="bg-primary text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 active:scale-95 transition-all shadow-sm text-[15px] cursor-pointer">
            Đăng tin
        </button>
        
      <?php endif; ?>
</div>
    
  </div>
</header>

<script>
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

// Cập nhật số lượng sản phẩm hiển thị trên icon giỏ hàng (badge đỏ)
function updateNavbarCartBadge() {
    const badge = document.getElementById('navbar-cart-badge');
    if (!badge) return;
    try {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const count = Array.isArray(cart) ? cart.length : 0;
        if (count > 0) {
            badge.innerText = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    } catch (e) {
        badge.classList.add('hidden');
    }
}

// Chờ giao diện tải xong để bắt các sự kiện click / gõ phím
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
});

// Lưu ý: hàm logout() dùng chung cho toàn bộ trang web đã được định nghĩa
// sẵn trong frontend/assets/js/ui-helpers.js (được header.php include ở mọi trang),
// nên không định nghĩa lại ở đây để tránh xung đột.
</script>
