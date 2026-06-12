<?php
/**
 * Diagnostic script to check for logical errors, data integrity issues,
 * and inconsistencies in the c2c_used_marketplace database.
 */

require_once __DIR__ . '/../backend/src/config/Database.php';

use App\Config\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "==================================================\n";
    echo "DATABASE DIAGNOSTIC REPORT FOR c2c_used_marketplace\n";
    echo "==================================================\n\n";

    // 1. Check tables list
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    echo "[+] Tables in Database: " . implode(', ', $tables) . "\n\n";

    // 2. Table row counts
    echo "[+] Table row counts:\n";
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $countStmt->fetchColumn();
        echo "  - $table: $count rows\n";
    }
    echo "\n";

    // 3. Check for orphan records (Data Integrity)
    echo "[+] Data Integrity (Orphan records check):\n";

    // products -> categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.Category_ID = c.ID WHERE c.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Products with invalid/missing Category_ID: $orphans\n";

    // products -> users (sellers)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products p LEFT JOIN users u ON p.Seller_ID = u.ID WHERE u.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Products with invalid/missing Seller_ID: $orphans\n";

    // orders -> users (buyers)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.Buyer_ID = u.ID WHERE u.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Orders with invalid/missing Buyer_ID: $orphans\n";

    // orders -> users (sellers)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.Seller_ID = u.ID WHERE u.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Orders with invalid/missing Seller_ID: $orphans\n";

    // orders -> products
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders o LEFT JOIN products p ON o.Product_ID = p.ID WHERE p.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Orders with invalid/missing Product_ID: $orphans\n";

    // payments -> orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM payments p LEFT JOIN orders o ON p.Order_ID = o.ID WHERE o.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Payments with invalid/missing Order_ID: $orphans\n";

    // notifications -> users
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications n LEFT JOIN users u ON n.User_ID = u.ID WHERE u.ID IS NULL");
    $orphans = $stmt->fetchColumn();
    echo "  - Notifications with invalid/missing User_ID: $orphans\n\n";

    // 4. Business Logic Validation Checks
    echo "[+] Business Logic Validation:\n";

    // Check if buyer is also the seller of the product (Self-purchase error)
    $stmt = $pdo->query("SELECT ID, Buyer_ID, Seller_ID, Product_ID FROM orders WHERE Buyer_ID = Seller_ID");
    $selfPurchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  - Orders where Buyer_ID is the same as Seller_ID (Self-purchase): " . count($selfPurchases) . "\n";
    foreach ($selfPurchases as $sp) {
        echo "    * Order ID {$sp['ID']}: Buyer {$sp['Buyer_ID']} bought from Seller {$sp['Seller_ID']} (Product {$sp['Product_ID']})\n";
    }

    // Check if the order's Seller_ID does not match the product's actual Seller_ID
    $stmt = $pdo->query("SELECT o.ID as OrderID, o.Seller_ID as OrderSeller, p.ID as ProductID, p.Seller_ID as ProductSeller 
                         FROM orders o 
                         JOIN products p ON o.Product_ID = p.ID 
                         WHERE o.Seller_ID != p.Seller_ID");
    $mismatchedSellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  - Orders where Seller_ID does not match the product's owner: " . count($mismatchedSellers) . "\n";
    foreach ($mismatchedSellers as $ms) {
        echo "    * Order ID {$ms['OrderID']}: Order seller is {$ms['OrderSeller']} but Product {$ms['ProductID']} owner is {$ms['ProductSeller']}\n";
    }

    // Check if there are multiple orders for the same product, but the product's stock_quantity is 1 or status is sold
    // (Meaning double selling of a single product)
    $stmt = $pdo->query("SELECT Product_ID, COUNT(*) as OrderCount FROM orders GROUP BY Product_ID HAVING OrderCount > 1");
    $multipleOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  - Products sold multiple times: " . count($multipleOrders) . "\n";
    foreach ($multipleOrders as $mo) {
        echo "    * Product ID {$mo['Product_ID']} has {$mo['OrderCount']} orders associated with it.\n";
    }
    echo "\n";

    // 5. Distinct Status Values Check
    echo "[+] Distinct Status Values in Database:\n";
    
    // Users status
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM users GROUP BY Status");
    echo "  - User Statuses:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    * '{$row['Status']}': {$row['count']} users\n";
    }

    // Products status
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM products GROUP BY Status");
    echo "  - Product Statuses:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    * '{$row['Status']}': {$row['count']} products\n";
    }

    // Orders status
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM orders GROUP BY Status");
    echo "  - Order Statuses:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    * '{$row['Status']}': {$row['count']} orders\n";
    }

    // Payments status
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM payments GROUP BY Status");
    echo "  - Payment Statuses:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    * '{$row['Status']}': {$row['count']} payments\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "Error running diagnostics: " . $e->getMessage() . "\n";
}
