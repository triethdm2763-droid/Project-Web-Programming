Feature('Order Cancel');

Scenario('Buyer cancels a pending order', async ({ I }) => {

    // Mở trang đăng nhập
    I.amOnPage('/frontend/pages/auth/login.php');

    // Đợi ô nhập xuất hiện
    I.waitForElement('#username', 5);

    // Đăng nhập
    I.fillField('#username', 'huynhduongminhtriet@gmail.com');
    I.fillField('#password', '12345678');

    I.click('#loginBtn');

    // Đợi chuyển trang
    I.wait(3);

    const orderCreated = await I.executeScript(async () => {
        const apiUrl = window.appUrl || ((path) => path);
        const productResponse = await fetch(apiUrl('/backend/public/index.php/api/products?limit=10'));
        const productPayload = await productResponse.json();
        const products = Array.isArray(productPayload) ? productPayload : (productPayload.data || []);
        const product = products.find((item) => parseInt(item.Stock_quantity || 0) > 0);
        if (!product) return false;

        const orderResponse = await fetch(apiUrl('/backend/public/index.php/api/orders'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: product.ID || product.id,
                quantity: 1,
                fullname: 'Huynh Duong Minh Triet',
                phone: '0962618573',
                shipping_address: '123 Nguyen Kiem, Go Vap, Ho Chi Minh',
                payment_method: 'COD'
            })
        });

        return orderResponse.ok;
    });

    if (!orderCreated) {
        throw new Error('Could not create a pending order for cancellation test');
    }

    // Vào lịch sử đơn hàng
    I.amOnPage('/frontend/pages/user/dashboard.php');

    I.see('Lịch sử mua hàng');
    
    I.click('Lịch sử mua hàng');

    I.wait(2);

    I.click('Hủy đơn');

    I.see('Xác nhận');

    I.click('Xác nhận');

});
