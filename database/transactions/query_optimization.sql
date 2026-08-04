-- ============================================================
--  FILE: query_optimization.sql
--  Mục đích: Tối ưu hóa câu lệnh truy vấn JOIN giữa
--            products, categories và users
-- ============================================================

USE c2c_used_marketplace;

-- ============================================================
-- BƯỚC 1: TẠO INDEX ĐỂ TĂNG TỐC TRUY VẤN
-- (Chạy 1 lần duy nhất)
-- ============================================================

-- Index cho cột tìm kiếm thường xuyên
CREATE INDEX IF NOT EXISTS idx_products_status ON products (status);

CREATE INDEX IF NOT EXISTS idx_products_category_id ON products (category_id);

CREATE INDEX IF NOT EXISTS idx_products_seller_id ON products (seller_id);

CREATE INDEX IF NOT EXISTS idx_products_price ON products (price);

-- ============================================================
-- QUERY 1: Lấy danh sách sản phẩm đang bán kèm danh mục và người bán
-- Dùng cho trang chủ / danh sách sản phẩm
-- ============================================================

SELECT
    p.id,
    p.name AS ten_san_pham,
    p.price AS gia,
    p.stock_quantity AS so_luong,
    p.status AS trang_thai,
    c.name AS danh_muc,
    u.username AS nguoi_ban,
    u.phone AS sdt_nguoi_ban,
    p.created_at AS ngay_dang
FROM
    products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN users u ON p.seller_id = u.id
WHERE
    p.status = 'available'
ORDER BY p.created_at DESC;

-- ============================================================
-- QUERY 2: Lấy sản phẩm theo danh mục cụ thể
-- Dùng cho trang lọc theo danh mục
-- ============================================================

SELECT
    p.id,
    p.name AS ten_san_pham,
    p.price AS gia,
    p.status AS trang_thai,
    c.name AS danh_muc,
    u.username AS nguoi_ban
FROM
    products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN users u ON p.seller_id = u.id
WHERE
    p.status = 'available'
    AND c.name = 'Điện tử & Công nghệ'
ORDER BY p.price ASC;

-- ============================================================
-- QUERY 3: Lấy tất cả sản phẩm của 1 seller
-- Dùng cho trang quản lý sản phẩm của seller
-- ============================================================

SELECT
    p.id,
    p.name AS ten_san_pham,
    p.price AS gia,
    p.status AS trang_thai,
    c.name AS danh_muc,
    p.created_at AS ngay_dang
FROM products p
    INNER JOIN categories c ON p.category_id = c.id
WHERE
    p.seller_id = 2
ORDER BY p.created_at DESC;

-- ============================================================
-- QUERY 4: Thống kê số sản phẩm theo từng danh mục
-- Dùng cho trang admin / báo cáo
-- ============================================================

SELECT
    c.name AS danh_muc,
    COUNT(p.id) AS tong_san_pham,
    SUM(
        CASE
            WHEN p.status = 'available' THEN 1
            ELSE 0
        END
    ) AS dang_ban,
    SUM(
        CASE
            WHEN p.status = 'pending' THEN 1
            ELSE 0
        END
    ) AS cho_duyet,
    SUM(
        CASE
            WHEN p.status = 'sold' THEN 1
            ELSE 0
        END
    ) AS da_ban
FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
GROUP BY
    c.id,
    c.name
ORDER BY tong_san_pham DESC;

-- ============================================================
-- QUERY 5: Đo tốc độ truy vấn bằng EXPLAIN
-- Dùng để kiểm tra hiệu năng, đảm bảo dưới 0.5s
-- ============================================================

EXPLAIN
SELECT p.id, p.name, p.price, c.name AS danh_muc, u.username AS nguoi_ban
FROM
    products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN users u ON p.seller_id = u.id
WHERE
    p.status = 'available'
ORDER BY p.created_at DESC;