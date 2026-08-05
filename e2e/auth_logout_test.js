Feature('Authentication - Logout');

Scenario(
  'AUTH-LOGOUT-01: Đăng xuất thành công và xóa phiên đăng nhập',
  ({ I }) => {
    const uniqueId = `${Date.now()}${Math.floor(Math.random() * 1000)}`;

    const username = `logout_${uniqueId}`;
    const email = `logout_${uniqueId}@example.com`;
    const phone = `09${uniqueId.slice(-8)}`;
    const password = 'E2ePassword@123';

    // 1. Tạo một tài khoản mới
    I.amOnPage('/frontend/pages/auth/register.php');

    I.fillField('#username', username);
    I.fillField('#email', email);
    I.fillField('#phone', phone);
    I.fillField('#password', password);
    I.fillField('#confirmPassword', password);

    I.click('#registerBtn');

    I.waitForText('Đăng ký thành công!', 10);
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/auth/login.php'), 10);

    // 2. Đăng nhập bằng tài khoản vừa tạo
    I.fillField('#username', username);
    I.fillField('#password', password);

    I.click('#loginBtn');

    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/home/index.php'), 10);

    // Xác nhận tài khoản đã đăng nhập
    I.see(username);

    // 3. Mở menu tài khoản
    I.moveCursorTo('a[href$="/frontend/pages/user/dashboard.php"]');

    I.waitForVisible('button[title="Đăng xuất"]', 5);
    I.click('button[title="Đăng xuất"]');

    // 4. Xác nhận đăng xuất trong hộp thoại
    I.waitForText(
      'Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?',
      5
    );

    I.waitForVisible('#confirm-ok-btn', 5);
    I.click('#confirm-ok-btn');

    // 5. Kiểm tra đăng xuất thành công
    I.waitForText('Đăng xuất thành công!', 10);

    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/home/index.php'), 10);

    // Navbar phải hiện lại nút đăng nhập
    I.waitForElement('a[title="Đăng nhập"]', 5);
    I.seeElement('a[title="Đăng nhập"]');

    // 6. Kiểm tra session đã bị xóa
    I.amOnPage('/frontend/pages/user/dashboard.php');

    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/auth/login.php'), 10);

    I.see('Đăng nhập tài khoản');
  }
);
