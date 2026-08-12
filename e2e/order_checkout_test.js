Feature('Order Checkout');

Scenario('Buyer completes checkout successfully', async ({ I }) => {
    I.amOnPage('/frontend/pages/auth/login.php');
    I.waitForElement('#username', 5);
    I.fillField('#username', 'huynhduongminhtriet@gmail.com');
    I.fillField('#password', '12345678');
    I.click('#loginBtn');
    I.waitForCookie('token', 5);

    I.amOnPage('/frontend/pages/home/index.php');

    I.waitForElement('a[href*="/frontend/pages/products/detail.php?id="]', 10);
    I.click('a[href*="/frontend/pages/products/detail.php?id="]');

    I.waitForElement('#btn-buy-now', 10);
    I.waitForFunction(() => {
        const name = document.querySelector('#product-name')?.textContent?.trim();
        return name && !name.includes('Dang tai') && !name.includes('Đang tải');
    }, 10);

    I.click('#btn-buy-now');

    I.waitForElement('#fullname', 5);
    I.fillField('#fullname', 'Huynh Duong Minh Triet');
    I.fillField('#phone', '0962618573');
    I.fillField('#address', '123 Nguyen Kiem');

    I.checkOption('input[name="payment_method"][value="cash"]');

    I.waitForElement('#btn-place-order', 5);
    I.click('#btn-place-order');

    I.waitForFunction(() => window.location.pathname.includes('/frontend/pages/payment/track.php'), 10);

    await I.executeScript(async () => {
        const apiUrl = window.appUrl || ((path) => path);
        const orderCode = new URL(window.location.href).searchParams.get('id');
        if (!orderCode) return false;

        const historyResponse = await fetch(apiUrl('/backend/public/index.php/api/orders/buyer'));
        const orders = await historyResponse.json();
        const order = (Array.isArray(orders) ? orders : []).find((item) => {
            return (item.Order_Code || item.order_code) === orderCode;
        });

        if (!order) return false;

        await fetch(apiUrl('/backend/public/index.php/api/orders/cancel'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: order.ID || order.id })
        });

        return true;
    });
});
