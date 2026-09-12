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

    // Vào lịch sử đơn hàng
    I.amOnPage('/frontend/pages/user/dashboard.php');

    I.see('Lịch sử mua hàng');
    
    I.click('Lịch sử mua hàng');

    I.wait(2);

    I.click('Hủy đơn');

    I.see('Xác nhận');

    I.click('Xác nhận');

});