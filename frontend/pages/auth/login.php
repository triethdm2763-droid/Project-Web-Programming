<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng nhập | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>
    <main class="flex-grow flex items-center justify-center px-gutter py-12 w-full">
        <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm w-full max-w-md">
            <h2 class="font-headline-md text-headline-md text-primary text-center mb-6">Đăng nhập tài khoản</h2>
            <div class="space-y-5">
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="username" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập tên đăng nhập hoặc email của bạn...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mật khẩu</label>
                    <input type="password" id="password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập mật khẩu...">
                    <div class="text-right mt-1.5">
                        <a href="forgot-password.php" class="text-xs text-primary font-medium hover:underline">Quên mật khẩu?</a>
                    </div>
                </div>
                <button onclick="login()" id="loginBtn" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm mt-2 uppercase tracking-wide">Đăng nhập</button>
            </div>
            <p class="text-center text-body-sm text-outline mt-6">Chưa có tài khoản? <a href="register.php" class="text-secondary font-medium hover:underline">Đăng ký ngay</a></p>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    <script>
    async function login() {
        let username = document.getElementById("username").value.trim();
        let password = document.getElementById("password").value;
        let btn = document.getElementById("loginBtn");
        
        if (!username || !password) { 
            showAlert("Thiếu thông tin", "Vui lòng điền đầy đủ thông tin.", "warning"); 
            return; 
        }
        
        try {
            btn.disabled = true;
            btn.innerText = "ĐANG ĐĂNG NHẬP...";
            btn.classList.add("opacity-70");

            let res = await fetch(window.appUrl ? window.appUrl("/backend/public/index.php/api/auth/login") : "../../../backend/public/index.php/api/auth/login", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ username, password })
            });
            
            let data = await res.json(); 
            console.log("LOGIN DATA =", data);
            if (res.ok) {
                showToast("Đăng nhập thành công!", "success");
                setTimeout(() => {
                    if (data.user && (data.user.Role === 'admin' || data.user.role === 'admin')) {
                        window.location.href = window.appUrl ? window.appUrl("/frontend/pages/admin/dashboard.php") : "../../../frontend/pages/admin/dashboard.php";
                    } else {
                        window.location.href = window.appUrl ? window.appUrl("/frontend/pages/home/index.php") : "../../../frontend/pages/home/index.php";
                    }
                }, 1200);
            } else {
                showAlert("Thất bại", data.error || data.message || "Tên đăng nhập hoặc mật khẩu không chính xác.", "error");
            }
        } catch (error) {
            console.error("Login Error:", error);
            showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
        } finally {
            btn.disabled = false;
            btn.innerText = "ĐĂNG NHẬP";
            btn.classList.remove("opacity-70");
        }
    }
    </script>
    
</body>
</html>
