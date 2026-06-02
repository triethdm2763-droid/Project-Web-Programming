-- ============================================================
--  FILE: transaction_buy_product.sql
--  Mục đích: Kịch bản Transaction khóa hàng chống tranh chấp
--  Khi 2 người cùng mua 1 sản phẩm cùng lúc, chỉ 1 người thành công
-- ============================================================

USE c2c_used_marketplace;

-- ============================================================
-- KỊCH BẢN: Buyer_ID = 6 mua Product_ID = 1 (iPhone 12)
-- ============================================================

START TRANSACTION;

-- Bước 1: Khóa dòng sản phẩm lại (SELECT ... FOR UPDATE)
-- Không cho người khác đọc/sửa dòng này cho đến khi COMMIT
SELECT id, name, stock_quantity, status
FROM products
WHERE id = 1
FOR UPDATE;

-- Bước 2: Kiểm tra còn hàng và đang available không
-- Nếu stock_quantity < 1 hoặc status != 'available' thì ROLLBACK
-- (Trong PHP sẽ kiểm tra kết quả trả về, ở đây mô phỏng bằng SQL)

-- Bước 3: Tạo đơn hàng mới
INSERT INTO orders (Buyer_ID, Seller_ID, Product_ID, Total_price, Shipping_address, Status)
SELECT
    6,                          -- Buyer_ID (buyer_minh)
    seller_id,                  -- Seller_ID lấy từ bảng products
    id,                         -- Product_ID
    price,                      -- Total_price
    '123 Nguyễn Huệ, Q1, TP.HCM', -- Shipping_address
    'pending'                   -- Status
FROM products
WHERE id = 1;

-- Bước 4: Trừ stock_quantity xuống 1
UPDATE products
SET stock_quantity = stock_quantity - 1
WHERE id = 1
  AND stock_quantity > 0
  AND status = 'available';

-- Bước 5: Kiểm tra UPDATE có ảnh hưởng đúng 1 dòng không
-- Nếu ROW_COUNT() = 0 tức là hàng đã hết → ROLLBACK
-- Nếu ROW_COUNT() = 1 tức là trừ thành công → tiếp tục

-- Bước 6: Nếu stock_quantity về 0 thì đổi status thành 'sold'
UPDATE products
SET status = 'sold'
WHERE id = 1
  AND stock_quantity = 0;

-- Bước 7: Xác nhận toàn bộ thay đổi
COMMIT;

-- ============================================================
-- KỊCH BẢN ROLLBACK: Khi hàng đã hết (mô phỏng)
-- ============================================================

START TRANSACTION;

SELECT id, name, stock_quantity, status
FROM products
WHERE id = 1
FOR UPDATE;

-- Phát hiện stock_quantity = 0 → hủy toàn bộ
ROLLBACK;

-- ============================================================
-- KIỂM TRA KẾT QUẢ SAU TRANSACTION
-- ============================================================

-- Xem trạng thái sản phẩm sau khi mua
SELECT id, name, stock_quantity, status
FROM products
WHERE id = 1;

-- Xem đơn hàng vừa tạo
SELECT o.ID, o.Buyer_ID, u.username, o.Product_ID, p.name, o.Total_price, o.Status
FROM orders o
JOIN users u ON o.Buyer_ID = u.id
JOIN products p ON o.Product_ID = p.id
ORDER BY o.ID DESC
LIMIT 1;
