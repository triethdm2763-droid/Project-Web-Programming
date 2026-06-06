-- Wrapper to (re)seed the database safely for local dev
-- This file will:
-- 1. Disable foreign key checks
-- 2. Truncate dependent tables
-- 3. Source seed SQL files in order
-- 4. Re-enable foreign key checks

USE c2c_used_marketplace;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM orders;
DELETE FROM payments;
DELETE FROM notifications;
DELETE FROM products;
DELETE FROM categories;
DELETE FROM users;

-- Import seed files (paths are relative to this file when run from project root)
SOURCE ./database/seeders/seed_users.sql;
SOURCE ./database/seeders/seed_categories.sql;
SOURCE ./database/seeders/seed_products.sql;
SOURCE ./database/seeders/seed_orders.sql;

SET FOREIGN_KEY_CHECKS = 1;

-- Quick summary counts
SELECT 'users' AS tbl, COUNT(*) AS cnt FROM users;
SELECT 'products' AS tbl, COUNT(*) AS cnt FROM products;
SELECT 'orders' AS tbl, COUNT(*) AS cnt FROM orders;
