USE `c2c_used_marketplace`;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `notifications`;
DELETE FROM `payments`;
DELETE FROM `orders`;
DELETE FROM `products`;
DELETE FROM `categories`;
DELETE FROM `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. SEED DỮ LIỆU BẢNG TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- Mật khẩu tương ứng:
-- admin   -> admin123
-- seller1 -> seller123
-- buyer1  -> buyer123
INSERT INTO `users` (`ID`, `Username`, `Password`, `Email`, `Phone`, `Role`, `Status`) VALUES
(1, 'admin', '$2y$10$XIDmP/3ONYEc/ddvGUD8I.u1XtmWhSLrQWdhb4lanR.DkCsO1MjgS', 'admin@chocu.vn', '0912345678', 'admin', 'active'),
(2, 'seller1', '$2y$10$PB7Yt1/Pw9L.axYLLU8ZDeZq6at1R3/opE0n6oyAnwNqi5ZnlrGwO', 'seller1@chocu.vn', '0987654321', 'seller', 'active'),
(3, 'buyer1', '$2y$10$Fria1412E7xlZ9ruS/oOjuXMGgz/3fKk5ojoQiYv3mAs4E47TSxzW', 'buyer1@gmail.com', '0901234567', 'user', 'active');

-- 2. SEED DỮ LIỆU BẢNG DANH MỤC (CATEGORIES)
INSERT INTO `categories` (`ID`, `Name`) VALUES
(1, 'Thiết bị số & Máy ảnh'),
(2, 'Thời trang & Phụ kiện'),
(3, 'Đồ gia dụng & Đời sống'),
(4, 'Phụ kiện thể thao & Dã ngoại');

-- 3. SEED DỮ LIỆU BẢNG SẢN PHẨM THANH LÝ (PRODUCTS)
INSERT INTO `products` (`ID`, `Name`, `Description`, `Image`, `Category_ID`, `Seller_ID`, `Price`, `Stock_quantity`, `Status`) VALUES
(1, 'Máy ảnh Fujifilm X-T30 II kèm ống kính 18-55mm f/2.8-4 R LM OIS', 'Máy ảnh Fujifilm X-T30 II kèm ống kính kit 18-55mm ngoại hình đẹp 99%, hoạt động hoàn hảo không một lỗi nhỏ. Sensor sạch sẽ không trầy xước, số shot đã chụp khoảng 3000 shot. Đầy đủ phụ kiện pin sạc zin kèm theo hộp.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBvzpouZValUbG51LPqZJvCRQM1dQ5WI5FqEqB8it3JWawAaTOtWYxQSiWOu0_ObQrIsfz_ZFVGeJHH9vQmZzO0coRsdL6PBV_HTgFhCRb1gNF6T0mq2tp7-brTMHs4HsRBY8C0PrRXVpZS98r6Gb6shYHFV6D6-TtcNaBkasGzrarJKgcY_KyUyd-YdP4pB3EzqZLtZmzYwqHCxGSyXU5wnLOffKy_CuqQZswBDGrqDQ8krt1PqUCpELd8z6g606L0y7QDXpc2peg', 1, 2, 18500000, 1, 'active'),
(2, 'Sony Alpha A6400 Body - Ngoại hình đẹp 95% nguyên bản chính chủ', 'Cần pass lại Sony Alpha A6400 Body hoạt động tốt, sensor đẹp, ngoại hình xước dăm nhẹ dưới đáy không đáng kể. Đi kèm 2 pin (1 zin 1 wasabi) và đốc sạc đôi. Máy chụp khoảng 12.000 shot chuyên dùng quay vlog cá nhân.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDfmuYUFI1o3GCzWvm_5nRPqm3uA7vxePXGA3wvp7b16bsu_Qx3tr_DW0XkKQ076dI3d9zrUPRhecmQfsisZEJgEL0i_AyCZkyhT4jjWMRAXXLmQKtiNLeTf-nOWQv-KbbaocjmJZ6xS-6FFVafMJPY5p_3jrjGK-oPjaAMkxm9_MA4GuXAOqAkNeSea4EicnXf5uB-yiRw8u9rSUujlRkCt7r11AoeF2syaIGbKkqvu45tVyBSEsd9NZ7XL6cBVhKq9Pqkj1GZQRY', 1, 2, 14200000, 1, 'active'),
(3, 'Canon EOS RP Mirrorless - Fullframe giá rẻ, máy hoạt động mượt mà', 'Canon EOS RP máy hoạt động mượt mà ổn định, dòng Fullframe giá rẻ cho ae tập chơi. Máy nguyên bản chưa qua sửa chữa, mất nắp che cao su cổng mic (bệnh chung của dòng này) nhưng không ảnh hưởng đến trải nghiệm.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBJcAGGYi5TctiPeBuwxbNRUDV3cUZfKykjh0fBlNjj5Rnj6VvusN9S2A0EMwwHPl6izYzOGQXTyxTSDpPTjxNuR0C8K4NBYWFzru8wnLfaaMsvSWVotU3fqAlxx33vhZZeEyPfA3KFo5J1N5TZ9wZdQaymPOfHlye3gR7OH-suUtgpr84aN0vkf4RtZN4ECVMzna8jDeyfJVmv2HvzAUAlCSm-XMjUW3p-8SOW2jLIIrU3tjeQsl-U9Ia-OrkZf3QDnvOZsap6jSU', 1, 2, 16800000, 1, 'active'),
(4, 'Nikon Z fc Silver Edition - Phong cách Retro cổ điển thời thượng', 'Bán máy ảnh Nikon Z fc màu bạc retro cực đẹp. Hàng chính hãng VIC đã hết bảo hành, ngoại hình like new vì cực ít dùng, chủ yếu cất tủ chống ẩm. Tặng kèm bao da màu nâu và thẻ nhớ 64GB tốc độ cao.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB1xrb0AgcwPMso-5WHozKBSmthBO7Z1cmmuhowZFdU-gXwT8ilTR5jf0rC9hH04xN_Dih0E1wNNwlCt_QpXDVngxroVdWwAWkKXWoQ55Kd9WacQJ78VbX-aC4-Gq7V3hiDQxi07JZYIOxFxUfv-qcLcytCfs6wC0XvqN9LkIH-MksEjvRGuN7OwBhCk_tct0GjNZCbalS0b_daWHi-voOtPNN5MXdxnt3brSBNQspjGQI578-ZNeWv3gB01UQwBKK761-djbD10lk', 1, 2, 17500000, 1, 'pending');

-- 4. SEED DỮ LIỆU BẢNG ĐƠN HÀNG (ORDERS)
-- (Đơn hàng 1: Buyer1 mua Sony A6400 của Seller1 và đã hoàn thành)
INSERT INTO `orders` (`ID`, `Buyer_ID`, `Seller_ID`, `Product_ID`, `Total_price`, `Shipping_address`, `Status`) VALUES
(1, 3, 2, 2, 14200000, '123 Đường Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh', 'completed');

-- Cập nhật trạng thái sản phẩm số 2 sang 'sold' (đã bán) vì đơn hàng đã hoàn thành
UPDATE `products` SET `Status` = 'sold' WHERE `ID` = 2;

-- 5. SEED DỮ LIỆU BẢNG THANH TOÁN (PAYMENTS)
-- (Thanh toán cho đơn hàng 1 qua chuyển khoản thành công)
INSERT INTO `payments` (`ID`, `Order_ID`, `Amount`, `Payment_method`, `Status`) VALUES
(1, 1, 14200000, 'Bank_Transfer', 'success');

-- 6. SEED DỮ LIỆU BẢNG THÔNG BÁO (NOTIFICATIONS)
INSERT INTO `notifications` (`ID`, `User_ID`, `Title`, `Content`, `Is_read`) VALUES
(1, 2, 'Sản phẩm đã được duyệt', 'Sản phẩm Fujifilm X-T30 II của bạn đã được quản trị viên duyệt và hiển thị trên sàn.', 1),
(2, 2, 'Bạn có đơn hàng mới!', 'Người dùng buyer1 đã mua sản phẩm Sony Alpha A6400 của bạn. Vui lòng chuẩn bị hàng và giao đi.', 0),
(3, 3, 'Đặt mua hàng thành công', 'Đơn hàng mua máy ảnh Sony Alpha A6400 của bạn đã được thanh toán thành công và đang được vận chuyển.', 1);
