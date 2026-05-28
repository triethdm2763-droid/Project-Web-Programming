CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,

    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (buyer_id)
    REFERENCES users(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
    REFERENCES orders(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    FOREIGN KEY (product_id)
    REFERENCES products(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

INSERT INTO orders (
    buyer_id,
    total_price,
    shipping_address,
    status
)
VALUES
(
    1,
    8500000,
    'Thu Duc, TP.HCM',
    'completed'
),
(
    2,
    7000000,
    'Bien Hoa, Dong Nai',
    'pending'
);

INSERT INTO order_details (
    order_id,
    product_id,
    quantity,
    price
)
VALUES
(
    1,
    1,
    1,
    8500000
),
(
    2,
    2,
    1,
    7000000
);

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
