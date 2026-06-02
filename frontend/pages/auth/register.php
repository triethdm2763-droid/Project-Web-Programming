<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng ký | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>
    <main class="flex-grow flex items-center justify-center px-gutter py-12 w-full">
        <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm w-full max-w-md">
            <h2 class="font-headline-md text-headline-md text-primary text-center mb-6">Tạo tài khoản mới</h2>
            <div class="space-y-5">
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Họ và tên</label>
                    <input type="text" id="fullname" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập họ tên...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ Email</label>
                    <input type="email" id="email" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập email của bạn...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mật khẩu</label>
                    <input type="password" id="password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)...">
                </div>
                <button onclick="register()" id="registerBtn" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm mt-2 uppercase tracking-wide">Đăng ký</button>
            </div>
            <p class="text-center text-body-sm text-outline mt-6">Đã có tài khoản? <a href="login.php" class="text-secondary font-medium hover:underline">Đăng nhập ngay</a></p>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    
    <script>
    async function register() {
        let fullname = document.getElementById("fullname").value.trim();
        let email = document.getElementById("email").value.trim();
        let password = document.getElementById("password").value.trim();
        let btn = document.getElementById("registerBtn");
        
        if (!fullname || !email || !password) { 
            alert("Vui lòng điền đầy đủ tất cả các trường."); 
            return; 
        }

        if (password.length < 6) {
            alert("Mật khẩu phải chứa ít nhất 6 ký tự.");
            return;
        }

        try {
            // UX: Xử lý trạng thái Loading
            btn.disabled = true;
            btn.innerText = "ĐANG ĐĂNG KÝ...";
            btn.classList.add("opacity-70");

            // Gọi API Đăng ký của BE1 (Tuần 1)
            let res = await fetch("http://localhost/api/auth/register", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ fullname, email, password })
            });

            let data = await res.json(); 
            
            if(res.ok && data.status !== false) {
                alert("Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.");
                window.location.href = "login.php"; // Điều hướng sang trang Login
            } else {
                alert(data.message || "Email đã tồn tại hoặc xảy ra lỗi.");
            }

        } catch (error) {
            console.error("Register Error:", error);
            alert("Lỗi kết nối đến máy chủ.");
        } finally {
            btn.disabled = false;
            btn.innerText = "ĐĂNG KÝ";
            btn.classList.remove("opacity-70");
        }
    }
    </script>
</body>
</html>