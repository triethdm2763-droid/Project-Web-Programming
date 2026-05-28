<!DOCTYPE html>
<html>
<head>
    <title>Product Detail</title>
</head>
<body>

<h2 id="name">Iphone cũ</h2>
<p id="price">5000000</p>

<button onclick="buyNow()">MUA NGAY</button>

<script src="../../assets/js/cart.js"></script>

<script>
function buyNow() {
    let product = {
        name: document.getElementById("name").innerText,
        price: document.getElementById("price").innerText
    };

    addToCart(product);
    window.location.href = "../cart/index.php";
}
</script>

</body>
</html>