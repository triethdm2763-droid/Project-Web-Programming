Feature('Search Product');

Scenario('Search products on the category page', async ({ I }) => {
  I.amOnPage('/frontend/pages/products/category.php');
  I.waitForElement('#categorySearchInput', 10);
  I.waitForElement('#categoryProducts', 10);

  I.fillField('#categorySearchInput', 'iPhone');

  I.waitForFunction(() => {
    const url = new URL(window.location.href);
    return url.searchParams.get('search') === 'iPhone';
  }, 5);

  I.waitForText('iPhone', 10, '#categoryProducts');
  I.seeInCurrentUrl('category.php');
});
