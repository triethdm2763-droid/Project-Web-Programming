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

    // Kiểm tra chuông thông báo có hiển thị thông báo mới
    I.waitForElement('#nav-btn-notifications', 5);
    I.click('#nav-btn-notifications');  

    const badge = await I.grabTextFrom('#nav-notification-badge');
    console.log(badge);

    I.waitForElement('#nav-btn-notifications', 5);
    I.click('#nav-btn-notifications');

    I.waitForElement('#nav-notifications-dropdown', 5);

    I.see('Đặt mua thành công!', '#nav-notifications-dropdown');

});
