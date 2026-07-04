<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Khôi phục mật khẩu | Chợ Thanh Lý</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow flex items-center justify-center px-gutter py-12 w-full">
        <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm w-full max-w-md bg-white">
            
            <!-- Bước 1: Yêu cầu mã OTP -->
            <div id="step-request">
                <h2 class="font-headline-md text-headline-md text-primary text-center mb-2">Quên mật khẩu?</h2>
                <p class="text-center text-body-sm text-outline mb-6">Nhập email đăng ký tài khoản của bạn để nhận mã OTP khôi phục mật khẩu.</p>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Địa chỉ Email</label>
                        <input type="email" id="email" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="example@domain.com">
                    </div>
                    <button onclick="requestReset()" id="requestBtn" class="w-full bg-[#F97316] text-white py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm uppercase tracking-wide">Gửi yêu cầu</button>
                </div>
            </div>

            <!-- Bước 2: Nhập OTP và Đặt mật khẩu mới -->
            <div id="step-reset" class="hidden">
                <h2 class="font-headline-md text-headline-md text-primary text-center mb-2">Khôi phục mật khẩu</h2>
                <p class="text-center text-body-sm text-outline mb-4">Vui lòng kiểm tra mã OTP (mô phỏng bên dưới) và nhập mật khẩu mới.</p>
                
                <!-- Hộp thông báo hiển thị OTP mô phỏng cho người dùng dễ test -->
                <div id="otp-simulation-alert" class="bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-xl p-3.5 mb-5 space-y-1">
                    <p class="font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span> HỆ THỐNG MÔ PHỎNG (DEVELOPMENT ONLY):
                    </p>
                    <p>Mã OTP của bạn là: <strong id="simulated-otp" class="text-blue-900 text-sm tracking-widest font-mono bg-blue-100 px-2 py-0.5 rounded">000000</strong></p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mã OTP (6 chữ số)</label>
                        <input type="text" id="otp" maxlength="6" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-center tracking-widest font-mono text-lg" placeholder="******">
                    </div>
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Mật khẩu mới (Tối thiểu 6 ký tự)</label>
                        <input type="password" id="new_password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Nhập mật khẩu mới...">
                    </div>
                    <button onclick="performReset()" id="resetBtn" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm uppercase tracking-wide">Đặt lại mật khẩu</button>
                </div>
            </div>

            <p class="text-center text-body-sm text-outline mt-6">Quay lại trang <a href="login.php" class="text-secondary font-medium hover:underline">Đăng nhập</a></p>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
    let userEmail = "";

    async function requestReset() {
        const email = document.getElementById("email").value.trim();
        const btn = document.getElementById("requestBtn");
        
        if (!email) {
            showAlert("Thiếu thông tin", "Vui lòng nhập địa chỉ email.", "warning");
            return;
        }

        try {
            btn.disabled = true;
            btn.innerHTML = "ĐANG XỬ LÝ...";
            btn.classList.add("opacity-70");

            let res = await fetch("/backend/public/api/auth/forgot-password", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email })
            });

            let data = await res.json();

            if (res.ok && !data.error) {
                userEmail = email;
                
                // Hiển thị OTP mô phỏng
                if (data.otp) {
                    document.getElementById("simulated-otp").innerText = data.otp;
                }

                // Chuyển bước UI mượt mà
                document.getElementById("step-request").classList.add("hidden");
                document.getElementById("step-reset").classList.remove("hidden");
            } else {
                showAlert("Thất bại", data.error || "Không thể yêu cầu đặt lại mật khẩu.", "error");
            }
        } catch (error) {
            console.error(error);
            showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = "GỬI YÊU CẦU";
            btn.classList.remove("opacity-70");
        }
    }

    async function performReset() {
        const otp = document.getElementById("otp").value.trim();
        const password = document.getElementById("new_password").value;
        const btn = document.getElementById("resetBtn");

        if (!otp || !password) {
            showAlert("Thiếu thông tin", "Vui lòng điền đầy đủ mã OTP và mật khẩu mới.", "warning");
            return;
        }

        if (password.length < 6) {
            showAlert("Mật khẩu yếu", "Mật khẩu mới phải dài tối thiểu 6 ký tự.", "warning");
            return;
        }

        try {
            btn.disabled = true;
            btn.innerHTML = "ĐANG ĐỔI MẬT KHẨU...";
            btn.classList.add("opacity-70");

            let res = await fetch("/backend/public/api/auth/reset-password", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ otp, password })
            });

            let data = await res.json();

            if (res.ok && !data.error) {
                showToast("🎉 Đặt lại mật khẩu thành công!", "success");
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 1200);
            } else {
                showAlert("Thất bại", data.error || "Đặt lại mật khẩu thất bại.", "error");
            }
        } catch (error) {
            console.error(error);
            showAlert("Lỗi hệ thống", "Lỗi kết nối đến máy chủ.", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = "ĐẶT LẠI MẬT KHẨU";
            btn.classList.remove("opacity-70");
        }
    }
    </script>
</body>

</html>
