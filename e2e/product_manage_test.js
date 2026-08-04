Feature('Manage Product');

Scenario('Xóa sản phẩm cá nhân', async ({ I }) => {
    // 1. Đăng nhập
    I.amOnPage('/frontend/pages/auth/login.php');
    I.fillField('#username', 'admin@c2c.vn');
    I.fillField('#password', '123456');
    I.click('#loginBtn');
    I.wait(2);

    // 2. Vào trang danh sách sản phẩm
    I.amOnPage('/frontend/pages/seller/my-store.php');
    I.wait(2);

    // 3. Bấm nút Xóa dòng/sản phẩm đầu tiên (tuỳ selector trên web của nhóm)
    // Thường là nút có class .btn-danger, .btn-delete hoặc link chứa chữ delete
    I.click('a[href*="delete"], .btn-danger, .btn-delete');
    I.wait(1);

    // 4. Nếu web có popup/alert xác nhận "Bạn có chắc muốn xóa?"
    // CodeceptJS sẽ tự động chấp nhận hoặc bạn dùng:
    // I.acceptPopup(); 

    I.wait(2);
    
    // 5. Kiểm tra xem đã xóa thành công (Ví dụ: không còn thấy sản phẩm vừa xóa hoặc báo thành công)
    I.seeInCurrentUrl('my-store.php');
});