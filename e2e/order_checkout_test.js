Feature('Order Checkout');

Scenario('Buyer completes checkout successfully', async ({ I }) => {

    I.amOnPage('/frontend/pages/home/index.php');

    I.click('Độc bản');

    I.waitForElement('#btn-buy-now',10);

    I.click('#btn-buy-now');

    I.waitForElement('#fullname', 5);

    I.fillField('#fullname','Huỳnh Dương Minh Triết');
    I.fillField('#phone','0962618573');
    I.fillField('#address','123 Nguyễn Kiệm');

    I.checkOption('input[name="payment_method"][value="cash"]');

    I.see('Đặt hàng');

    I.click('Đặt hàng');

    I.see('Đồng ý');
    
    I.click('Đồng ý');


});