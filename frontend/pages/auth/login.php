<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng nhập | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>
    <main class="flex-grow flex items-center justify-center px-gutter py-12 w-full">
        <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm w-full max-w-md">
            <h2 class="font-headline-md text-headline-md text-primary text-center mb-6">Đăng nhập tài khoản</h2>
            <div class="space-y-5">
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ Email</label>
                    <input type="text" id="email" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập email của bạn...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mật khẩu</label>
                    <input type="password" id="password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập mật khẩu...">
                </div>
                <button onclick="login()" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm mt-2 uppercase tracking-wide">Đăng nhập</button>
            </div>
            <p class="text-center text-body-sm text-outline mt-6">Chưa có tài khoản? <a href="register.php" class="text-secondary font-medium hover:underline">Đăng ký ngay</a></p>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    <script>
    async function login() {
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;
        
        if (!email || !password) { 
            alert("Vui lòng điền đầy đủ thông tin."); 
            return; 
        }
        
        let res = await fetch("http://localhost/api/auth/login", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ email, password })
        });
        
        let data = await res.json(); 
        alert(data.message); // Lưu ý: Câu thông báo này lấy từ Backend API
    }
    </script>
</body>
</html>