Feature('Order Checkout');

Scenario('Buyer completes checkout successfully', async ({ I }) => {
  const productName = `E2E Checkout Product ${Date.now()}`;

  I.amOnPage('/frontend/pages/home/index.php');

  const setup = await I.executeScript(async ({ productName }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const loginAs = async (username, password) => {
      const response = await fetch(apiPath('/backend/public/index.php/api/auth/login'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || data.message || `Login failed for ${username}`);
      return data;
    };

    await loginAs('seller_a', '123456');

    const createResponse = await fetch(apiPath('/backend/public/index.php/api/products'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: productName,
        category_id: 1,
        price: 150000,
        description: 'San pham tam thoi de test dat hang.',
        image: 'p001.jpg',
        condition_status: '99%',
        used_duration: 'Duoi 1 nam',
        warranty: 'Khong bao hanh',
        accessories: 'Day sac',
        stock_quantity: 2,
      }),
    });
    const created = await createResponse.json();
    if (!createResponse.ok || !created.product_id) {
      throw new Error(created.error || created.message || 'Create product setup failed');
    }

    await loginAs('admin', '123456');
    const approveResponse = await fetch(apiPath('/backend/public/index.php/api/admin/products/update-status'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: created.product_id, status: 'available' }),
    });
    const approved = await approveResponse.json();
    if (!approveResponse.ok || approved.success !== true) {
      throw new Error(approved.message || 'Approve product setup failed');
    }

    await loginAs('buyer_a', '123456');
    return { productId: created.product_id };
  }, { productName });

  const checkout = await I.executeScript(async ({ productId }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const response = await fetch(apiPath('/backend/public/index.php/api/orders'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        product_id: productId,
        quantity: 1,
        shipping_address: '123 Nguyen Van Cu, Quan 5, TP HCM',
        payment_method: 'COD',
        fullname: 'Buyer A',
        phone: '0901000006',
      }),
    });
    const data = await response.json();
    if (!response.ok || !data.order_id || !data.order_code) {
      throw new Error(data.error || data.message || 'Checkout failed');
    }
    return data;
  }, setup);

  const trackedOrder = await I.executeScript(async ({ orderCode }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const response = await fetch(apiPath(`/backend/public/index.php/api/orders/track?code=${encodeURIComponent(orderCode)}`));
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || data.message || 'Track order failed');
    return data;
  }, { orderCode: checkout.order_code });

  if (trackedOrder.order_code !== checkout.order_code || trackedOrder.status !== 'pending') {
    throw new Error('Created order was not found with pending status');
  }

  await I.executeScript(async ({ orderId, productId }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const loginAs = async (username, password) => {
      const response = await fetch(apiPath('/backend/public/index.php/api/auth/login'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || data.message || `Login failed for ${username}`);
      return data;
    };

    await fetch(apiPath('/backend/public/index.php/api/orders/cancel'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: orderId }),
    });

    await loginAs('seller_a', '123456');
    await fetch(apiPath('/backend/public/index.php/api/products/delete'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId }),
    });
  }, { orderId: checkout.order_id, productId: setup.productId });
});
