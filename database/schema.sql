
CREATE DATABASE IF NOT EXISTS `c2c_used_marketplace`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `c2c_used_marketplace`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. BẢNG DANH MỤC SẢN PHẨM (CATEGORIES)
CREATE TABLE `categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG TÀI KHOẢN NGƯỜI DÙNG (USERS)
CREATE TABLE `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(10) DEFAULT NULL,
  `Fullname` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Avatar` text DEFAULT NULL,
  `Role` varchar(100) NOT NULL DEFAULT 'user',
  `Status` varchar(100) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`Username`),
  UNIQUE KEY `email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG SẢN PHẨM THANH LÝ (PRODUCTS)
CREATE TABLE `products` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Image` text DEFAULT NULL,
  `Category_ID` int(11) NOT NULL,
  `Seller_ID` int(11) NOT NULL,
  `Price` decimal(15,2) NOT NULL,
  `Stock_quantity` int(11) NOT NULL DEFAULT 1,
  `Status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `fk_product_category` (`Category_ID`),
  KEY `fk_product_seller` (`Seller_ID`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`ID`),
  CONSTRAINT `fk_product_seller` FOREIGN KEY (`Seller_ID`) REFERENCES `users` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG ĐƠN HÀNG (ORDERS)
CREATE TABLE `orders` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Buyer_ID` int(11) NOT NULL,
  `Seller_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL,
  `Total_price` decimal(15,2) NOT NULL,
  `Shipping_address` text NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `fk_order_buyer` (`Buyer_ID`),
  KEY `fk_order_seller` (`Seller_ID`),
  KEY `fk_order_product` (`Product_ID`),
  CONSTRAINT `fk_order_buyer` FOREIGN KEY (`Buyer_ID`) REFERENCES `users` (`ID`),
  CONSTRAINT `fk_order_seller` FOREIGN KEY (`Seller_ID`) REFERENCES `users` (`ID`),
  CONSTRAINT `fk_order_product` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG THANH TOÁN (PAYMENTS)
CREATE TABLE `payments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Order_ID` int(11) NOT NULL,
  `Amount` decimal(15,2) NOT NULL,
  `Payment_method` varchar(50) NOT NULL DEFAULT 'COD',
  `Status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `fk_payment_order` (`Order_ID`),
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BẢNG THÔNG BÁO (NOTIFICATIONS)
CREATE TABLE `notifications` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `fk_notification_user` (`User_ID`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`User_ID`) REFERENCES `users` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
