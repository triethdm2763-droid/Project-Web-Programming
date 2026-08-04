Feature('Login');

Scenario('Login Seller', async ({ I }) => {
    I.amOnPage('/frontend/pages/auth/login.php');

    // Điền đúng username/password của Seller trong Database
    I.fillField('#username', 'admin@c2c.vn'); // hoặc 'seller@gmail.com' tùy DB của bạn
    I.fillField('#password', '123456');

    I.click('#loginBtn');
    I.wait(2);

    // Kiểm tra xem đã rời khỏi trang login chưa (Tránh match cứng URL nếu redirect khác nhau)
    I.dontSeeInCurrentUrl('/frontend/pages/auth/login.php');
});