```php
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Detail | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="loadProductDetail()">

    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">

        <!-- Breadcrumb -->
        <div class="text-sm text-outline mb-6">
            <a href="../../index.php" class="hover:text-primary transition-colors">
                Home
            </a>
            <span class="mx-2">/</span>
            <span class="text-on-surface">Product Detail</span>
        </div>

        <!-- Product Detail -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Product Image -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">

                    <img
                        id="product-image"
                        src="https://placehold.co/600x600"
                        alt="Product Image"
                        class="w-full aspect-square object-cover"
                    >

                </div>
            </div>

            <!-- Product Info -->
            <div class="lg:col-span-7">

                <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-6 space-y-6">

                    <!-- Product Name -->
                    <div>
                        <h1
                            id="product-name"
                            class="font-headline-lg text-headline-lg text-on-background leading-tight"
                        >
                            Loading product...
                        </h1>
                    </div>

                    <!-- Price -->
                    <div class="flex items-center gap-3">
                        <span class="text-outline line-through text-body-md" id="old-price">
                            ₫0
                        </span>

                        <span
                            id="product-price"
                            class="text-secondary text-[32px] font-bold"
                        >
                            ₫0
                        </span>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                        <span
                            id="product-status"
                            class="text-body-md text-on-surface-variant"
                        >
                            Available
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="border-t border-outline-variant/20 pt-5">
                        <h3 class="font-headline-sm text-headline-sm mb-3">
                            Product Description
                        </h3>

                        <p
                            id="product-description"
                            class="text-body-md text-on-surface-variant leading-relaxed"
                        >
                            Product description will appear here...
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">

                        <button
                            onclick="buyNow()"
                            class="flex-1 bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide"
                        >
                            BUY NOW
                        </button>

                        <button
                            onclick="addToCart()"
                            class="flex-1 border border-primary text-primary py-4 rounded-xl font-headline-sm hover:bg-primary hover:text-white transition-all uppercase tracking-wide"
                        >
                            ADD TO CART
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <?php include '../../components/footer.php'; ?>

    <script>

    let currentProduct = null;

    async function loadProductDetail() {

        try {

            const params = new URLSearchParams(window.location.search);
            const productId = params.get("id");

            if (!productId) {
                alert("Product ID not found.");
                return;
            }

            const response = await fetch(`http://localhost/api/products/${productId}`);
            const data = await response.json();

            currentProduct = data.data;

            document.getElementById("product-image").src =
                currentProduct.image || "https://placehold.co/600x600";

            document.getElementById("product-name").innerText =
                currentProduct.name || "Unnamed Product";

            document.getElementById("product-price").innerText =
                `₫${Number(currentProduct.price).toLocaleString()}`;

            document.getElementById("product-description").innerText =
                currentProduct.description || "No description.";

            document.getElementById("product-status").innerText =
                currentProduct.status || "Available";

        } catch (error) {

            console.error(error);
            alert("Failed to load product detail.");

        }

    }

    function addToCart() {

        if (!currentProduct) return;

        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        cart.push(currentProduct);

        localStorage.setItem("cart", JSON.stringify(cart));

        alert("Product added to cart.");

    }

    function buyNow() {

        if (!currentProduct) return;

        localStorage.setItem("cart", JSON.stringify([currentProduct]));

        window.location.href = "../cart/index.php";

    }

    </script>

</body>
</html>
```
