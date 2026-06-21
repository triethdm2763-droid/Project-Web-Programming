<!DOCTYPE html>
<html lang="vi">

<head>
    <title id="page-title">Đăng tin mới | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="initPage()">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow max-w-3xl mx-auto px-gutter py-8 w-full">
        <div class="mb-6">
            <h1 id="page-heading" class="text-headline-md font-bold text-on-surface">Đăng tin mới</h1>
            <p class="text-body-sm text-outline-variant mt-1">Vui lòng cung cấp đầy đủ thông tin để sản phẩm của bạn dễ dàng tiếp cận người mua hơn.</p>
        </div>

        <form class="space-y-6" id="post-ad-form" onsubmit="submitProduct(event)">

            <!-- Hình ảnh sản phẩm -->
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white">
                <label class="block text-label-md font-semibold text-on-surface mb-3">Hình ảnh sản phẩm *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="image-upload-grid">
                    <!-- Nút bấm upload -->
                    <div id="upload-trigger" onclick="triggerFileInput()" class="border-2 border-dashed border-outline-variant/60 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface transition-colors aspect-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-outline mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[12px] font-medium text-on-surface">Thêm ảnh</span>
                        <input type="file" id="image-file-input" onchange="handleImageUpload(event)" accept="image/*" class="hidden">
                    </div>
                </div>
                <!-- Lưu tên file ảnh đã upload thành công -->
                <input type="hidden" id="uploaded-image-name" value="">
            </div>

            <!-- Thông tin chi tiết -->
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white space-y-5">
                <h2 class="text-label-md font-semibold text-on-surface border-b border-outline-variant/20 pb-2">Thông tin chi tiết</h2>

                <div>
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tiêu đề tin đăng *</label>
                    <input type="text" id="title" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" placeholder="Nhập tiêu đề..." required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Danh mục *</label>
                        <select id="category_id" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                            <option value="">Chọn danh mục</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Giá bán (VNĐ) *</label>
                        <div class="relative">
                            <input type="number" id="price" min="1" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none pr-8" placeholder="0" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-outline">đ</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Tình trạng *</label>
                    <input type="text" id="condition" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Tự nhập tình trạng sản phẩm (VD: Mới, 99%, trầy xước nhẹ...)" required>
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
                    <input type="text" id="input-usage" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Hoặc tự nhập thời gian sử dụng...">
                </div>

                <div class="space-y-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Bảo hành</label>
                    <div class="flex flex-wrap gap-4 mb-2">
                        <label class="flex items-center gap-1.5 text-body-sm cursor-pointer">
                            <input type="radio" name="war_shortcut" value="Không bảo hành" class="accent-primary" onclick="syncValue('warranty', this.value)"> Không bảo hành
                        </label>
                        <label class="flex items-center gap-1.5 text-body-sm cursor-pointer">
                            <input type="radio" name="war_shortcut" value="Còn bảo hành hãng" class="accent-primary" onclick="syncValue('warranty', this.value)"> Còn bảo hành hãng
                        </label>
                    </div>
                    <input type="text" id="input-warranty" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Hoặc tự nhập thông tin bảo hành...">
                </div>

                <div class="space-y-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Phụ kiện đi kèm</label>
                    <input type="text" id="accessories" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none text-body-md" placeholder="Tự nhập phụ kiện đi kèm (VD: Hộp, cáp, sạc, tai nghe...)">
                </div>
            </div>

            <!-- Mô tả sản phẩm -->
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white">
                <label class="block text-label-md font-semibold text-on-surface mb-2">Mô tả chi tiết *</label>
                <textarea rows="5" id="description" class="w-full px-4 py-3 border border-outline-variant/40 rounded-xl outline-none text-body-md bg-white resize-none" placeholder="Nhập mô tả chi tiết về sản phẩm..." required></textarea>
            </div>

            <!-- Khu vực & Liên hệ -->
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white space-y-4">
                <h2 class="text-label-md font-semibold text-on-surface">Khu vực & Liên hệ</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Thành phố / Tỉnh *</label>
                        <select id="location" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                            <option value="">Chọn khu vực</option>
                            <option value="Hồ Chí Minh">TP. Hồ Chí Minh</option>
                            <option value="Hà Nội">Hà Nội</option>
                            <option value="Đà Nẵng">Đà Nẵng</option>
                            <option value="Cần Thơ">Cần Thơ</option>
                            <option value="Hải Phòng">Hải Phòng</option>
                            <option value="Bình Dương">Bình Dương</option>
                            <option value="Đồng Nai">Đồng Nai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-label-sm font-medium text-on-surface-variant mb-1.5">Số điện thoại liên hệ *</label>
                        <input type="tel" id="phone" class="w-full px-4 py-2.5 bg-white border border-outline-variant/40 rounded-xl outline-none" required>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <button type="button" onclick="window.history.back()" class="px-6 py-3 border border-outline-variant text-on-surface rounded-xl hover:bg-surface font-medium text-sm">Hủy bỏ</button>
                <button type="submit" id="submitBtn" class="px-8 py-3 bg-[#F97316] text-white rounded-xl hover:opacity-90 transition-all font-medium text-sm shadow-sm uppercase tracking-wide">Đăng tin ngay</button>
            </div>
        </form>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        let editingProductId = null;

        function syncValue(fieldId, val) {
            document.getElementById('input-' + fieldId).value = val;
        }

        // Với mô tả được tạo bởi bản submitProduct() mới (không còn gộp Tình trạng/Sử dụng/
        // Bảo hành/Phụ kiện vào trong Description), ta có thể tách lại SĐT/Khu vực/Mô tả gốc
        // theo định dạng cố định bên dưới để phục vụ việc chỉnh sửa tin đăng.
        function parseDescriptionForEdit(text) {
            const result = { phone: '', location: '', rawDescription: text || '' };
            if (!text) return result;

            const phoneMatch = text.match(/Liên hệ SĐT:\s*([^\n]+)/i);
            if (phoneMatch) result.phone = phoneMatch[1].trim();

            const locationMatch = text.match(/Khu vực:\s*([^\n]+)/i);
            if (locationMatch) result.location = locationMatch[1].trim();

            const descMatch = text.match(/Mô tả chi tiết:\s*\n([\s\S]*)$/i);
            if (descMatch) result.rawDescription = descMatch[1].trim();

            return result;
        }

        async function initPage() {
            // Tải danh mục từ API
            let categories = [];
            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/categories");
                let data = await res.json();
                categories = data.data || data || [];
                let select = document.getElementById("category_id");
                select.innerHTML = '<option value="">Chọn danh mục</option>' + categories.map(cat =>
                    `<option value="${cat.ID || cat.id}">${cat.Name || cat.name}</option>`
                ).join('');
            } catch (err) {
                console.error("Lỗi khi tải danh mục:", err);
            }

            // Chế độ chỉnh sửa: nếu URL có ?id=, tải dữ liệu sản phẩm hiện có lên form
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');
            if (!id) return;

            editingProductId = id;
            document.getElementById('page-title').innerText = 'Chỉnh sửa tin đăng | Chợ Cũ';
            document.getElementById('page-heading').innerText = 'Chỉnh sửa tin đăng';
            document.getElementById('submitBtn').innerText = 'LƯU THAY ĐỔI';

            try {
                const res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${encodeURIComponent(id)}`);
                const data = await res.json();
                const product = data.data || data;
                if (!res.ok || !product || (!product.ID && !product.id)) {
                    showAlert("Không tìm thấy tin đăng", "Tin đăng này không tồn tại hoặc đã bị xóa.", "error");
                    return;
                }

                document.getElementById('title').value = product.Name || '';
                document.getElementById('category_id').value = product.Category_ID || product.CategoryID || '';
                document.getElementById('price').value = product.Price || '';
                document.getElementById('condition').value = product.Condition_status || '';
                document.getElementById('input-usage').value = product.Used_duration || '';
                document.getElementById('input-warranty').value = product.Warranty || '';
                document.getElementById('accessories').value = product.Accessories || '';

                const parsed = parseDescriptionForEdit(product.Description || '');
                document.getElementById('description').value = parsed.rawDescription;
                document.getElementById('phone').value = parsed.phone;
                if (parsed.location) {
                    document.getElementById('location').value = parsed.location;
                }

                // Hiển thị ảnh hiện có làm preview, đồng thời coi như "đã có ảnh" để không bắt buộc upload lại
                if (product.Image) {
                    document.getElementById('uploaded-image-name').value = product.Image;
                    const grid = document.getElementById('image-upload-grid');
                    const trigger = document.getElementById('upload-trigger');
                    const previewDiv = document.createElement('div');
                    previewDiv.className = "relative rounded-xl overflow-hidden aspect-square border border-outline-variant/40 group";
                    const imgSrc = (product.Image.startsWith('http://') || product.Image.startsWith('https://'))
                        ? product.Image
                        : `/Project-Web-Programming/backend/uploads/products/${product.Image}`;
                    previewDiv.innerHTML = `
                        <img src="${imgSrc}" class="w-full h-full object-cover">
                        <button type="button" onclick="removeUploadedImage(this)" class="absolute top-1.5 right-1.5 bg-black/60 text-white rounded-full p-1 hover:bg-red-600 transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    `;
                    grid.insertBefore(previewDiv, trigger);
                    trigger.style.display = 'none';
                }
            } catch (err) {
                console.error("Lỗi khi tải dữ liệu tin đăng:", err);
                showAlert("Lỗi hệ thống", "Không thể tải dữ liệu tin đăng để chỉnh sửa.", "error");
            }
        }

        function triggerFileInput() {
            document.getElementById('image-file-input').click();
        }

        // Xử lý upload ảnh trực tiếp qua API uploadImage mới
        async function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Hiển thị trạng thái đang upload
            const trigger = document.getElementById('upload-trigger');
            const originalHTML = trigger.innerHTML;
            trigger.innerHTML = `<span class="text-xs text-slate-400">Đang tải lên...</span>`;
            trigger.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('image', file);

            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/products/upload", {
                    method: "POST",
                    body: formData
                });
                let data = await res.json();

                if (res.ok && data.status === 'success') {
                    const filename = data.filename;
                    document.getElementById('uploaded-image-name').value = filename;

                    // Cập nhật Grid hiển thị ảnh preview
                    const grid = document.getElementById('image-upload-grid');
                    
                    // Tạo preview card
                    const previewDiv = document.createElement('div');
                    previewDiv.className = "relative rounded-xl overflow-hidden aspect-square border border-outline-variant/40 group";
                    previewDiv.innerHTML = `
                        <img src="/Project-Web-Programming/backend/uploads/products/${filename}" class="w-full h-full object-cover">
                        <button type="button" onclick="removeUploadedImage(this)" class="absolute top-1.5 right-1.5 bg-black/60 text-white rounded-full p-1 hover:bg-red-600 transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    `;
                    
                    // Chèn trước nút bấm upload
                    grid.insertBefore(previewDiv, trigger);
                    
                    // Ẩn nút upload đi (vì hiện tại dự án hỗ trợ 1 hình ảnh chính trong database)
                    trigger.style.display = 'none';
                } else {
                    showAlert("Lỗi upload ảnh", data.error || "Không rõ nguyên nhân", "error");
                }
            } catch (err) {
                console.error(err);
                showAlert("Lỗi kết nối", "Lỗi kết nối khi tải ảnh lên server.", "error");
            } finally {
                trigger.innerHTML = originalHTML;
                trigger.style.pointerEvents = 'auto';
            }
        }

        function removeUploadedImage(btn) {
            btn.parentElement.remove();
            document.getElementById('uploaded-image-name').value = '';
            document.getElementById('upload-trigger').style.display = 'flex';
        }

        // Đăng sản phẩm mới HOẶC lưu chỉnh sửa tin đăng hiện có (editingProductId)
        async function submitProduct(event) {
            event.preventDefault();

            const title = document.getElementById('title').value.trim();
            const categoryId = document.getElementById('category_id').value;
            const price = document.getElementById('price').value;

            if (parseFloat(price) < 1) {
                showAlert("Giá không hợp lệ", "Giá bán phải lớn hơn hoặc bằng 1 VNĐ.", "warning");
                return;
            }
            const condition = document.getElementById('condition').value.trim();
            const usage = document.getElementById('input-usage').value.trim();
            const warranty = document.getElementById('input-warranty').value.trim();
            const accessories = document.getElementById('accessories').value.trim();
            const rawDescription = document.getElementById('description').value.trim();
            const location = document.getElementById('location').value;
            const phone = document.getElementById('phone').value.trim();
            const imageName = document.getElementById('uploaded-image-name').value;

            if (!imageName) {
                showAlert("Thiếu ảnh sản phẩm", "Vui lòng tải lên 1 hình ảnh mô tả cho sản phẩm!", "warning");
                return;
            }

            // Tình trạng / Thời gian sử dụng / Bảo hành / Phụ kiện được lưu vào các CỘT RIÊNG
            // trong CSDL (Condition_status, Used_duration, Warranty, Accessories) thay vì gộp
            // chung vào Description như trước đây - giúp trang chi tiết hiển thị đúng dữ liệu.
            // Description chỉ còn giữ lại SĐT liên hệ + khu vực + mô tả gốc của người bán.
            let formattedDescription = `Liên hệ SĐT: ${phone}\n`;
            formattedDescription += `Khu vực: ${location}\n\n`;
            formattedDescription += `Mô tả chi tiết:\n${rawDescription}`;

            const payload = {
                name: title,
                category_id: categoryId,
                price: price,
                description: formattedDescription,
                image: imageName,
                condition_status: condition,
                used_duration: usage,
                warranty: warranty,
                accessories: accessories
            };

            const isEditing = !!editingProductId;
            if (isEditing) {
                payload.id = editingProductId;
            }

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;

            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = isEditing ? "ĐANG LƯU..." : "ĐANG ĐĂNG TIN...";
                submitBtn.classList.add("opacity-70");

                let res = await fetch(
                    isEditing
                        ? "/Project-Web-Programming/backend/public/index.php/api/products/update"
                        : "/Project-Web-Programming/backend/public/index.php/api/products",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(payload)
                    }
                );
                let data = await res.json();

                if (res.ok && !data.error) {
                    showToast(isEditing ? "✅ Đã lưu thay đổi tin đăng!" : "🎉 Đăng tin thanh lý thành công!", "success");
                    setTimeout(() => {
                        window.location.href = "/Project-Web-Programming/frontend/pages/seller/my-store.php";
                    }, 1200);
                } else {
                    showAlert(isEditing ? "Lưu thay đổi thất bại" : "Đăng tin thất bại", data.error || "Không thể xử lý yêu cầu", "error");
                }
            } catch (err) {
                console.error(err);
                showAlert("Lỗi hệ thống", "Lỗi kết nối khi gửi dữ liệu lên máy chủ.", "error");
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                submitBtn.classList.remove("opacity-70");
            }
        }
    </script>
</body>

</html>