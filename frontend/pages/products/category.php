<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Danh mục sản phẩm | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>
    
    <main class="flex-grow px-gutter py-8 w-full max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8">
            
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm">
                    <h2 class="font-headline-sm text-primary mb-5">Danh mục</h2>
                    <ul class="space-y-3">
                        <li><a href="#" class="block text-on-surface-variant hover:text-primary transition-colors">Đồ điện tử</a></li>
                        <li><a href="#" class="block text-on-surface-variant hover:text-primary transition-colors">Thời trang</a></li>
                        <li><a href="#" class="block text-on-surface-variant hover:text-primary transition-colors">Nhà cửa</a></li>
                        <li><a href="#" class="block text-on-surface-variant hover:text-primary transition-colors">Xe cộ</a></li>
                    </ul>
                </div>
            </aside>

            <section class="flex-grow">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-headline-md font-bold text-on-surface">Tất cả sản phẩm</h1>
                    <select class="px-4 py-2 bg-white border border-outline-variant/40 rounded-lg outline-none focus:ring-2 focus:ring-primary/20">
                        <option>Mới nhất</option>
                        <option>Giá cao đến thấp</option>
                        <option>Giá thấp đến cao</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div class="glass-card p-4 rounded-xl border border-outline-variant/40 shadow-sm hover:shadow-md transition-all">
                        <div class="aspect-square bg-outline-variant/20 rounded-lg mb-4 flex items-center justify-center text-outline">Ảnh</div>
                        <h3 class="font-medium text-on-surface truncate">iPhone 13 Pro 128GB</h3>
                        <p class="text-primary font-bold mt-1">15.500.000đ</p>
                        <p class="text-xs text-outline-variant mt-2">Hà Nội • 2 giờ trước</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
</body>
</html>