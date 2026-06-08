// Hàm format tiền tệ VNĐ
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

// Khởi tạo và Render Giỏ hàng
function renderCart() {
    // Đọc dữ liệu từ localStorage 
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    
    const cartList = document.getElementById("cart-list");
    const itemCountEl = document.getElementById("cart-item-count");
    const summaryCount = document.getElementById("summary-count");
    const summarySubtotal = document.getElementById("summary-subtotal");
    const summaryTotal = document.getElementById("summary-total");

    // Nếu không ở trang giỏ hàng (không có DOM) thì bỏ qua
    if (!cartList) return; 

    itemCountEl.innerText = `Bạn đang có ${cart.length} sản phẩm trong giỏ hàng`;
    summaryCount.innerText = `Tạm tính (${cart.length} sản phẩm)`;

    // Xử lý giao diện giỏ hàng trống
    if (cart.length === 0) {
        cartList.innerHTML = `
            <div class="text-center py-10">
                <p class="text-on-surface-variant mb-4">Giỏ hàng của bạn đang trống.</p>
                <a href="../../index.php" class="inline-block bg-primary text-white px-6 py-2 rounded-lg font-medium hover:opacity-90">Đi mua sắm ngay</a>
            </div>`;
        summarySubtotal.innerText = '0đ';
        summaryTotal.innerText = '0đ';
        return;
    }

    let total = 0;
    let html = '';

    // Render danh sách sản phẩm
    cart.forEach((item, index) => {
        const price = Number(item.Price || item.price) || 0;
        const name = item.Name || item.name || 'Sản phẩm chưa rõ tên';
        const image = item.Image || item.image || '';
        const imgUrl = image ? 
            (image.startsWith('http://') || image.startsWith('https://') ? image : `/Project-Web-Programming/backend/uploads/products/${image}`) 
            : 'https://placehold.co/100x100';

        total += price;
        html += `
            <div class="flex items-center gap-4 border-b border-outline-variant/20 pb-4 last:border-0 last:pb-0 pt-4 first:pt-0">
                <img src="${imgUrl}" alt="${name}" class="w-20 h-20 object-cover rounded-lg border border-outline-variant/20">
                <div class="flex-grow">
                    <h4 class="font-medium text-on-background line-clamp-2">${name}</h4>
                    <p class="text-secondary font-bold mt-1">${formatCurrency(price)}</p>
                </div>
                <button onclick="removeFromCart(${index})" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Xóa khỏi giỏ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        `;
    });

    cartList.innerHTML = html;
    summarySubtotal.innerText = formatCurrency(total);
    summaryTotal.innerText = formatCurrency(total);
}

// Hàm xóa 1 sản phẩm khỏi giỏ
function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    renderCart(); // Cập nhật lại UI lập tức
}

// Hàm xóa toàn bộ giỏ hàng
function clearCart() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) return;
    
    if (confirm("Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?")) {
        localStorage.removeItem("cart");
        renderCart();
    }
}

// Xử lý nút [XÁC NHẬN ĐẶT HÀNG] đẩy JSON sang API 
async function checkout() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) {
        alert("Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng!");
        return;
    }

    // Lấy thông tin Form
    const fullname = document.getElementById("fullname").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const address = document.getElementById("address").value.trim();
    const notes = document.getElementById("notes").value.trim();
    const paymentMethodEl = document.getElementById("payment_method");
    const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'COD';

    if (!fullname || !phone || !address) {
        alert("Vui lòng điền đầy đủ các thông tin: Họ tên, Số điện thoại và Địa chỉ giao hàng!");
        return;
    }

    if (address.length < 10) {
        alert("Địa chỉ giao hàng phải có ít nhất 10 ký tự để giao hàng!");
        return;
    }

    const btn = document.getElementById("checkoutBtn");
    const originalText = btn.innerHTML;

    try {
        // UX: Disable nút bấm trong lúc đợi API
        btn.disabled = true;
        btn.innerHTML = 'ĐANG XỬ LÝ...';
        btn.classList.add("opacity-70");

        let successCount = 0;
        let errors = [];
        let successfulIds = [];

        // Vì DB chỉ hỗ trợ mỗi đơn hàng một sản phẩm (C2C), chúng ta sẽ gửi yêu cầu đặt hàng lần lượt cho từng sản phẩm
        for (const item of cart) {
            const itemId = item.ID || item.id;
            const itemName = item.Name || item.name || 'Sản phẩm';
            
            const payload = {
                product_id: itemId,
                shipping_address: address,
                payment_method: paymentMethod,
                fullname: fullname,
                phone: phone,
                notes: notes
            };

            try {
                let res = await fetch("/Project-Web-Programming/backend/public/api/orders", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                let data = await res.json(); 

                if (res.ok && !data.error) {
                    successCount++;
                    successfulIds.push(itemId);
                } else {
                    errors.push(`Sản phẩm "${itemName}": ${data.error || 'Có lỗi xảy ra'}`);
                }
            } catch (err) {
                errors.push(`Sản phẩm "${itemName}": Lỗi kết nối mạng.`);
            }
        }

        if (successCount > 0) {
            // Lọc bỏ những sản phẩm đã đặt mua thành công khỏi giỏ hàng
            let cartAfterCheckout = cart.filter(item => !successfulIds.includes(item.ID || item.id));
            localStorage.setItem("cart", JSON.stringify(cartAfterCheckout));
            
            if (successCount === cart.length) {
                alert("🎉 Đặt hàng thành công tất cả sản phẩm!");
                window.location.href = "../../index.php";
            } else {
                alert(`Đặt hàng thành công ${successCount}/${cart.length} sản phẩm.\n\nMột số sản phẩm gặp lỗi:\n` + errors.join('\n'));
                renderCart();
            }
        } else {
            alert("Đặt hàng thất bại!\n\nChi tiết lỗi:\n" + errors.join('\n'));
        }

    } catch (error) {
        console.error("Checkout Error:", error);
        alert("Lỗi kết nối mạng hoặc máy chủ không phản hồi.");
    } finally {
        // Trả lại trạng thái UI cho nút bấm
        btn.disabled = false;
        btn.innerHTML = originalText;
        btn.classList.remove("opacity-70");
    }
}
