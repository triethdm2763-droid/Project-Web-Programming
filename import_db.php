@@ -0,0 +1,68 @@
<?php

/**
 * Script to automatically set up and seed the database.
 * Uses the Database PDO configuration.
 */
require_once __DIR__ . '/backend/src/config/Database.php';

use App\Config\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable Foreign Key Checks temporarily for resetting database tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Import Schema
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    echo "Importing schema from schema.sql...\n";
    executeSqlFile($pdo, $schemaFile);

    // Import Seeds
    $seedFile = __DIR__ . '/database/seed.sql';
    if (!file_exists($seedFile)) {
        throw new Exception("Seed file not found: $seedFile");
    }

    echo "Importing seed data from seed.sql...\n";
    executeSqlFile($pdo, $seedFile);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\nDatabase standard setup and seed completed successfully!\n";
} catch (Exception $e) {
    echo "\nError during database setup:\n" . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Executes multi-statement SQL files.
 */
function executeSqlFile(PDO $pdo, string $filePath)
{
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        throw new Exception("Failed to read file: $filePath");
    }

    // Simple command splitter (by semicolon)
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (empty($query)) continue;
        try {
            echo "Running: " . substr(trim($query), 0, 50) . "...\n";
            $pdo->exec($query);
        } catch (PDOException $e) {
            // Check if error is because of dropping database/tables which is normal on first run
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                echo "Warning or error executing query: " . substr(trim($query), 0, 100) . "...\nError: " . $e->getMessage() . "\n";
            }
        }
    }
}