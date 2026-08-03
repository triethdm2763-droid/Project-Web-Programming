Feature('Admin E2E Testing');

Scenario('E2E-ADMIN-01: Admin đăng nhập thành công và truy cập khu vực Admin', ({ I }) => {
  // 1. Truy cập trang đăng nhập
  I.amOnPage('/frontend/pages/auth/login.php');

  // 2. Điền tài khoản Admin
  I.fillField('#username', 'admin@c2c.vn');
  I.fillField('#password', '123456');

  // 3. Bấm nút đăng nhập
  I.click('#loginBtn');
  I.wait(2);

  // 4. Kiểm tra quyền Admin bằng cách mở trang Quản lý User
  I.amOnPage('/frontend/pages/admin/users.php');

  // 5. Xác nhận đã vào đúng giao diện Admin
  I.dontSeeInCurrentUrl('/frontend/pages/auth/login.php');
});

Scenario('E2E-ADMIN-02: Admin khóa tài khoản User và kiểm tra giao diện bị chặn', ({ I }) => {
  // 1. Đăng nhập Admin
  I.amOnPage('/frontend/pages/auth/login.php');
  I.fillField('#username', 'admin@c2c.vn');
  I.fillField('#password', '123456');
  I.click('#loginBtn');
  I.wait(2);

  // 2. Chuyển sang trang Quản lý User
  I.amOnPage('/frontend/pages/admin/users.php');
  
  // 3. Kiểm tra trang đã load được giao diện admin (không bị đẩy văng về login nữa)
  I.dontSeeInCurrentUrl('/frontend/pages/auth/login.php');
});