Feature('Admin E2E Testing');

Scenario('E2E-ADMIN-01: Admin đăng nhập thành công', async ({ I }) => {
    I.amOnPage('/frontend/pages/auth/login.php');
    I.fillField('#username', 'admin@c2c.vn');
    I.fillField('#password', '123456');
    I.click('#loginBtn');
    I.wait(2);
    
    // Sửa lại kiểm tra URL chứa admin/dashboard.php
    I.seeInCurrentUrl('/admin/dashboard.php'); 
});