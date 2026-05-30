START TRANSACTION;

-- 1. BẢNG ĐƠN HÀNG (ORDERS)
CREATE TABLE `orders` (
  `ID` int(11) NOT NULL,
  `Buyer_ID` int(11) NOT NULL,
  `Seller_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Total_price` decimal(15,0) NOT NULL,
  `Shipping_address` text NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG THANH TOÁN (PAYMENTS)
CREATE TABLE `payments` (
  `ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Amount` decimal(15,0) NOT NULL,
  `Payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `Status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG THÔNG BÁO (NOTIFICATIONS)
CREATE TABLE `notifications` (
  `ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- THIẾT LẬP KHÓA CHÍNH VÀ CÁC CHỈ MỤC
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_order_buyer` (`Buyer_ID`),
  ADD KEY `fk_order_seller` (`Seller_ID`),
  ADD KEY `fk_order_product` (`Product_ID`);

ALTER TABLE `payments`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_payment_order` (`Order_ID`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_notification_user` (`User_ID`);

-- THIẾT LẬP TỰ ĐỘNG TĂNG ID (AUTO_INCREMENT)
ALTER TABLE `orders`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `payments`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

-- THIẾT LẬP RÀNG BUỘC KHÓA NGOẠI (FOREIGN KEYS)
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_buyer` FOREIGN KEY (`Buyer_ID`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `fk_order_seller` FOREIGN KEY (`Seller_ID`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`ID`);

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`ID`);

ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`User_ID`) REFERENCES `users` (`ID`);

COMMIT;
