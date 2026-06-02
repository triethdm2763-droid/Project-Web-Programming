-- ============================================================
--  FILE: seed_products.sql
--  Mục đích: Nạp dữ liệu mẫu cho bảng products
--  Phân loại theo categories, đủ 3 trạng thái: available, pending, sold
-- ============================================================

USE c2c_used_marketplace;

INSERT INTO products (name, description, image, category_id, seller_id, price, stock_quantity, status) VALUES

-- Danh mục 1: Điện tử & Công nghệ (category_id = 1)
('Laptop Dell Inspiron 15',     'Core i5 Gen 10, RAM 8GB, SSD 256GB, pin 4 tiếng, còn đẹp 90%',     'dell_inspiron.jpg',    1, 2, 8500000,  1, 'available'),
('Tai nghe Sony WH-1000XM3',    'Chống ồn tốt, còn bảo hành 3 tháng, đầy đủ phụ kiện',             'sony_wh1000.jpg',      1, 3, 2200000,  1, 'available'),

-- Danh mục 2: Điện thoại & Máy tính bảng (category_id = 2)
('iPhone 12 64GB Đen',          'Pin 89%, không trầy, có ốp lưng, sạc đầy đủ',                      'iphone12.jpg',         2, 2, 9800000,  1, 'available'),
('Samsung Galaxy S21',           'RAM 8GB, 128GB, màn hình đẹp, kèm sạc nhanh',                      'samsung_s21.jpg',      2, 4, 7500000,  1, 'pending'),

-- Danh mục 3: Thời trang Nam (category_id = 3)
('Áo khoác bomber nam',         'Size L, màu đen, mặc 2 lần, còn mới 95%',                          'bomber_nam.jpg',       3, 3, 280000,   1, 'available'),
('Giày Nike Air Force 1 sz42',  'Mặc vài lần, còn sạch, có hộp đầy đủ',                            'nike_af1.jpg',         3, 5, 1200000,  1, 'sold'),

-- Danh mục 5: Sách & Tài liệu (category_id = 5)
('Bộ sách Tư duy nhanh và chậm','Bản dịch mới 2023, còn nguyên vẹn, chưa ghi chép',                'sach_tuyduy.jpg',      5, 4, 120000,   1, 'available'),
('Giáo trình Lập trình Web',    'Dùng 1 học kỳ, còn sạch, không rách',                             'giaotrinh_web.jpg',    5, 2, 65000,    1, 'pending'),

-- Danh mục 6: Đồ gia dụng & Nội thất (category_id = 6)
('Máy xay sinh tố Philips',     'Dùng 1 năm, hoạt động tốt, đầy đủ cốc xay',                       'philips_xay.jpg',      6, 5, 350000,   1, 'available'),

-- Danh mục 7: Xe cộ & Phụ tùng (category_id = 7)
('Xe đạp địa hình Trinx M136',  'Khung nhôm size 17, 21 số, ít đi, còn tốt',                       'trinx_m136.jpg',       7, 3, 3200000,  1, 'sold');
