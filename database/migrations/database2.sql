CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    total_price DECIMAL(12,0) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(12,0) NOT NULL
);

ALTER TABLE orders
ADD CONSTRAINT fk_orders_user
FOREIGN KEY (buyer_id)
REFERENCES users(ID)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE order_details
ADD CONSTRAINT fk_orderdetails_order
FOREIGN KEY (order_id)
REFERENCES orders(id)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE order_details
ADD CONSTRAINT fk_orderdetails_product
FOREIGN KEY (product_id)
REFERENCES products(ID)
ON DELETE CASCADE
ON UPDATE CASCADE;


SELECT *
FROM orders;

SELECT *
FROM order_details;

SELECT
    orders.id AS order_id,
    orders.total_price,
    orders.status,
    order_details.product_id,
    order_details.price
FROM orders
JOIN order_details
ON orders.id = order_details.order_id;
