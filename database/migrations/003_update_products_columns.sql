-- ============================================================
--  FILE: seed_products.sql
--  Mục đích: Nạp lại 100 sản phẩm với cấu trúc cột mới
--  Đã tách "Tình trạng" và "Phụ kiện" từ Description ra 2 cột riêng
--  Thêm cột Warranty (bảo hành) và Used_duration (thời gian đã dùng)
--  File tự thêm cột nếu chưa có, không cần chạy file nào khác trước
-- ============================================================

USE c2c_used_marketplace;

-- Bước 1: Thêm 4 cột mới vào bảng products (nếu chưa có)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'c2c_used_marketplace'
    AND TABLE_NAME = 'products'
    AND COLUMN_NAME = 'Condition_status'
);

SET @sql_add_cols = IF(@col_exists = 0,
  'ALTER TABLE products
   ADD COLUMN Condition_status VARCHAR(50)  NOT NULL DEFAULT "" COMMENT "Tình trạng sản phẩm" AFTER Status,
   ADD COLUMN Accessories      VARCHAR(255) NOT NULL DEFAULT "" COMMENT "Phụ kiện kèm theo"   AFTER Condition_status,
   ADD COLUMN Warranty         VARCHAR(50)  NOT NULL DEFAULT "Không bảo hành" COMMENT "Bảo hành" AFTER Accessories,
   ADD COLUMN Used_duration    VARCHAR(50)  NOT NULL DEFAULT "" COMMENT "Thời gian đã sử dụng" AFTER Warranty',
  'SELECT "Cột đã tồn tại, bỏ qua" AS thong_bao'
);

PREPARE stmt FROM @sql_add_cols;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Bước 2: Xóa dữ liệu cũ theo đúng thứ tự (con trước, cha sau)
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM payments;
ALTER TABLE payments AUTO_INCREMENT = 1;

DELETE FROM notifications;
ALTER TABLE notifications AUTO_INCREMENT = 1;

DELETE FROM orders;
ALTER TABLE orders AUTO_INCREMENT = 1;

DELETE FROM products;
ALTER TABLE products AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Bước 3: Nạp lại 100 sản phẩm với đầy đủ cột mới

