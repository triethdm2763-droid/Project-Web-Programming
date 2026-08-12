Feature('Notification');

Scenario('Seller receives notification after buyer places an order', async ({ I }) => {

    I.amOnPage('/frontend/pages/auth/login.php');

    // Đợi ô nhập xuất hiện
    I.waitForElement('#username', 5);

    // Đăng nhập
    I.fillField('#username', 'huynhduongminhtriet@gmail.com');
    I.fillField('#password', '12345678');

    I.click('#loginBtn');

    // Đợi chuyển trang
    I.wait(3);

    const createdOrderId = await I.executeScript(async () => {
        const apiUrl = window.appUrl || ((path) => path);
        const productResponse = await fetch(apiUrl('/backend/public/index.php/api/products?limit=10'));
        const productPayload = await productResponse.json();
        const products = Array.isArray(productPayload) ? productPayload : (productPayload.data || []);
        const product = products.find((item) => parseInt(item.Stock_quantity || 0) > 0);
        if (!product) return null;

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

        if (!orderResponse.ok) return null;
        const orderPayload = await orderResponse.json();
        return orderPayload.order_id || null;
    });

    if (!createdOrderId) {
        throw new Error('Could not create an order for notification test');
    }

    // Kiểm tra chuông thông báo có hiển thị thông báo mới
    I.waitForElement('#nav-btn-notifications', 5);
    I.click('#nav-btn-notifications');  

    const badge = await I.grabTextFrom('#nav-notification-badge');
    console.log(badge);

    I.waitForElement('#nav-btn-notifications', 5);
    I.click('#nav-btn-notifications');

    I.waitForElement('#nav-notifications-dropdown', 5);

    I.see('Đặt mua thành công!', '#nav-notifications-dropdown');

    await I.executeScript(async (orderId) => {
        const apiUrl = window.appUrl || ((path) => path);
        await fetch(apiUrl('/backend/public/index.php/api/orders/cancel'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });
    }, createdOrderId);

});
