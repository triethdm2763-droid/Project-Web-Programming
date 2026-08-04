Feature('Authentication - Register');

Scenario(
  'AUTH-REGISTER-01: Kiểm tra validate khi bỏ trống form',
  ({ I }) => {
    I.amOnPage('/frontend/pages/auth/register.php');

    I.see('Tạo tài khoản mới');
    I.click('#registerBtn');

    I.waitForText('Thiếu thông tin', 5);
    I.see('Vui lòng điền đầy đủ tất cả các trường.');

    I.click('#alert-confirm-btn');
  }
);

Scenario(
  'AUTH-REGISTER-02: Kiểm tra mật khẩu xác nhận không khớp',
  ({ I }) => {
    I.amOnPage('/frontend/pages/auth/register.php');

    I.fillField('#username', 'e2e_validate_user');
    I.fillField('#email', 'e2e_validate_user@example.com');
    I.fillField('#phone', '0912345678');
    I.fillField('#password', 'E2ePassword@123');
    I.fillField('#confirmPassword', 'MatKhauKhac@123');

    I.click('#registerBtn');

    I.waitForText('Không trùng khớp', 5);
    I.see('Mật khẩu xác nhận không khớp.');

    I.click('#alert-confirm-btn');
  }
);

Scenario(
  'AUTH-REGISTER-03: Đăng ký tài khoản thành công',
  ({ I }) => {
    const uniqueId = `${Date.now()}${Math.floor(Math.random() * 1000)}`;

    const username = `e2e_${uniqueId}`;
    const email = `e2e_${uniqueId}@example.com`;
    const phone = `09${uniqueId.slice(-8)}`;
    const password = 'E2ePassword@123';

    I.amOnPage('/frontend/pages/auth/register.php');

    I.see('Tạo tài khoản mới');

    I.fillField('#username', username);
    I.fillField('#email', email);
    I.fillField('#phone', phone);
    I.fillField('#password', password);
    I.fillField('#confirmPassword', password);

    I.click('#registerBtn');

    I.waitForText('Đăng ký thành công!', 10);
    I.waitInUrl(
  '/Project-Web-Programming/frontend/pages/auth/login.php',
  10);

    I.see('Đăng nhập tài khoản');
  }
);