<?php
/**
 * Test SQL scripts for query optimization and transactions to ensure no syntax/runtime errors.
 */

require_once __DIR__ . '/../backend/src/config/Database.php';

use App\Config\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Testing query_optimization.sql...\n";
    $optSql = file_get_contents(__DIR__ . '/../database/transactions/query_optimization.sql');
    executeSqlContents($pdo, $optSql);
    echo "✔ query_optimization.sql runs successfully.\n\n";

    echo "Testing transaction_buy_product.sql...\n";
    $transSql = file_get_contents(__DIR__ . '/../database/transactions/transaction_buy_product.sql');
    executeSqlContents($pdo, $transSql);
    echo "✔ transaction_buy_product.sql runs successfully.\n\n";

} catch (Exception $e) {
    echo "\n❌ Error executing SQL script:\n" . $e->getMessage() . "\n";
    exit(1);
}

function executeSqlContents(PDO $pdo, string $sql) {
    // Simple command splitter (by semicolon)
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (empty($query)) continue;
        // Strip SQL comments
        $cleanQuery = preg_replace('/^--.*$/m', '', $query);
        $cleanQuery = trim($cleanQuery);
        if (empty($cleanQuery)) continue;

        try {
            if (stripos($cleanQuery, 'select') === 0 || stripos($cleanQuery, 'explain') === 0) {
                $stmt = $pdo->query($cleanQuery);
                $stmt->fetchAll();
                $stmt->closeCursor();
            } else {
                $pdo->exec($cleanQuery);
            }
        } catch (PDOException $e) {
            // Re-throw with original context
            throw new Exception("Query failed: " . substr(trim($query), 0, 100) . "...\nReason: " . $e->getMessage());
        }
    }
}
