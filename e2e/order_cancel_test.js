Feature('Order Cancel');

Scenario('Buyer cancels a pending order created by this test', async ({ I }) => {
  const productName = `E2E Cancel Product ${Date.now()}`;

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
        price: 120000,
        description: 'San pham tam thoi de test huy don.',
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
    const orderResponse = await fetch(apiPath('/backend/public/index.php/api/orders'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        product_id: created.product_id,
        quantity: 1,
        shipping_address: '123 Nguyen Van Cu, Quan 5, TP HCM',
        payment_method: 'COD',
        fullname: 'Buyer A',
        phone: '0901000006',
      }),
    });
    const order = await orderResponse.json();
    if (!orderResponse.ok || !order.order_id || !order.order_code) {
      throw new Error(order.error || order.message || 'Create order setup failed');
    }

    return { productId: created.product_id, orderId: order.order_id, orderCode: order.order_code };
  }, { productName });

  const cancelResult = await I.executeScript(async ({ orderId }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const response = await fetch(apiPath('/backend/public/index.php/api/orders/cancel'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: orderId }),
    });
    return { ok: response.ok, body: await response.json() };
  }, { orderId: setup.orderId });

  if (!cancelResult.ok) {
    throw new Error(cancelResult.body.error || cancelResult.body.message || 'Cancel order failed');
  }

  const trackedOrder = await I.executeScript(async ({ orderCode }) => {
    const apiPath = (path) => window.appUrl ? window.appUrl(path) : path;
    const response = await fetch(apiPath(`/backend/public/index.php/api/orders/track?code=${encodeURIComponent(orderCode)}`));
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || data.message || 'Track order failed');
    return data;
  }, { orderCode: setup.orderCode });

  if (trackedOrder.status !== 'cancelled') {
    throw new Error(`Expected cancelled order, got ${trackedOrder.status}`);
  }

  await I.executeScript(async ({ productId }) => {
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
    await fetch(apiPath('/backend/public/index.php/api/products/delete'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId }),
    });
  }, { productId: setup.productId });
});
