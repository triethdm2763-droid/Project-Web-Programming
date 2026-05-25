<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
  .navbar-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--bg-card);
    padding: 12px 5%;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }

  .nav-logo {
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
  }
  .nav-logo i {
    color: var(--color-primary);
    margin-right: 8px;
    transform: rotate(-45deg);
  }

  .nav-search-container {
    display: flex;
    align-items: center;
    width: 40%;
    position: relative;
  }
  .nav-search-input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border-radius: 20px;
    background-color: var(--bg-main);
  }
  .nav-search-input:focus {
    border-color: var(--color-primary);
    background-color: var(--bg-card);
  }
  .nav-search-icon {
    position: absolute;
    right: 15px;
    color: var(--text-muted);
    cursor: pointer;
  }

  .nav-buttons {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .btn-post {
    background-color: var(--color-secondary);
    color: var(--text-dark);
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .btn-post:hover {
    filter: brightness(0.9);
    transform: translateY(-1px);
  }
  .user-menu {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--color-primary);
    font-weight: 600;
  }
</style>

<nav class="navbar-custom">
  <a href="../home/index.php" class="nav-logo">
    <i class="fas fa-gavel"></i>
    <span style="color: var(--color-primary);">e</span>
    <span style="color: var(--color-secondary);">B</span>
    <span style="color: var(--color-accent);">a</span>
    <span style="color: var(--color-primary);">y</span>
    <span style="font-weight: 300; margin-left: 4px; color: var(--text-muted);">Mini</span>
  </a>

  <div class="nav-search-container">
    <input type="text" class="nav-search-input" placeholder="Tìm kiếm sản phẩm đấu giá...">
    <i class="fas fa-magnifying-glass nav-search-icon"></i>
  </div>

  <div class="nav-buttons">
    <a href="../seller/post-item.php" class="btn-post">
      <i class="fas fa-box-open"></i> Đăng Bán Đồ
    </a>
    
    <div class="user-menu">
      <i class="fas fa-circle-user fa-lg"></i>
      <span>Triet_User</span>
    </div>
  </div>
</nav>