-- ============================================================
--  FILE: seed_users.sql
--  Mục đích: Nạp dữ liệu mẫu cho bảng users
--  Mật khẩu mẫu: 280606
-- ============================================================

USE c2c_used_marketplace;

INSERT INTO users (username, password, email, phone, role, status) VALUES
-- Admin
('admin',        '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'admin@c2c.vn',          '0901000001', 'admin', 'active'),

-- Sellers
('nguyen_ban',   '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'nguyenban@gmail.com',   '0901000002', 'user',  'active'),
('tran_shop',    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'transhop@gmail.com',    '0901000003', 'user',  'active'),
('le_secondhand','$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'leshop@gmail.com',      '0901000004', 'user',  'active'),
('pham_cu',      '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'phamcu@gmail.com',      '0901000005', 'user',  'active'),

-- Buyers
('buyer_minh',   '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'minhbuyer@gmail.com',   '0901000006', 'user',  'active'),
('buyer_lan',    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'lanbuyer@gmail.com',    '0901000007', 'user',  'active'),
('buyer_hung',   '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'hungbuyer@gmail.com',   '0901000008', 'user',  'active'),
('buyer_thu',    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'thubuyer@gmail.com',    '0901000009', 'user',  'active'),
('buyer_nam',    '$2y$10$tZ2cK.2L7F/D.r9u8y7GVuM8B3jYn1l3iY2rZq4O7Xz.m1N8b.eG2', 'nambuyer@gmail.com',    '0901000010', 'user',  'banned');