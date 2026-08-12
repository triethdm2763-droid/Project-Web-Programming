Feature('Category & Navigation');

Scenario('User can navigate and filter products by category', async ({ I }) => {
  I.amOnPage('/frontend/pages/products/category.php');
  I.waitForElement('#categoriesList .category-link', 10);

  const categories = await I.executeScript(async () => {
    const apiUrl = window.appUrl || ((path) => path);
    const response = await fetch(apiUrl('/backend/public/index.php/api/categories'));
    const payload = await response.json();
    const items = Array.isArray(payload) ? payload : (payload.data || []);
    const usable = [];

    for (const category of items) {
      const productsResponse = await fetch(apiUrl(`/backend/public/index.php/api/products?category_id=${category.ID}&limit=1`));
      const productsPayload = await productsResponse.json();
      const products = Array.isArray(productsPayload) ? productsPayload : (productsPayload.data || []);
      if (products.length > 0) {
        usable.push({ id: category.ID, label: category.Name });
      }
      if (usable.length >= 3) break;
    }

    return usable;
  });

  if (!categories.length) {
    throw new Error('No categories with active products are available for the filter test');
  }

  for (const category of categories) {
    I.say(`Checking category ${category.id}: ${category.label}`);

    I.click(`#categoriesList .category-link[data-id="${category.id}"]`);
    I.waitForFunction(
      (categoryId) => window.location.href.includes(`category=${categoryId}`),
      [category.id],
      5
    );

    I.waitForElement('#categoryTitle', 5);
    I.waitForElement('#categoryProducts', 5);
    I.waitForElement('#categoryProducts a[href*="detail.php?id="]', 10);

    I.seeInCurrentUrl(`/frontend/pages/products/category.php?category=${category.id}`);
    I.seeElement(`#categoriesList .category-link[data-id="${category.id}"]`);
    I.seeElement('#categoryProducts a[href*="detail.php?id="]');
  }
});
