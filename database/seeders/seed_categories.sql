USE c2c_used_marketplace;

-- Bước 1: Tạm thời tắt kiểm tra khóa ngoại để dọn dẹp không bị lỗi ràng buộc
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `categories`;

SET FOREIGN_KEY_CHECKS = 1;

-- Bước 2: Nạp lại bảng categories với cấu trúc gán cứng ID chuẩn chỉnh
INSERT INTO
    categories (ID, Name, Icon)
VALUES (
        1,
        'Điện tử & Công nghệ',
        'laptop_mac'
    ),
    (
        2,
        'Điện thoại & Máy tính bảng',
        'smartphone'
    ),
    (
        3,
        'Thời trang Nam',
        'checkroom'
    ),
    (4, 'Thời trang Nữ', 'apparel'),
    (
        5,
        'Sách & Tài liệu',
        'menu_book'
    ),
    (
        6,
        'Đồ gia dụng & Nội thất',
        'chair'
    ),
    (
        7,
        'Xe cộ & Phụ tùng',
        'directions_car'
    ),
    (
        8,
        'Thể thao & Dã ngoại',
        'sports_soccer'
    ),
    (9, 'Mẹ & Bé', 'stroller'),
    (
        10,
        'Nhạc cụ & Âm thanh',
        'music_note'
    );

ON DUPLICATE KEY UPDATE id = id;