Feature('Category & Navigation');

const categories = [
  { id: 1, label: 'Electronics and technology' },
  { id: 3, label: 'Men fashion' },
  { id: 5, label: 'Books and documents' }
];

Scenario('User can navigate and filter products by category', async ({ I }) => {
  I.amOnPage('/frontend/pages/products/category.php');
  I.waitForElement('#categoriesList .category-link[data-id="1"]', 10);

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
