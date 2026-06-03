USE c2c_used_marketplace;

-- Bước 1: Tạm thời tắt kiểm tra khóa ngoại để dọn dẹp không bị lỗi ràng buộc
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `categories`;
SET FOREIGN_KEY_CHECKS = 1;

-- Bước 2: Nạp lại bảng categories với cấu trúc gán cứng ID chuẩn chỉnh
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Điện tử & Công nghệ'),
(2, 'Điện thoại & Máy tính bảng'),
(3, 'Thời trang Nam'),
(4, 'Thời trang Nữ'),
(5, 'Sách & Tài liệu'),
(6, 'Đồ gia dụng & Nội thất'),
(7, 'Xe cộ & Phụ tùng'),
(8, 'Thể thao & Dã ngoại'),
(9, 'Mẹ & Bé'),
(10, 'Nhạc cụ & Âm thanh')
ON DUPLICATE KEY UPDATE id=id;