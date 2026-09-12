Feature('Order Checkout');

Scenario('Buyer completes checkout successfully', async ({ I }) => {
    const uniqueId = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
    const username = `buyer_${uniqueId}`;
    const email = `buyer_${uniqueId}@example.com`;
    const phone = `09${uniqueId.slice(-8)}`;
    const password = 'E2ePassword@123';

    I.amOnPage('/frontend/pages/auth/register.php');
    I.fillField('#username', username);
    I.fillField('#email', email);
    I.fillField('#phone', phone);
    I.fillField('#password', password);
    I.fillField('#confirmPassword', password);
    I.click('#registerBtn');
    I.waitForText('Đăng ký thành công!', 10);
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/auth/login.php'), 10);

    I.fillField('#username', username);
    I.fillField('#password', password);
    I.click('#loginBtn');
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/home/index.php'), 10);

    I.waitForElement('a[href*="/frontend/pages/products/detail.php?id="]', 10);
    I.click('a[href*="/frontend/pages/products/detail.php?id="]');
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/products/detail.php'), 10);

    I.waitForElement('#btn-buy-now',10);
    I.waitForFunction(
        () => document.querySelector('#product-name')?.textContent.trim() !== 'Đang tải...',
        10
    );

    I.click('#btn-buy-now');

    I.waitForElement('#fullname', 10);

    I.fillField('#fullname','Huỳnh Dương Minh Triết');
    I.fillField('#phone', phone);
    I.fillField('#address','123 Nguyễn Kiệm');

    I.checkOption('input[name="payment_method"][value="cash"]');

    I.see('Đặt hàng');

    I.click('Đặt hàng');

    I.waitForText('Đơn hàng đã được tạo thành công!', 15);
    I.see('Thành công');


});
