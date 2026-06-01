<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Cart | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="renderCart()">
    <?php include '../../components/navbar.php'; ?>
    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full">
        <h1 class="font-headline-lg text-headline-lg text-on-background mb-8 border-l-4 border-primary pl-3">Shopping Cart</h1>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 bg-white p-6 rounded-xl border border-outline-variant/20 shadow-sm min-h-[200px]">
                <div id="cart-list" class="space-y-4"></div>
            </div>
            <div class="lg:col-span-4 bg-white p-6 rounded-xl border border-outline-variant/20 shadow-sm space-y-5">
                <h3 class="font-headline-sm text-headline-sm text-on-background">Delivery Details</h3>
                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Shipping Address</label>
                    <input type="text" id="address" class="w-full px-4 py-2.5 bg-surface border border-outline-variant/40 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-[15px]" placeholder="Street name, district, city...">
                </div>
                <div class="border-t border-outline-variant/20 pt-4">
                    <button onclick="checkout()" class="w-full bg-[#F97316] text-white py-4 rounded-xl font-headline-sm shadow-lg shadow-secondary/20 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-wide">Place Order</button>
                </div>
            </div>
        </div>
    </main>
    <?php include '../../components/footer.php'; ?>
    <script src="../../assets/js/cart.js"></script>
</body>
</html>