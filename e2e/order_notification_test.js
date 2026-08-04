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

    // Mở trang Dashboard
    I.amOnPage('/frontend/pages/user/dashboard.php');

    // Kiểm tra chuông thông báo có hiển thị thông báo mới
    I.click('#notification-bell');
    I.wait(2);

    I.see('Bạn có đơn hàng mới');

});
