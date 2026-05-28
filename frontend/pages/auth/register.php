<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Đăng ký</h2>

<input type="text" id="email" placeholder="Email"><br>
<input type="password" id="password" placeholder="Password"><br>

<button onclick="register()">Register</button>

<script>
async function register() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    let res = await fetch("http://localhost/api/auth/register", {
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