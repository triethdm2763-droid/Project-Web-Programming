
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Chi tiết sản phẩm</title>

    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md">

<?php include '../../components/navbar.php'; ?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="grid md:grid-cols-2 gap-10 bg-white rounded-2xl shadow-xl p-6">

        <!-- Image -->
        <div>
            <img
                id="image"
                src="https://via.placeholder.com/500"
                class="w-full rounded-2xl"
            >
        </div>

        <!-- Info -->
        <div>

            <h1
                id="name"
                class="text-4xl font-bold text-on-surface mb-4"
            >
                Iphone cũ
            </h1>

            <p class="text-gray-500 mb-4">
                Máy còn mới 95%, pin tốt, đầy đủ phụ kiện.
            </p>

            <p
                id="price"
                class="text-3xl font-bold text-primary mb-8"
            >
                5.000.000đ
            </p>

            <button
                onclick="buyNow()"
                class="bg-primary text-white px-8 py-4 rounded-xl font-bold hover:opacity-90 hover:scale-105 transition-all duration-300"
            >
                MUA NGAY
            </button>

        </div>

    </div>

</div>

<script src="../../assets/js/cart.js"></script>

<script>

function buyNow() {

    let product = {
        id: 1,
        name: document.getElementById("name").innerText,
        price: document.getElementById("price").innerText,
        image: document.getElementById("image").src,
        quantity: 1
    };

    addToCart(product);

    window.location.href = "../cart/index.php";
}

</script>

</body>
</html>