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
        '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
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
    'nguyen_ban',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'nguyenban@gmail.com',
    '0901000002',
    'Nguyễn Văn Bán',
    '123 Đường Bán Hàng, Quận 1, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'tran_shop',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'transhop@gmail.com',
    '0901000003',
    'Trần Thị Shop',
    '456 Đường Cửa Hàng, Quận 3, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'le_secondhand',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'leshop@gmail.com',
    '0901000004',
    'Lê Đồ Cũ',
    '789 Đường Thanh Lý, Bình Thạnh, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'pham_cu',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'phamcu@gmail.com',
    '0901000005',
    'Phạm Hữu Cũ',
    '321 Đường Mua Bán, Quận 10, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),

-- Buyers
(
    'buyer_minh',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'minhbuyer@gmail.com',
    '0901000006',
    'Nguyễn Quang Minh',
    '12 Đường Mua Sắm, Tân Bình, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_lan',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'lanbuyer@gmail.com',
    '0901000007',
    'Lê Thị Ngọc Lan',
    '88 Đường Hoa Hồng, Gò Vấp, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_hung',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'hungbuyer@gmail.com',
    '0901000008',
    'Phạm Quốc Hùng',
    '19 Đường Lê Lợi, Quận 5, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_thu',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'thubuyer@gmail.com',
    '0901000009',
    'Đỗ Hoài Thu',
    '99 Đường Lý Thường Kiệt, Quận 11, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'active'
),
(
    'buyer_nam',
    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2',
    'nambuyer@gmail.com',
    '0901000010',
    'Trịnh Hữu Nam',
    '55 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM',
    'https://placehold.co/150x150',
    'user',
    'banned'
);