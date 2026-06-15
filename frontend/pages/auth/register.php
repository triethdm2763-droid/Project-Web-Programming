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
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tên đăng nhập</label>
                    <input type="text" id="username" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập tên đăng nhập...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ Email</label>
                    <input type="email" id="email" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập email của bạn...">
                </div>
                <!-- Bắt đầu phần bổ sung số điện thoại -->
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại</label>
                    <input type="tel" id="phone" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập số điện thoại của bạn...">
                </div>
                <!-- Kết thúc phần bổ sung số điện thoại -->
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mật khẩu</label>
                    <input type="password" id="password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)...">
                </div>
                <!-- CAPTCHA -->
                <div class="cf-turnstile mt-4"
                    data-sitekey="0x4AAAAAADlFZ5CRWzrHy-kN">
                </div>
                <button onclick="register()" id="registerBtn" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm mt-2 uppercase tracking-wide">Đăng ký</button>
            </div>
            <p class="text-center text-body-sm text-outline mt-6">Đã có tài khoản? <a href="login.php" class="text-secondary font-medium hover:underline">Đăng nhập ngay</a></p>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    
    <script>
    async function register() {
        let username = document.getElementById("username").value.trim();
        let email = document.getElementById("email").value.trim();
        let phone = document.getElementById("phone").value.trim();
        let password = document.getElementById("password").value.trim();
        let turnstileToken = document.querySelector('[name="cf-turnstile-response"]')?.value;
        let btn = document.getElementById("registerBtn");
        
        if (!username || !email || !phone || !password) { 
            showAlert("Thiếu thông tin", "Vui lòng điền đầy đủ tất cả các trường.", "warning"); 
            return; 
        }

        if (!turnstileToken) {
            alert("Vui lòng xác thực CAPTCHA.");
            return;
        }
        
        if (password.length < 6) {
            showAlert("Mật khẩu yếu", "Mật khẩu phải chứa ít nhất 6 ký tự.", "warning");
            return;
        }

        try {
            // UX: Xử lý trạng thái Loading
            btn.disabled = true;
            btn.innerText = "ĐANG ĐĂNG KÝ...";
            btn.classList.add("opacity-70");

            // Gọi API Đăng ký của BE
            let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/auth/register", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ username, email, phone, password,turnstileToken })
            });

            let data = await res.json(); 
            
            if (res.ok) {
                showToast("🎉 Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.", "success");
                setTimeout(() => {
                    window.location.href = "login.php"; // Điều hướng sang trang Login
                }, 1200);
            } else {
                // Hiển thị chi tiết lỗi validate nếu có
                if (data.errors && typeof data.errors === 'object') {
                    let errMsg = [];
                    for (const field in data.errors) {
                        errMsg.push(...data.errors[field]);
                    }
                    showAlert("Đăng ký thất bại", errMsg.join(", ") || "Vui lòng kiểm tra lại thông tin.", "error");
                } else {
                    showAlert("Đăng ký thất bại", data.message || "Tên đăng nhập hoặc Email có thể đã tồn tại.", "error");
                }
            }

        } catch (error) {
            console.error("Register Error:", error);
            showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
        } finally {
            btn.disabled = false;
            btn.innerText = "ĐĂNG KÝ";
            btn.classList.remove("opacity-70");
        }
    }

    </script>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"
        async
        defer>
    </script>
    </script>
</body>
</html>
