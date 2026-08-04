Feature('Search Product');

Scenario('Tìm kiếm và lọc danh sách sản phẩm', async ({ I }) => {
    I.amOnPage('/frontend/pages/products/category.php');
    I.wait(2);

    I.fillField('input[type="text"], input[type="search"]', 'Áo');
    I.pressKey('Enter');
    I.wait(2);

    I.seeInCurrentUrl('category.php');
});