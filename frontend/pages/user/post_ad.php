<?php
// Mày tự truyền data từ backend vào các biến này nha:
// $categories = [...];
// $locations = [...];
// $warranties = [...]; // Nếu có
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng tin mới | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow max-w-3xl mx-auto px-gutter py-8 w-full">
        <div class="mb-6">
            <h1 class="text-headline-md font-bold text-on-surface">Đăng tin mới</h1>
            <p class="text-body-sm text-outline-variant mt-1">Vui lòng cung cấp đầy đủ thông tin để sản phẩm của bạn dễ dàng tiếp cận người mua hơn.</p>
        </div>

        <form class="space-y-6" id="post-ad-form" method="POST" action="" enctype="multipart/form-data">
            
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white">
                <label class="block text-label-md font-semibold text-on-surface mb-3">Hình ảnh sản phẩm</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="border-2 border-dashed border-outline-variant/60 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface transition-colors aspect-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-outline mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-body-sm font-medium text-on-surface">Thêm hình ảnh (Tối đa 6)</span>
                        <input type="file" name="product_images[]" multiple class="hidden">
                    </div>
                </div>
            </div>

            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white space-y-5">
                <h2 class="text-label-md font-semibold text-on-surface border-b border-outline-variant/20 pb-2">Thông chi tiết</h2>

                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tiêu đề tin đăng *</label>
                    <input type="text" name="title" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" placeholder="Nhập tiêu đề..." required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Danh mục *</label>
                        <select name="category_id" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                            <option value="">Chọn danh mục</option>
                            <?php if(!empty($categories)): foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Giá bán (VNĐ) *</label>
                        <div class="relative">
                            <input type="number" name="price" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none pr-8" placeholder="0" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-outline">đ</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tình trạng *</label>
                    <input type="text" name="condition" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Tự nhập tình trạng sản phẩm (VD: Mới, 99%, trầy xước nhẹ...)" required>
                </div>

                <div class="space-y-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Thời gian sử dụng</label>
                    <div class="flex flex-wrap gap-4 mb-2">
                        <label class="flex items-center gap-1.5 text-body-sm cursor-pointer">
                            <input type="radio" name="use_shortcut" value="Dưới 1 năm" class="accent-primary" onclick="syncValue('usage', this.value)"> Dưới 1 năm
                        </label>
                        <label class="flex items-center gap-1.5 text-body-sm cursor-pointer">
                            <input type="radio" name="use_shortcut" value="Trên 1 năm" class="accent-primary" onclick="syncValue('usage', this.value)"> Trên 1 năm
                        </label>
                    </div>
                    <input type="text" id="input-usage" name="usage" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Hoặc tự nhập thời gian sử dụng...">
                </div>

                <div class="space-y-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Bảo hành</label>
                    <div class="flex flex-wrap gap-4 mb-2">
                        <?php if(!empty($warranties)): foreach($warranties as $war): ?>
                        <label class="flex items-center gap-1.5 text-body-sm cursor-pointer">
                            <input type="radio" name="war_shortcut" value="<?= htmlspecialchars($war['name']) ?>" class="accent-primary" onclick="syncValue('warranty', this.value)"> <?= htmlspecialchars($war['name']) ?>
                        </label>
                        <?php endforeach; endif; ?>
                    </div>
                    <input type="text" id="input-warranty" name="warranty" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Hoặc tự nhập thông tin bảo hành...">
                </div>

                <div class="space-y-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Phụ kiện đi kèm</label>
                    <input type="text" name="accessories" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Tự nhập phụ kiện đi kèm (VD: Hộp, cáp, sạc, tai nghe...)">
                </div>
            </div>

            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white">
                <label class="block text-label-md font-semibold text-on-surface mb-2">Mô tả sản phẩm</label>
                <textarea rows="5" name="description" class="w-full px-4 py-3 border border-outline-variant/40 rounded-xl outline-none text-body-md bg-white resize-none" placeholder="Nhập mô tả chi tiết..." required></textarea>
            </div>

            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white space-y-4">
                <h2 class="text-label-md font-semibold text-on-surface">Khu vực & Liên hệ</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Thành phố / Tỉnh *</label>
                        <select name="location" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                            <option value="">Chọn khu vực</option>
                            <?php if(!empty($locations)): foreach($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['id']) ?>"><?= htmlspecialchars($loc['name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại liên hệ *</label>
                        <input type="tel" name="phone" value="" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <button type="button" class="px-6 py-3 border border-outline-variant text-on-surface rounded-xl hover:bg-surface font-medium text-sm">Lưu bản nháp</button>
                <button type="submit" class="px-8 py-3 bg-primary text-on-primary rounded-xl hover:opacity-90 transition-all font-medium text-sm shadow-sm">Đăng tin ngay</button>
            </div>
        </form>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
    // Chỉ giữ lại hàm đồng bộ cho các phần có radio button (Thời gian sử dụng, Bảo hành)
    function syncValue(fieldId, val) {
        document.getElementById('input-' + fieldId).value = val;
    }
    </script>
</body>
</html>