Feature('Authentication - Login');

Scenario(
  'AUTH-LOGIN-01: Đăng nhập sai hiển thị thông báo lỗi',
  ({ I }) => {
    I.amOnPage('/frontend/pages/auth/login.php');

    I.see('Đăng nhập tài khoản');

    I.fillField('#username', 'tai_khoan_khong_ton_tai');
    I.fillField('#password', 'sai_mat_khau');

    I.click('#loginBtn');

    I.waitForText('Thất bại', 10);
    I.see('Tên đăng nhập hoặc mật khẩu không chính xác.');
  }
);

Scenario(
  'AUTH-LOGIN-02: Đăng nhập đúng, chuyển trang và hiển thị Avatar',
  ({ I }) => {
    const uniqueId = `${Date.now()}${Math.floor(Math.random() * 1000)}`;

    const username = `login_${uniqueId}`;
    const email = `login_${uniqueId}@example.com`;
    const phone = `09${uniqueId.slice(-8)}`;
    const password = 'E2ePassword@123';

    // Chuẩn bị một tài khoản hợp lệ để test không phụ thuộc database seed
    I.amOnPage('/frontend/pages/auth/register.php');

    I.fillField('#username', username);
    I.fillField('#email', email);
    I.fillField('#phone', phone);
    I.fillField('#password', password);
    I.fillField('#confirmPassword', password);

    I.click('#registerBtn');

    I.waitForText('Đăng ký thành công!', 10);
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/auth/login.php'), 10);

    // Thực hiện đăng nhập bằng tài khoản vừa tạo
    I.fillField('#username', username);
    I.fillField('#password', password);

    I.click('#loginBtn');

    // Chuyển trang chứng minh đăng nhập thành công
    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/home/index.php'), 10);

    // Navbar hiển thị tên tài khoản
    I.see(username);

    // Kiểm tra Avatar trên trang tài khoản
    I.amOnPage('/frontend/pages/user/dashboard.php');

    I.waitForFunction(() => window.location.pathname.endsWith('/frontend/pages/user/dashboard.php'), 10);

    I.waitForElement('#sidebar-avatar', 10);
    I.seeElement('#sidebar-avatar[alt="Avatar"]');
  }
);
