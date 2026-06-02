-- ============================================================
--  FILE: seed_users.sql
--  Mục đích: Nạp dữ liệu mẫu cho bảng users
--  Mật khẩu mẫu: 280606
-- ============================================================

USE c2c_used_marketplace;

INSERT INTO users (username, password, email, phone, role, status) VALUES
-- Admin
('admin',        '280606', 'admin@c2c.vn',          '0901000001', 'admin', 'active'),

-- Sellers
('nguyen_ban',   '280606', 'nguyenban@gmail.com',   '0901000002', 'user',  'active'),
('tran_shop',    '280606', 'transhop@gmail.com',    '0901000003', 'user',  'active'),
('le_secondhand','280606', 'leshop@gmail.com',      '0901000004', 'user',  'active'),
('pham_cu',      '280606', 'phamcu@gmail.com',      '0901000005', 'user',  'active'),

-- Buyers
('buyer_minh',   '280606', 'minhbuyer@gmail.com',   '0901000006', 'user',  'active'),
('buyer_lan',    '280606', 'lanbuyer@gmail.com',    '0901000007', 'user',  'active'),
('buyer_hung',   '280606', 'hungbuyer@gmail.com',   '0901000008', 'user',  'active'),
('buyer_thu',    '280606', 'thubuyer@gmail.com',    '0901000009', 'user',  'active'),
('buyer_nam',    '280606', 'nambuyer@gmail.com',    '0901000010', 'user',  'banned');
