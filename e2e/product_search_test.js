Feature('Search Product');

Scenario('Search products on the category page', async ({ I }) => {
  I.amOnPage('/frontend/pages/products/category.php');
  I.waitForElement('#categorySearchInput', 10);
  I.waitForElement('#categoryProducts', 10);

  const searchTarget = await I.executeScript(async () => {
    const apiUrl = window.appUrl || ((path) => path);
    const response = await fetch(apiUrl('/backend/public/index.php/api/products?limit=1'));
    const payload = await response.json();
    const products = Array.isArray(payload) ? payload : (payload.data || []);
    const product = products[0];
    if (!product) return null;

    const name = product.Name || product.name || '';
    return {
      query: name.split(/\s+/)[0],
      name,
    };
  });

  if (!searchTarget) {
    throw new Error('No active product is available for the search test');
  }

  I.fillField('#categorySearchInput', searchTarget.query);

  I.waitForText(searchTarget.query, 10, '#categoryProducts');
  I.seeInCurrentUrl('category.php');
});
