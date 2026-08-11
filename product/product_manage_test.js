Feature('Manage Product');

const seller = {
  username: 'seller_a',
  password: '123456',
};

Scenario('Delete only a product created by this test', async ({ I }) => {
  const productName = `E2E Manage Product ${Date.now()}`;

  I.amOnPage('/frontend/pages/auth/login.php');
  I.waitForElement('#username', 5);
  I.fillField('#username', seller.username);
  I.fillField('#password', seller.password);
  I.click('#loginBtn');
  I.waitForCookie('token', 5);

  const productId = await I.executeScript(async ({ productName }) => {
    const createUrl = window.appUrl
      ? window.appUrl('/backend/public/index.php/api/products')
      : '/backend/public/index.php/api/products';
    const response = await fetch(createUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: productName,
        category_id: 1,
        price: 120000,
        description: 'San pham tam thoi de test xoa.',
        image: 'p001.jpg',
        condition_status: '99%',
        used_duration: 'Duoi 1 nam',
        warranty: 'Khong bao hanh',
        accessories: 'Day sac',
        stock_quantity: 1,
      }),
    });

    const data = await response.json();
    if (!response.ok || !data.product_id) {
      throw new Error(data.error || data.message || 'Create product setup failed');
    }
    return data.product_id;
  }, { productName });

  const deleteResult = await I.executeScript(async ({ productId }) => {
    const deleteUrl = window.appUrl
      ? window.appUrl('/backend/public/index.php/api/products/delete')
      : '/backend/public/index.php/api/products/delete';
    const response = await fetch(deleteUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId }),
    });

    return {
      ok: response.ok,
      body: await response.json(),
    };
  }, { productId });

  if (!deleteResult.ok) {
    throw new Error(deleteResult.body.error || deleteResult.body.message || 'Delete product failed');
  }

  const stillExists = await I.executeScript(async ({ productId }) => {
    const listUrl = window.appUrl
      ? window.appUrl('/backend/public/index.php/api/products/mine?status=pending')
      : '/backend/public/index.php/api/products/mine?status=pending';
    const response = await fetch(listUrl);
    const products = await response.json();
    const items = Array.isArray(products) ? products : (products.data || []);
    return items.some((product) => String(product.ID || product.id) === String(productId));
  }, { productId });

  if (stillExists) {
    throw new Error('Product was not deleted');
  }
});
