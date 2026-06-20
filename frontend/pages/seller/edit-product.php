<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Chỉnh sửa tin đăng | Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>

<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col" onload="initPage()">
    <?php include '../../components/navbar.php'; ?>

    <main class="flex-grow max-w-3xl mx-auto px-gutter py-8 w-full">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-headline-md font-bold text-on-surface">Chỉnh sửa tin đăng</h1>
                <p class="text-body-sm text-outline-variant mt-1">Cập nhật thông tin chi tiết sản phẩm của bạn.</p>
            </div>
            <button onclick="window.history.back()" class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg">arrow_back</span> Quay lại
            </button>
        </div>

        <form class="space-y-6" id="edit-product-form" onsubmit="submitProduct(event)">
            <!-- ID sản phẩm -->
            <input type="hidden" id="product-id" value="">

            <!-- Hình ảnh sản phẩm -->
            <div class="glass-card p-6 rounded-xl border border-outline-variant/40 shadow-sm bg-white">
                <label class="block text-label-md font-semibold text-on-surface mb-3">Hình ảnh sản phẩm *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="image-upload-grid">
                    <!-- Nút bấm upload -->
                    <div id="upload-trigger" onclick="triggerFileInput()" class="border-2 border-dashed border-outline-variant/60 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface transition-colors aspect-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-outline mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[12px] font-medium text-on-surface">Thêm ảnh mới</span>
                        <input type="file" id="image-file-input" onchange="handleImageUpload(event)" accept="image/*" class="hidden">
                    </div>
                </div>
                <!-- Lưu tên file ảnh -->
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
                <button type="submit" id="submitBtn" class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium text-sm shadow-sm uppercase tracking-wide">Lưu thay đổi</button>
            </div>
        </form>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        function syncValue(fieldId, val) {
            document.getElementById('input-' + fieldId).value = val;
        }

        async function initPage() {
            // 1. Tải danh mục từ API trước
            try {
                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/categories");
                let data = await res.json();
                let categories = data.data || data || [];
                let select = document.getElementById("category_id");
                select.innerHTML = '<option value="">Chọn danh mục</option>' + categories.map(cat =>
                    `<option value="${cat.ID || cat.id}">${cat.Name || cat.name}</option>`
                ).join('');
            } catch (err) {
                console.error("Lỗi khi tải danh mục:", err);
            }

            // 2. Lấy thông tin sản phẩm để đổ dữ liệu vào Form
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id');
            if (!productId) {
                showAlert("Thiếu ID", "Không tìm thấy thông tin ID sản phẩm chỉnh sửa.", "error");
                return;
            }
            document.getElementById('product-id').value = productId;

            try {
                let res = await fetch(`/Project-Web-Programming/backend/public/index.php/api/products/detail?id=${productId}`);
                let json = await res.json();
                let product = Array.isArray(json) ? (json[0] || null) : (json.data || json);

                if (!product) {
                    showAlert("Lỗi", "Không tìm thấy dữ liệu sản phẩm.", "error");
                    return;
                }

                // Điền thông tin cơ bản
                document.getElementById('title').value = product.name || product.Name || '';
                document.getElementById('category_id').value = product.category_id || product.Category_ID || '';
                document.getElementById('price').value = parseInt(product.price || product.Price || 0);

                // Hiển thị ảnh cũ
                const imgName = product.image || product.Image || '';
                if (imgName) {
                    document.getElementById('uploaded-image-name').value = imgName;

                    const grid = document.getElementById('image-upload-grid');
                    const previewDiv = document.createElement('div');
                    previewDiv.className = "relative rounded-xl overflow-hidden aspect-square border border-outline-variant/40 group";
                    previewDiv.innerHTML = `
                        <img src="/Project-Web-Programming/backend/uploads/products/${imgName}" class="w-full h-full object-cover">
                        <button type="button" onclick="removeUploadedImage(this)" class="absolute top-1.5 right-1.5 bg-black/60 text-white rounded-full p-1 hover:bg-red-600 transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    `;
                    grid.insertBefore(previewDiv, document.getElementById('upload-trigger'));
                    document.getElementById('upload-trigger').style.display = 'none';
                }

                // Parse description gộp
                const desc = product.description || product.Description || '';

                let condition = '';
                let matchCond = desc.match(/Tình trạng:\s*(.*)/);
                if (matchCond) condition = matchCond[1].trim();

                let usage = '';
                let matchUsage = desc.match(/Thời gian sử dụng:\s*(.*)/);
                if (matchUsage) usage = matchUsage[1].trim();

                let warranty = '';
                let matchWar = desc.match(/Bảo hành:\s*(.*)/);
                if (matchWar) warranty = matchWar[1].trim();

                let accessories = '';
                let matchAcc = desc.match(/Phụ kiện đi kèm:\s*(.*)/);
                if (matchAcc) accessories = matchAcc[1].trim();

                let phone = '';
                let matchPhone = desc.match(/Liên hệ SĐT:\s*(.*)/);
                if (matchPhone) phone = matchPhone[1].trim();

                let location = '';
                let matchLoc = desc.match(/Khu vực:\s*(.*)/);
                if (matchLoc) location = matchLoc[1].trim();

                let rawDescription = '';
                let splitIdx = desc.indexOf("Mô tả chi tiết:\n");
                if (splitIdx !== -1) {
                    rawDescription = desc.substring(splitIdx + "Mô tả chi tiết:\n".length);
                } else {
                    rawDescription = desc;
                }

                // Điền thông tin chi tiết
                document.getElementById('condition').value = condition;
                document.getElementById('input-usage').value = usage;
                document.getElementById('input-warranty').value = warranty;
                document.getElementById('accessories').value = accessories;
                document.getElementById('description').value = rawDescription.trim();
                document.getElementById('location').value = location;
                document.getElementById('phone').value = phone;

                // Sync radio check nếu khớp giá trị mẫu
                document.querySelectorAll('input[name="use_shortcut"]').forEach(r => {
                    if (r.value === usage) r.checked = true;
                });
                document.querySelectorAll('input[name="war_shortcut"]').forEach(r => {
                    if (r.value === warranty) r.checked = true;
                });

            } catch (err) {
                console.error("Lỗi khi tải thông tin sản phẩm:", err);
                showAlert("Lỗi", "Không thể kết nối Backend để lấy dữ liệu.", "error");
            }
        }

        function triggerFileInput() {
            document.getElementById('image-file-input').click();
        }

        async function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

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

                    const grid = document.getElementById('image-upload-grid');
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
                    grid.insertBefore(previewDiv, trigger);
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

        async function submitProduct(event) {
            event.preventDefault();

            const id = document.getElementById('product-id').value;
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

            let formattedDescription = `Tình trạng: ${condition}\n`;
            if (usage) formattedDescription += `Thời gian sử dụng: ${usage}\n`;
            if (warranty) formattedDescription += `Bảo hành: ${warranty}\n`;
            if (accessories) formattedDescription += `Phụ kiện đi kèm: ${accessories}\n`;
            formattedDescription += `Liên hệ SĐT: ${phone}\n`;
            formattedDescription += `Khu vực: ${location}\n\n`;
            formattedDescription += `Mô tả chi tiết:\n${rawDescription}`;

            const payload = {
                id: id,
                name: title,
                category_id: categoryId,
                price: price,
                description: formattedDescription,
                image: imageName
            };

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;

            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = "ĐANG LƯU...";
                submitBtn.classList.add("opacity-70");

                let res = await fetch("/Project-Web-Programming/backend/public/index.php/api/products/update", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });
                let data = await res.json();

                if (res.ok && !data.error) {
                    showToast("🎉 Cập nhật sản phẩm thành công!", "success");
                    setTimeout(() => {
                        window.location.href = "/Project-Web-Programming/frontend/pages/seller/my-store.php";
                    }, 1200);
                } else {
                    showAlert("Lưu thất bại", data.error || "Không thể cập nhật sản phẩm", "error");
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