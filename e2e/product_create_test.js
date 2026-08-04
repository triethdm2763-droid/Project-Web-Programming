Feature('Create Product');

Scenario('Tạo sản phẩm mới và upload tệp ảnh', async ({ I }) => {
    // 1. Đăng nhập
    I.amOnPage('/frontend/pages/auth/login.php');
    I.fillField('#username', 'admin@c2c.vn');
    I.fillField('#password', '123456');
    I.click('#loginBtn');
    I.wait(2);

    // 2. Chuyển sang trang Tạo sản phẩm
    I.amOnPage('/frontend/pages/seller/create-product.php');
    I.wait(2);

    // 3. Nhập dữ liệu (Sử dụng selector chính xác hơn)
    // Nếu ô tên sản phẩm có name="name" hoặc id="product_name"
    // Trường hợp muốn chọn ô input dạng text đầu tiên trên màn hình:
    I.fillField('form input[type="text"]', 'Sản phẩm Test E2E'); 
    
    // 4. Upload tệp ảnh
    I.attachFile('input[type="file"]', 'e2e/sample.jpg'); 
    
    // 5. Submit form
    I.click('button[type="submit"]');
    I.wait(2);
});