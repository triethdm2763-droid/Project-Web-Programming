<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>
    <main class="flex-grow flex items-center justify-center px-gutter py-12 w-full">
        <div class="glass-card p-8 rounded-xl border border-outline-variant/40 shadow-sm w-full max-w-md">
            <h2 class="font-headline-md text-headline-md text-primary text-center mb-6">Create New Account</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Username</label>
                    <input type="text" id="username" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Choose a unique username...">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Email Address</label>
                    <input type="text" id="email" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="name@example.com">
                </div>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Password</label>
                    <input type="password" id="password" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" placeholder="Create a secure password...">
                </div>
                <button onclick="register()" class="w-full bg-primary text-on-primary py-3.5 rounded-full font-label-md hover:opacity-90 active:scale-95 transition-all shadow-sm mt-3 uppercase tracking-wide">Sign Up</button>
            </div>
            <p class="text-center text-body-sm text-outline mt-6">Already have an account? <a href="login.php" class="text-secondary font-medium hover:underline">Log in</a></p>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    <script>
    async function register() {
        let username = document.getElementById("username").value;
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;
        if (!username || !email || !password) { alert("Please fill in all fields."); return; }
        let res = await fetch("http://localhost/api/auth/register", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ username, email, password })
        });
        let data = await res.json(); alert(data.message);
    }
    </script>
</body>
</html>