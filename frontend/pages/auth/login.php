<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Đăng nhập</h2>

<input type="text" id="email" placeholder="Email"><br>
<input type="password" id="password" placeholder="Password"><br>

<button onclick="login()">Login</button>

<script>
async function login() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    if (!email || !password) {
        alert("Nhập đầy đủ");
        return;
    }

    let res = await fetch("http://localhost/api/auth/login", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ email, password })
    });

    let data = await res.json();
    alert(data.message);
}
</script>

</body>
</html>