INSERT INTO products (name, description, image, category_id, seller_id, price, stock_quantity, status, Condition_status, Accessories, Warranty, Used_duration) VALUES
('Laptop Điện tử & Công nghệ #1', 'Laptop thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: Core i5 Gen 10, RAM 8GB, SSD 256GB, Pin ~4h, Vỏ còn đẹp 90%. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p001.jpg', 1, 5, 12200000, 1, 'pending', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 3 tháng', '3 tháng'),
('PC mini Điện tử & Công nghệ #2', 'PC mini thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: Ryzen 5, RAM 16GB, SSD 512GB, Gọn nhẹ, Phù hợp học tập/VP. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p002.jpg', 1, 5, 5000000, 0, 'sold', 'mới 90%', 'đủ phụ kiện', 'Còn 6 tháng', '2 năm'),
('Màn hình Điện tử & Công nghệ #3', 'Màn hình thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: 24 inch IPS, Full HD, Màu đẹp, Ít hở sáng, Chân đế chắc chắn. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p003.jpg', 1, 4, 11700000, 1, 'available', 'ngoại hình 90%', 'có hộp', 'Không bảo hành', '2 tháng'),
('Bàn phím cơ Điện tử & Công nghệ #4', 'Bàn phím cơ thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: Switch Brown, LED trắng, Gõ êm, Keycap PBT, Có hộp. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p004.jpg', 1, 2, 16500000, 0, 'sold', 'còn đẹp 95%', 'không kèm phụ kiện', 'Còn 12 tháng', '2 tháng'),
('Chuột gaming Điện tử & Công nghệ #5', 'Chuột gaming thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: DPI cao, Click bền, Form ôm tay, Dây còn tốt, Chơi game mượt. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p005.jpg', 1, 3, 17000000, 0, 'sold', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 3 tháng', '1.5 năm'),
('SSD Điện tử & Công nghệ #6', 'SSD thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: NVMe 512GB, Tốc độ cao, Health tốt, Test ổn định, Phù hợp nâng cấp. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p006.jpg', 1, 2, 1900000, 1, 'available', 'còn đẹp 95%', 'không kèm phụ kiện', 'Không bảo hành', '1 năm'),
('Router Wifi Điện tử & Công nghệ #7', 'Router Wifi thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: WiFi 6, Băng tần kép, Phủ sóng tốt, Cài đặt dễ, Có adapter. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p007.jpg', 1, 2, 15650000, 1, 'pending', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 1 tháng', '1 tháng'),
('Loa bluetooth Điện tử & Công nghệ #8', 'Loa bluetooth thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: Âm bass, Pin trâu, Kết nối ổn, Có mic, Nhỏ gọn. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p008.jpg', 1, 3, 15400000, 1, 'pending', 'ít sử dụng', 'có hóa đơn', 'Không bảo hành', '8 tháng'),
('Webcam Điện tử & Công nghệ #9', 'Webcam thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: Full HD, Mic rõ, Tự động lấy nét, Cắm là chạy, Hợp học online. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p009.jpg', 1, 2, 13050000, 1, 'available', 'ngoại hình 90%', 'tặng kèm ốp/bao', 'Còn 6 tháng', '2 tháng'),
('Máy in Điện tử & Công nghệ #10', 'Máy in thuộc danh mục Điện tử & Công nghệ. Thông số/đặc điểm: In WiFi, In 2 mặt, Còn mực, Ít dùng, Có dây nguồn. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p010.jpg', 1, 2, 10700000, 1, 'available', 'dùng kỹ, còn bền', 'tặng kèm ốp/bao', 'Còn 1 tháng', '2 tháng'),
('iPhone Điện thoại & Máy tính bảng #1', 'iPhone thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Pin tốt, Ngoại hình đẹp, FaceID ổn, Không cấn móp, Kèm ốp. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p011.jpg', 2, 4, 13400000, 1, 'available', 'ít sử dụng', 'đủ phụ kiện', 'Còn 12 tháng', '8 tháng'),
('Samsung Galaxy Điện thoại & Máy tính bảng #2', 'Samsung Galaxy thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Màn hình đẹp, RAM 8GB, Máy mượt, Loa to, Có sạc nhanh. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p012.jpg', 2, 5, 3050000, 1, 'available', 'còn đẹp 95%', 'không kèm phụ kiện', 'Không bảo hành', '1.5 năm'),
('Xiaomi Điện thoại & Máy tính bảng #3', 'Xiaomi thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Cấu hình cao, Pin khỏe, Sạc nhanh, Chơi game tốt, Màn đẹp. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p013.jpg', 2, 2, 2000000, 1, 'available', 'ít sử dụng', 'có hóa đơn', 'Không bảo hành', '4 tháng'),
('iPad Điện thoại & Máy tính bảng #4', 'iPad thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Màn 10.2 inch, Pin tốt, Loa ổn, Học tập ok, Kèm bao da. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p014.jpg', 2, 5, 1600000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 12 tháng', '1 tháng'),
('AirPods Điện thoại & Máy tính bảng #5', 'AirPods thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Nghe hay, Mic ổn, Pin tốt, Hộp sạc ok, Kết nối nhanh. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p015.jpg', 2, 3, 2150000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 12 tháng', '1.5 năm'),
('Sạc nhanh Điện thoại & Máy tính bảng #6', 'Sạc nhanh thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: 65W, An toàn, Có PD, Dùng được nhiều máy, Nhỏ gọn. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p016.jpg', 2, 4, 8250000, 1, 'available', 'ít sử dụng', 'có hộp', 'Còn 6 tháng', '1 tháng'),
('Cáp sạc Điện thoại & Máy tính bảng #7', 'Cáp sạc thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Type-C, Dày, Sạc ổn, Dài 1m, Chống đứt. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p017.jpg', 2, 3, 3700000, 1, 'available', 'dùng kỹ, còn bền', 'có hộp', 'Còn 1 tháng', '1 tháng'),
('Kính cường lực Điện thoại & Máy tính bảng #8', 'Kính cường lực thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Chống xước, Trong, Dán dễ, Full màn, Kèm phụ kiện. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p018.jpg', 2, 5, 12200000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Còn 12 tháng', '3 tháng'),
('Ốp lưng Điện thoại & Máy tính bảng #9', 'Ốp lưng thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Chống sốc, Ôm khít, Không ố, Cầm chắc, Mới 90%. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p019.jpg', 2, 3, 11750000, 1, 'available', 'mới 90%', 'đủ phụ kiện', 'Còn 3 tháng', '8 tháng'),
('Dock sạc Điện thoại & Máy tính bảng #10', 'Dock sạc thuộc danh mục Điện thoại & Máy tính bảng. Thông số/đặc điểm: Để bàn, Gọn, Tiện, Sạc ổn định, Có cáp. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p020.jpg', 2, 5, 3250000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 1 tháng', '1 năm'),
('Áo thun Thời trang Nam #1', 'Áo thun thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Cotton 100%, Thoáng, Màu basic, Ít xù, Form đẹp. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p021.jpg', 3, 4, 760000, 1, 'available', 'ngoại hình 90%', 'có hộp', 'Không bảo hành', '1.5 năm'),
('Áo khoác Thời trang Nam #2', 'Áo khoác thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Dày dặn, Chống gió, Khóa kéo tốt, Ít sờn, Giữ ấm. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p022.jpg', 3, 2, 1080000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 3 tháng', '1 năm'),
('Quần jean Thời trang Nam #3', 'Quần jean thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Form slim, Co giãn, Không phai nhiều, Ít sờn, Dễ phối. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p023.jpg', 3, 4, 1200000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 1 tháng', '2 tháng'),
('Giày sneaker Thời trang Nam #4', 'Giày sneaker thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Size chuẩn, Đế êm, Ít mòn, Có hộp, Dễ vệ sinh. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p024.jpg', 3, 5, 820000, 1, 'available', 'mới 90%', 'tặng kèm ốp/bao', 'Còn 12 tháng', '1.5 năm'),
('Đồng hồ Thời trang Nam #5', 'Đồng hồ thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Pin mới, Chạy chuẩn, Dây còn tốt, Mặt ít xước, Lịch hoạt động. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p025.jpg', 3, 3, 600000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Còn 1 tháng', '6 tháng'),
('Thắt lưng Thời trang Nam #6', 'Thắt lưng thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Da thật, Khóa chắc, Ít tróc, Dễ chỉnh, Màu nâu. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p026.jpg', 3, 5, 1020000, 1, 'available', 'mới 90%', 'tặng kèm ốp/bao', 'Không bảo hành', '1 năm'),
('Áo sơ mi Thời trang Nam #7', 'Áo sơ mi thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Ít nhăn, Vải mát, Form vừa, Cúc đầy đủ, Mới 90%. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p027.jpg', 3, 3, 1540000, 1, 'available', 'mới 90%', 'có hóa đơn', 'Không bảo hành', '1.5 năm'),
('Balo Thời trang Nam #8', 'Balo thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Chống nước, Nhiều ngăn, Khóa tốt, Lót dày, Đeo êm. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p028.jpg', 3, 3, 860000, 0, 'sold', 'ngoại hình 90%', 'đủ phụ kiện', 'Không bảo hành', '1.5 năm'),
('Mũ lưỡi trai Thời trang Nam #9', 'Mũ lưỡi trai thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Màu basic, Dễ phối, Vải bền, Ít phai, Size free. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p029.jpg', 3, 3, 480000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 1 tháng', '10 tháng'),
('Áo hoodie Thời trang Nam #10', 'Áo hoodie thuộc danh mục Thời trang Nam. Thông số/đặc điểm: Nỉ ấm, Form rộng, Ít xù, Mũ dày, Dễ mặc. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p030.jpg', 3, 5, 280000, 1, 'available', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 12 tháng', '8 tháng'),
('Váy Thời trang Nữ #1', 'Váy thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Dễ phối, Vải nhẹ, Không rách, Ít nhăn, Màu xinh. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p031.jpg', 4, 4, 160000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Còn 3 tháng', '10 tháng'),
('Áo len Thời trang Nữ #2', 'Áo len thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Mềm, Giữ ấm, Không xù nhiều, Form đẹp, Màu pastel. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p032.jpg', 4, 5, 1140000, 1, 'available', 'dùng kỹ, còn bền', 'tặng kèm ốp/bao', 'Còn 12 tháng', '10 tháng'),
('Túi xách Thời trang Nữ #3', 'Túi xách thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Đẹp, Khóa tốt, Lót sạch, Ít trầy, Dễ phối. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p033.jpg', 4, 2, 1920000, 1, 'available', 'mới 90%', 'đủ phụ kiện', 'Còn 3 tháng', '5 tháng'),
('Giày cao gót Thời trang Nữ #4', 'Giày cao gót thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Êm, Đế chắc, Ít mòn, Size chuẩn, Đi tiệc ok. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p034.jpg', 4, 2, 1500000, 1, 'available', 'ngoại hình 90%', 'có hóa đơn', 'Còn 1 tháng', '3 tháng'),
('Áo khoác Thời trang Nữ #5', 'Áo khoác thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Form chuẩn, Giữ ấm, Ít sờn, Khóa tốt, Màu dễ mặc. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p035.jpg', 4, 2, 1980000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 1 tháng', '2 tháng'),
('Quần culottes Thời trang Nữ #6', 'Quần culottes thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Thoải mái, Vải mát, Dễ phối, Ống rộng, Mới 90%. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p036.jpg', 4, 3, 1220000, 1, 'available', 'ít sử dụng', 'có hóa đơn', 'Còn 12 tháng', '5 tháng'),
('Áo blouse Thời trang Nữ #7', 'Áo blouse thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Nhẹ, Nữ tính, Ít nhăn, Form đẹp, Dễ giặt. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p037.jpg', 4, 4, 800000, 1, 'pending', 'còn đẹp 95%', 'đủ phụ kiện', 'Còn 12 tháng', '10 tháng'),
('Chân váy Thời trang Nữ #8', 'Chân váy thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Xếp ly, Dáng đẹp, Khóa ổn, Màu trung tính, Mới 90%. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p038.jpg', 4, 4, 300000, 1, 'available', 'mới 90%', 'đủ phụ kiện', 'Còn 3 tháng', '10 tháng'),
('Khăn choàng Thời trang Nữ #9', 'Khăn choàng thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Ấm, Mềm, Không xù, Dễ phối, Màu đẹp. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p039.jpg', 4, 3, 1760000, 0, 'sold', 'dùng kỹ, còn bền', 'tặng kèm ốp/bao', 'Còn 3 tháng', '1.5 năm'),
('Phụ kiện Thời trang Nữ #10', 'Phụ kiện thuộc danh mục Thời trang Nữ. Thông số/đặc điểm: Nhỏ xinh, Dễ dùng, Không gỉ, Còn mới, Tặng kèm. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p040.jpg', 4, 4, 1280000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Không bảo hành', '2 tháng'),
('Sách kỹ năng Sách & Tài liệu #1', 'Sách kỹ năng thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Bản đẹp, Không rách, Không ghi chú, Bìa cứng, Nội dung hay. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p041.jpg', 5, 5, 70000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Còn 12 tháng', '8 tháng'),
('Sách lập trình Sách & Tài liệu #2', 'Sách lập trình thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Có ví dụ, Bản mới, Không rách, Học tốt, In rõ. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p042.jpg', 5, 2, 110000, 1, 'available', 'ít sử dụng', 'tặng kèm ốp/bao', 'Còn 1 tháng', '6 tháng'),
('Truyện Sách & Tài liệu #3', 'Truyện thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Bản đầy đủ, Giấy đẹp, Không rách, Bìa sạch, Đọc giải trí. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p043.jpg', 5, 2, 260000, 1, 'available', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 1 tháng', '10 tháng'),
('Sách kinh tế Sách & Tài liệu #4', 'Sách kinh tế thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Nội dung hay, Sạch, Không note, Bìa đẹp, In rõ. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p044.jpg', 5, 5, 80000, 1, 'available', 'ít sử dụng', 'tặng kèm ốp/bao', 'Còn 6 tháng', '1 tháng'),
('Giáo trình Sách & Tài liệu #5', 'Giáo trình thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Dùng học, Sạch, Không rách, Có mục lục, In rõ. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p045.jpg', 5, 2, 340000, 0, 'sold', 'dùng kỹ, còn bền', 'có hộp', 'Không bảo hành', '1 năm'),
('Flashcard Sách & Tài liệu #6', 'Flashcard thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Ôn tập, Đầy đủ, Không thiếu, Còn mới, Hộp còn. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p046.jpg', 5, 2, 30000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 12 tháng', '6 tháng'),
('Sách tiếng Anh Sách & Tài liệu #7', 'Sách tiếng Anh thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Có bài tập, Sạch, In rõ, Phù hợp ôn, Bìa đẹp. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p047.jpg', 5, 5, 190000, 1, 'available', 'còn đẹp 95%', 'có hộp', 'Còn 3 tháng', '6 tháng'),
('Sách marketing Sách & Tài liệu #8', 'Sách marketing thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Thực chiến, Sạch, Không note, Bìa đẹp, In rõ. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p048.jpg', 5, 5, 320000, 1, 'pending', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 12 tháng', '10 tháng'),
('Sách tài chính Sách & Tài liệu #9', 'Sách tài chính thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Cơ bản, Dễ hiểu, Sạch, Không rách, In rõ. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p049.jpg', 5, 4, 40000, 0, 'sold', 'mới 90%', 'có hộp', 'Còn 12 tháng', '10 tháng'),
('Sách văn học Sách & Tài liệu #10', 'Sách văn học thuộc danh mục Sách & Tài liệu. Thông số/đặc điểm: Bìa đẹp, Giấy tốt, Sạch, Không note, Đọc hay. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p050.jpg', 5, 3, 40000, 1, 'available', 'còn đẹp 95%', 'có hóa đơn', 'Không bảo hành', '2 tháng'),
('Nồi cơm Đồ gia dụng & Nội thất #1', 'Nồi cơm thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Hoạt động tốt, Lòng nồi còn tốt, Nút bấm ok, Dễ vệ sinh, Cắm điện chạy. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p051.jpg', 6, 5, 2170000, 1, 'available', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 3 tháng', '10 tháng'),
('Máy xay Đồ gia dụng & Nội thất #2', 'Máy xay thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Còn bền, Lưỡi tốt, Xay ổn, Cốc sạch, Đầy phụ kiện. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p052.jpg', 6, 3, 1020000, 0, 'sold', 'còn đẹp 95%', 'tặng kèm ốp/bao', 'Không bảo hành', '1 tháng'),
('Bàn học Đồ gia dụng & Nội thất #3', 'Bàn học thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Gỗ chắc, Ít trầy, Kết cấu vững, Dễ lắp, Kích thước vừa. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p053.jpg', 6, 4, 570000, 1, 'pending', 'ngoại hình 90%', 'không kèm phụ kiện', 'Còn 3 tháng', '2 năm'),
('Ghế Đồ gia dụng & Nội thất #4', 'Ghế thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Êm, Không rách, Chân chắc, Ngồi lâu ổn, Dễ vệ sinh. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p054.jpg', 6, 5, 1070000, 1, 'available', 'dùng kỹ, còn bền', 'đủ phụ kiện', 'Còn 12 tháng', '2 năm'),
('Kệ sách Đồ gia dụng & Nội thất #5', 'Kệ sách thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Dễ lắp, Vững, Ít trầy, Nhiều tầng, Gọn. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p055.jpg', 6, 3, 2620000, 1, 'available', 'ngoại hình 90%', 'có hóa đơn', 'Còn 6 tháng', '5 tháng'),
('Đèn bàn Đồ gia dụng & Nội thất #6', 'Đèn bàn thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Ánh sáng vàng, Tiết kiệm điện, Bóng còn tốt, Chống chói, Đọc sách ok. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p056.jpg', 6, 4, 1920000, 1, 'available', 'dùng kỹ, còn bền', 'đủ phụ kiện', 'Còn 6 tháng', '2 năm'),
('Bộ chăn ga Đồ gia dụng & Nội thất #7', 'Bộ chăn ga thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Sạch, Không rách, Giặt thơm, Màu đẹp, Size chuẩn. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p057.jpg', 6, 4, 1620000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 3 tháng', '1 tháng'),
('Quạt Đồ gia dụng & Nội thất #8', 'Quạt thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Êm, Gió mạnh, Ít rung, Có nhiều mức, Dễ lau. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p058.jpg', 6, 3, 2670000, 1, 'available', 'còn đẹp 95%', 'tặng kèm ốp/bao', 'Còn 6 tháng', '6 tháng'),
('Bếp điện Đồ gia dụng & Nội thất #9', 'Bếp điện thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: An toàn, Nấu nhanh, Mặt kính tốt, Nút ổn, Dây còn. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p059.jpg', 6, 3, 920000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 1 tháng', '1.5 năm'),
('Bộ ly Đồ gia dụng & Nội thất #10', 'Bộ ly thuộc danh mục Đồ gia dụng & Nội thất. Thông số/đặc điểm: Đẹp, Không sứt, Trong, Dùng tốt, Set đủ. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p060.jpg', 6, 4, 1620000, 0, 'sold', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Không bảo hành', '10 tháng'),
('Xe đạp Xe cộ & Phụ tùng #1', 'Xe đạp thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Ít dùng, Bánh còn tốt, Phanh ổn, Sên êm, Khung chắc. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p061.jpg', 7, 2, 500000, 1, 'available', 'dùng kỹ, còn bền', 'tặng kèm ốp/bao', 'Không bảo hành', '4 tháng'),
('Mũ bảo hiểm Xe cộ & Phụ tùng #2', 'Mũ bảo hiểm thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Còn mới, Kính trong, Khóa chắc, Không bể, Size vừa. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p062.jpg', 7, 4, 4350000, 0, 'sold', 'mới 90%', 'có hộp', 'Còn 3 tháng', '3 tháng'),
('Áo mưa Xe cộ & Phụ tùng #3', 'Áo mưa thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Dày, Không rách, Còn mới, Chống thấm, Gấp gọn. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p063.jpg', 7, 2, 4200000, 1, 'available', 'ít sử dụng', 'tặng kèm ốp/bao', 'Còn 1 tháng', '8 tháng'),
('Bơm xe Xe cộ & Phụ tùng #4', 'Bơm xe thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Nhỏ gọn, Bơm nhanh, Còn tốt, Có đầu bơm, Dễ mang. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p064.jpg', 7, 2, 1250000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 6 tháng', '10 tháng'),
('Khoá xe Xe cộ & Phụ tùng #5', 'Khoá xe thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Chắc, Khóa mượt, Chống cắt, Có chìa, Bền. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p065.jpg', 7, 2, 5100000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Không bảo hành', '3 tháng'),
('Găng tay Xe cộ & Phụ tùng #6', 'Găng tay thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Đi phượt, Bền, Ôm tay, Chống trượt, Còn mới. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p066.jpg', 7, 4, 4050000, 1, 'available', 'dùng kỹ, còn bền', 'có hộp', 'Còn 6 tháng', '8 tháng'),
('Đèn xe Xe cộ & Phụ tùng #7', 'Đèn xe thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Sáng, Pin tốt, Nhiều chế độ, Dễ lắp, Chống nước. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p067.jpg', 7, 5, 5300000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Còn 12 tháng', '5 tháng'),
('Giá đỡ điện thoại Xe cộ & Phụ tùng #8', 'Giá đỡ điện thoại thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Dễ lắp, Chắc, Xoay được, Không rung, Hợp xe máy. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p068.jpg', 7, 5, 4850000, 1, 'pending', 'ngoại hình 90%', 'có hộp', 'Còn 1 tháng', '8 tháng'),
('Dầu nhớt Xe cộ & Phụ tùng #9', 'Dầu nhớt thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Chính hãng, Chưa mở, Loại tốt, Bảo quản ok, Giá tốt. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p069.jpg', 7, 4, 1450000, 1, 'available', 'còn đẹp 95%', 'đủ phụ kiện', 'Còn 12 tháng', '5 tháng'),
('Bộ dụng cụ Xe cộ & Phụ tùng #10', 'Bộ dụng cụ thuộc danh mục Xe cộ & Phụ tùng. Thông số/đặc điểm: Đầy đủ, Gọn, Chất lượng, Dễ mang, Bền. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p070.jpg', 7, 3, 3250000, 1, 'available', 'mới 90%', 'không kèm phụ kiện', 'Còn 6 tháng', '6 tháng'),
('Giày chạy Thể thao & Dã ngoại #1', 'Giày chạy thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Êm, Ít mòn, Thoáng, Size chuẩn, Chạy ổn. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p071.jpg', 8, 5, 1360000, 1, 'available', 'ngoại hình 90%', 'có hộp', 'Còn 6 tháng', '4 tháng'),
('Vợt cầu lông Thể thao & Dã ngoại #2', 'Vợt cầu lông thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Nhẹ, Căng dây tốt, Cầm chắc, Đánh ổn, Không nứt. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p072.jpg', 8, 2, 660000, 1, 'available', 'dùng kỹ, còn bền', 'có hóa đơn', 'Còn 1 tháng', '2 tháng'),
('Bóng đá Thể thao & Dã ngoại #3', 'Bóng đá thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Chuẩn, Da tốt, Bơm căng, Ít mòn, Đá ổn. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p073.jpg', 8, 4, 2110000, 1, 'pending', 'mới 90%', 'không kèm phụ kiện', 'Còn 1 tháng', '3 tháng'),
('Thảm yoga Thể thao & Dã ngoại #4', 'Thảm yoga thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Chống trượt, Dày, Không mùi, Dễ vệ sinh, Tặng dây. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p074.jpg', 8, 2, 360000, 1, 'available', 'ít sử dụng', 'đủ phụ kiện', 'Còn 1 tháng', '2 năm'),
('Dumbbell Thể thao & Dã ngoại #5', 'Dumbbell thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Tạ, Bọc cao su, Cầm chắc, Ít tróc, Set đôi. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p075.jpg', 8, 4, 310000, 1, 'pending', 'ngoại hình 90%', 'có hóa đơn', 'Còn 1 tháng', '1 tháng'),
('Balo du lịch Thể thao & Dã ngoại #6', 'Balo du lịch thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Nhiều ngăn, Chống nước, Dây chắc, Gọn, Đi chơi ok. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p076.jpg', 8, 2, 2960000, 1, 'available', 'ít sử dụng', 'tặng kèm ốp/bao', 'Còn 6 tháng', '1.5 năm'),
('Lều Thể thao & Dã ngoại #7', 'Lều thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: 2 người, Dễ dựng, Chống mưa nhẹ, Đầy phụ kiện, Gọn. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p077.jpg', 8, 5, 160000, 1, 'available', 'mới 90%', 'có hộp', 'Còn 1 tháng', '5 tháng'),
('Bình nước Thể thao & Dã ngoại #8', 'Bình nước thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Giữ nhiệt, Không rò, Nắp tốt, Dễ mang, Còn mới. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p078.jpg', 8, 4, 2510000, 1, 'pending', 'mới 90%', 'có hóa đơn', 'Còn 3 tháng', '1 tháng'),
('Áo thể thao Thể thao & Dã ngoại #9', 'Áo thể thao thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Thoáng, Co giãn, Dễ giặt, Mới 90%, Mặc êm. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p079.jpg', 8, 2, 2610000, 1, 'available', 'còn đẹp 95%', 'đủ phụ kiện', 'Còn 1 tháng', '8 tháng'),
('Găng tay gym Thể thao & Dã ngoại #10', 'Găng tay gym thuộc danh mục Thể thao & Dã ngoại. Thông số/đặc điểm: Bền, Chống trượt, Ôm tay, Đệm tốt, Mới. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p080.jpg', 8, 4, 1760000, 1, 'available', 'còn đẹp 95%', 'đủ phụ kiện', 'Còn 12 tháng', '6 tháng'),
('Xe đ�INSERT INTO orders (Order_Code, Buyer_ID, Seller_ID, Product_ID, Total_price, Shipping_address, Status) VALUES
('DH2606260011', 6, 5, 2,  5000000,  '123 Vo Van Ngan, Thu Duc, TP.HCM',        'completed'),
('DH2606260012', 7, 2, 4,  16500000, '45 Nguyen Ai Quoc, Bien Hoa, Dong Nai',   'completed'),
('DH2606260013', 8, 3, 5,  17000000, '78 Tran Hung Dao, Ninh Kieu, Can Tho',    'completed'),
('DH2606260014', 9, 3, 28, 860000,   '12 Le Loi, Tan An, Long An',              'completed'),
('DH2606260015', 10, 3, 39, 1760000, '56 Bach Dang, Hoi An, Quang Nam',         'completed'),
('DH2606260016', 6, 2, 45, 340000,   '90 Nguyen Van Linh, Quan 7, TP.HCM',      'completed'),
('DH2606260017', 7, 4, 49, 40000,    '33 Ba Cu, Vung Tau',                      'completed'),
('DH2606260018', 8, 3, 52, 1020000,  '25 Tran Phu, Nha Trang, Khanh Hoa',       'completed'),
('DH2606260019', 9, 4, 60, 1620000,  '88 Dong Khoi, Ben Tre',                   'completed'),
('DH2606260020', 10, 4, 62, 4350000, '11 Cach Mang Thang 8, Tay Ninh',          'completed'),
('DH2606260021', 6, 2, 87, 390000,   '15 Xuan Huong, Da Lat, Lam Dong',         'completed'),
('DH2606260022', 7, 5, 93, 5200000,  '120 Giai Phong, Ha Noi',                  'completed');.5 năm'),
('Bình sữa Mẹ & Bé #4', 'Bình sữa thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: BPA free, Còn mới, Không ố, Núm tốt, Dễ rửa. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p084.jpg', 9, 4, 840000, 1, 'available', 'mới 90%', 'không kèm phụ kiện', 'Không bảo hành', '10 tháng'),
('Quần áo bé Mẹ & Bé #5', 'Quần áo bé thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Mềm, Không phai, Size chuẩn, Sạch, Ít dùng. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p085.jpg', 9, 3, 990000, 1, 'pending', 'mới 90%', 'đủ phụ kiện', 'Còn 12 tháng', '8 tháng'),
('Sách thiếu nhi Mẹ & Bé #6', 'Sách thiếu nhi thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Hình đẹp, Sạch, Không rách, Bìa cứng, Bé học. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p086.jpg', 9, 5, 1840000, 1, 'available', 'mới 90%', 'tặng kèm ốp/bao', 'Còn 6 tháng', '8 tháng'),
('Gối Mẹ & Bé #7', 'Gối thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Êm, Sạch, Không rách, Mới 90%, Ngủ ngon. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p087.jpg', 9, 2, 390000, 0, 'sold', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 6 tháng', '2 tháng'),
('Tã Mẹ & Bé #8', 'Tã thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Size M, Còn nguyên, Bảo quản tốt, Chưa mở, Date xa. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p088.jpg', 9, 2, 1740000, 1, 'pending', 'dùng kỹ, còn bền', 'có hộp', 'Còn 6 tháng', '2 năm'),
('Máy hâm sữa Mẹ & Bé #9', 'Máy hâm sữa thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Nhanh, Hoạt động tốt, Ít dùng, Dễ vệ sinh, Có dây. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p089.jpg', 9, 2, 2590000, 1, 'pending', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Còn 6 tháng', '1 tháng'),
('Đèn ngủ Mẹ & Bé #10', 'Đèn ngủ thuộc danh mục Mẹ & Bé. Thông số/đặc điểm: Dịu, An toàn, Bóng tốt, Dễ dùng, Còn mới. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p090.jpg', 9, 5, 240000, 1, 'available', 'còn đẹp 95%', 'đủ phụ kiện', 'Còn 1 tháng', '2 tháng'),
('Guitar Nhạc cụ & Âm thanh #1', 'Guitar thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Dễ chơi, Dây còn tốt, Không cong cần, Âm ổn, Kèm bao. Khu vực: Quận 1, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p091.jpg', 10, 5, 6600000, 1, 'pending', 'dùng kỹ, còn bền', 'tặng kèm ốp/bao', 'Còn 1 tháng', '10 tháng'),
('Ukulele Nhạc cụ & Âm thanh #2', 'Ukulele thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Nhỏ gọn, Âm sáng, Dây mới, Còn đẹp, Kèm pick. Khu vực: Quận 7, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p092.jpg', 10, 5, 11400000, 1, 'pending', 'ngoại hình 90%', 'tặng kèm ốp/bao', 'Còn 1 tháng', '2 tháng'),
('Micro Nhạc cụ & Âm thanh #3', 'Micro thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Thu âm, Rõ, Ít nhiễu, Có chân, Dùng tốt. Khu vực: Thủ Đức, TP.HCM. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p093.jpg', 10, 5, 5200000, 0, 'sold', 'ít sử dụng', 'có hộp', 'Còn 3 tháng', '1.5 năm'),
('Tai nghe studio Nhạc cụ & Âm thanh #4', 'Tai nghe studio thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Monitor, Bass tốt, Đệm êm, Dây còn, Nghe chuẩn. Khu vực: Hà Đông, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p094.jpg', 10, 2, 3600000, 1, 'available', 'còn đẹp 95%', 'tặng kèm ốp/bao', 'Không bảo hành', '2 tháng'),
('Amp Nhạc cụ & Âm thanh #5', 'Amp thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Ổn định, Âm sạch, Nút tốt, Không rè, Dùng ok. Khu vực: Cầu Giấy, Hà Nội. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p095.jpg', 10, 2, 6100000, 1, 'available', 'ngoại hình 90%', 'đủ phụ kiện', 'Không bảo hành', '1.5 năm'),
('Loa Nhạc cụ & Âm thanh #6', 'Loa thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Công suất tốt, Âm rõ, Ít trầy, Kết nối ổn, Dùng tốt. Khu vực: Hải Châu, Đà Nẵng. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p096.jpg', 10, 5, 6550000, 1, 'available', 'ít sử dụng', 'tặng kèm ốp/bao', 'Còn 1 tháng', '1 năm'),
('Sound card Nhạc cụ & Âm thanh #7', 'Sound card thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Hỗ trợ livestream, Cắm là chạy, Ít lỗi, Âm ổn, Đủ dây. Khu vực: Ninh Kiều, Cần Thơ. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p097.jpg', 10, 4, 3400000, 1, 'available', 'dùng kỹ, còn bền', 'không kèm phụ kiện', 'Không bảo hành', '6 tháng'),
('Trống cajon Nhạc cụ & Âm thanh #8', 'Trống cajon thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Gỗ, Âm hay, Không nứt, Còn đẹp, Dễ chơi. Khu vực: Biên Hòa, Đồng Nai. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p098.jpg', 10, 5, 850000, 1, 'available', 'mới 90%', 'có hóa đơn', 'Còn 12 tháng', '1 tháng'),
('Stand mic Nhạc cụ & Âm thanh #9', 'Stand mic thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Chắc, Điều chỉnh được, Không rơ, Gọn, Dùng ổn. Khu vực: Dĩ An, Bình Dương. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p099.jpg', 10, 3, 4350000, 1, 'pending', 'mới 90%', 'không kèm phụ kiện', 'Không bảo hành', '4 tháng'),
('Dây đàn Nhạc cụ & Âm thanh #10', 'Dây đàn thuộc danh mục Nhạc cụ & Âm thanh. Thông số/đặc điểm: Mới, Chưa dùng, Chính hãng, Âm tốt, Set đủ. Khu vực: Nha Trang, Khánh Hòa. Liên hệ để xem hàng/kiểm tra trực tiếp.', 'p100.jpg', 10, 3, 5800000, 1, 'available', 'ngoại hình 90%', 'không kèm phụ kiện', 'Còn 12 tháng', '8 tháng');

-- Bước 4: Nạp lại orders, order_details, payments, notifications

INSERT INTO orders (Buyer_ID, Seller_ID, Product_ID, Total_price, Shipping_address, Status) VALUES
(6, 5, 2,  5000000,  '123 Vo Van Ngan, Thu Duc, TP.HCM',        'completed'),
(7, 2, 4,  16500000, '45 Nguyen Ai Quoc, Bien Hoa, Dong Nai',   'completed'),
(8, 3, 5,  17000000, '78 Tran Hung Dao, Ninh Kieu, Can Tho',    'completed'),
(9, 3, 28, 860000,   '12 Le Loi, Tan An, Long An',              'completed'),
(10, 3, 39, 1760000, '56 Bach Dang, Hoi An, Quang Nam',         'completed'),
(6, 2, 45, 340000,   '90 Nguyen Van Linh, Quan 7, TP.HCM',      'completed'),
(7, 4, 49, 40000,    '33 Ba Cu, Vung Tau',                      'completed'),
(8, 3, 52, 1020000,  '25 Tran Phu, Nha Trang, Khanh Hoa',       'completed'),
(9, 4, 60, 1620000,  '88 Dong Khoi, Ben Tre',                   'completed'),
(10, 4, 62, 4350000, '11 Cach Mang Thang 8, Tay Ninh',          'completed'),
(6, 2, 87, 390000,   '15 Xuan Huong, Da Lat, Lam Dong',         'completed'),
(7, 5, 93, 5200000,  '120 Giai Phong, Ha Noi',                  'completed');


INSERT INTO payments (Order_ID, Amount, Payment_method, Status) VALUES
(1,  5000000,  'COD',           'paid'),
(2,  16500000, 'bank_transfer', 'paid'),
(3,  17000000, 'momo',          'paid'),
(4,  860000,   'COD',           'paid'),
(5,  1760000,  'vnpay',         'paid'),
(6,  340000,   'COD',           'paid'),
(7,  40000,    'momo',          'paid'),
(8,  1020000,  'bank_transfer', 'paid'),
(9,  1620000,  'COD',           'paid'),
(10, 4350000,  'vnpay',         'paid'),
(11, 390000,   'COD',           'paid'),
(12, 5200000,  'bank_transfer', 'paid');

INSERT INTO notifications (User_ID, Title, Content, Is_read) VALUES
(6,  'Dat hang thanh cong',   'Don hang #1 da duoc tao thanh cong',   1),
(7,  'Dat hang thanh cong',   'Don hang #2 da duoc tao thanh cong',   1),
(8,  'Dat hang thanh cong',   'Don hang #3 da duoc tao thanh cong',   1),
(9,  'Dat hang thanh cong',   'Don hang #4 da duoc tao thanh cong',   0),
(10, 'Dat hang thanh cong',   'Don hang #5 da duoc tao thanh cong',   0),
(6,  'Thanh toan thanh cong', 'Don hang #6 da thanh toan thanh cong', 1),
(7,  'Thanh toan thanh cong', 'Don hang #7 da thanh toan thanh cong', 1),
(8,  'Thanh toan thanh cong', 'Don hang #8 da thanh toan thanh cong', 0),
(9,  'Thanh toan thanh cong', 'Don hang #9 da thanh toan thanh cong', 0),
(10, 'Thanh toan thanh cong', 'Don hang #10 da thanh toan thanh cong',0),
(6,  'Don hang hoan tat',     'Don hang #11 da giao thanh cong',      1),
(7,  'Don hang hoan tat',     'Don hang #12 da giao thanh cong',      1);

-- Kiểm tra kết quả
SELECT 'products' AS bang, COUNT(*) AS so_hang FROM products;
SELECT 'orders' AS bang, COUNT(*) AS so_hang FROM orders;
SELECT 'payments' AS bang, COUNT(*) AS so_hang FROM payments;
SELECT 'notifications' AS bang, COUNT(*) AS so_hang FROM notifications;
