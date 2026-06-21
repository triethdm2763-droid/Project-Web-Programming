-- ============================================================
--  FILE: seed_users.sql
--  Mục đích: Nạp dữ liệu mẫu cho bảng users
--  Mật khẩu mẫu: 280606
-- ============================================================

USE c2c_used_marketplace;

INSERT INTO
    users (
        username,
        password,
        email,
        phone,
        fullname,
        address,
        avatar,
        role,
        status
    )
VALUES
    -- Admin
    (
        'admin',
        '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
        'admin@c2c.vn',
        '0901000001',
        'Quản trị viên',
        'Hệ thống Chợ Cũ',
        'https://placehold.co/150x150',
        'admin',
        'active'
    ),

-- Sellers
(
    'seller_a',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'sellera@gmail.com',
    '0901000002',
    'Người Bán A',
    '123 Đường Bán Hàng, Quận 1, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'seller_b',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'sellerb@gmail.com',
    '0901000003',
    'Người Bán B',
    '456 Đường Cửa Hàng, Quận 3, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'seller_c',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'sellerc@gmail.com',
    '0901000004',
    'Người Bán C',
    '789 Đường Thanh Lý, Bình Thạnh, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'seller_d',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'sellerd@gmail.com',
    '0901000005',
    'Người Bán D',
    '321 Đường Mua Bán, Quận 10, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),

-- Buyers
(
    'buyer_a',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'buyera@gmail.com',
    '0901000006',
    'Người Mua A',
    '12 Đường Mua Sắm, Tân Bình, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_b',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'buyerb@gmail.com',
    '0901000007',
    'Người Mua B',
    '88 Đường Hoa Hồng, Gò Vấp, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_c',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'buyerc@gmail.com',
    '0901000008',
    'Người Mua C',
    '19 Đường Lê Lợi, Quận 5, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_d',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'buyerd@gmail.com',
    '0901000009',
    'Người Mua D',
    '99 Đường Lý Thường Kiệt, Quận 11, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_e',
    '$2y$10$vQ5Zou8g4JsPf.yqwb6RoOupImrsiZ.NH0YfLBxZ47OUNWK6QlgJG',
    'buyere@gmail.com',
    '0901000010',
    'Người Mua E (Bị Khoá)',
    '55 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'banned'
);