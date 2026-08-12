Feature('Create Product');

const seller = {
  username: 'seller_a',
  password: '123456',
};

Scenario('Create a new product from the seller post form', async ({ I }) => {
  const productName = `E2E Create Product ${Date.now()}`;

  I.amOnPage('/frontend/pages/auth/login.php');
  I.waitForElement('#username', 5);
  I.fillField('#username', seller.username);
  I.fillField('#password', seller.password);
  I.click('#loginBtn');
  I.waitForCookie('token', 5);

  I.amOnPage('/frontend/pages/seller/post-ad.php');
  I.waitForElement('#post-ad-form', 5);
  I.waitForElement('#category_id option[value="1"]', 10);

  I.fillField('#title', productName);
  I.selectOption('#category_id', '1');
  I.fillField('#price', '150000');
  I.fillField('#stock_quantity', '1');
  I.selectOption('#condition', '99%');
  I.fillField('#input-usage', 'Duoi 1 nam');
  I.fillField('#input-warranty', 'Khong bao hanh');
  I.fillField('#accessories', 'Day sac');
  I.fillField('#description', 'San pham duoc tao boi CodeceptJS E2E.');
  I.executeScript(() => {
    const location = document.querySelector('#location');
    location.selectedIndex = 2;
    location.dispatchEvent(new Event('change', { bubbles: true }));
  });
  I.fillField('#phone', '0901000002');

  I.attachFile('#image-file-input', 'backend/uploads/products/p001.jpg');
  I.waitForFunction(() => {
    const input = document.querySelector('#uploaded-image-name');
    return input && input.value.length > 0;
  }, 10);

  I.click('#submitBtn');
  I.wait(2);

  const createdProduct = await I.executeScript(async ({ productName }) => {
    const apiUrl = window.appUrl
      ? window.appUrl('/backend/public/index.php/api/products/mine?status=pending')
      : '/backend/public/index.php/api/products/mine?status=pending';
    const response = await fetch(apiUrl);
    const products = await response.json();
    const items = Array.isArray(products) ? products : (products.data || []);
    return items.find((product) => product.Name === productName || product.name === productName) || null;
  }, { productName });

  if (!createdProduct) {
    throw new Error('Created product was not found in pending seller products');
  }

  const cleanupResult = await I.executeScript(async ({ productId }) => {
    const deleteUrl = window.appUrl
      ? window.appUrl('/backend/public/index.php/api/products/delete')
      : '/backend/public/index.php/api/products/delete';
    const response = await fetch(deleteUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId }),
    });

    return response.ok;
  }, { productId: createdProduct.ID || createdProduct.id });

  if (!cleanupResult) {
    throw new Error('Created product cleanup failed');
  }
});
