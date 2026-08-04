Feature('Order Checkout');

Scenario('Buyer completes checkout successfully', async ({ I }) => {

    // Mở trang chủ
    I.amOnPage('/frontend/pages/home/index.php');

    // Kiểm tra đã vào đúng trang
    I.see('Tin đăng mới nhất');

    // Chọn một sản phẩm
    I.click('Độc bản');

    // Chờ trang chi tiết
    I.waitForElement('button', 10);
    I.waitForText('MUA NGAY', 10);

    I.scrollTo(locate('button').withText('MUA NGAY'));

    I.click('MUA NGAY');
    
    I.wait(2);

    I.scrollTo('Mua hàng');

    I.see('Mua hàng');

    i.click('Mua hàng');

    // Sang trang thanh toán
    I.waitForText('Thanh toán', 5);

    I.scrollTo('Đặt hàng');

    I.see('Đặt hàng');

    // Điền thông tin
    I.fillField('Họ và tên ', 'Huỳnh Dương Minh Triết');
    I.fillField('Số điện thoại', '0962618573');
    I.fillField('Email', 'huynhduongminhtriết')
    I.fillField('Địa chỉ giao hàng', '123 Nguyễn Văn Cừ');

    I.selectOption('Phương thức thanh toán', 'COD');

    // Đặt hàng
    I.click('Đặt hàng');

    // Kiểm tra kết quả
    I.waitForText('Đặt hàng thành công', 10);

});