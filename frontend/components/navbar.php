<header class="bg-surface/80 backdrop-blur-md font-body-md text-body-md docked full-width top-0 sticky z-50 shadow-sm border-b border-outline-variant/30">
  <div class="flex justify-between items-center px-gutter py-3 max-w-container-max mx-auto w-full">
    
    <div class="flex items-center gap-8">
      <a class="font-headline-md text-headline-md font-bold text-primary tracking-tight" href="/Project-Web-Programming/frontend/pages/home/index.php">Chợ Cũ</a>
      <nav class="hidden md:flex gap-6 font-medium text-[16px]">
        <a class="text-primary border-b-2 border-primary pb-1" href="/Project-Web-Programming/frontend/pages/home/index.php">Trang chủ</a>
        <a class="text-on-surface-variant hover:text-primary transition-colors duration-200" href="#">Danh mục</a>
        <a class="text-on-surface-variant hover:text-primary transition-colors duration-200" href="#">Khuyến mãi</a>
      </nav>
    </div>

    <div class="flex-1 max-w-xl mx-8 hidden md:block">
      <div class="relative group">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">search</span>
        <input class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-full border-none focus:ring-2 focus:ring-primary/20 transition-all text-[15px]" placeholder="Tìm kiếm sản phẩm đồ cũ..." type="text"/>
      </div>
    </div>

    <div class="flex items-center gap-4">
  <a href="/Project-Web-Programming/frontend/pages/cart/index.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative block">
    shopping_cart
  </a>
  
  <a href="/Project-Web-Programming/frontend/pages/auth/login.php" class="material-symbols-outlined p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors block">
    account_circle
  </a>
  
  <?php
    $isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
  ?>

  <button id="btn-create-post" data-logged-in="<?php echo $isLoggedIn; ?>" 
          class="bg-primary text-on-primary px-6 py-2 rounded-full font-semibold hover:opacity-90 active:scale-95 transition-all shadow-sm text-[15px] cursor-pointer">
    Đăng tin
  </button>
</div>
    
  </div>
</header>