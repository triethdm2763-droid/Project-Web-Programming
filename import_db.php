<?php

/**
 * Script to automatically set up and seed the database.
 * Dynamically parses the Database configuration to support setup
 * even if the database does not exist yet.
 */

try {
    // Read credentials directly from the config file to avoid the connection error of the Database class if DB doesn't exist
    $dbConfigFile = __DIR__ . '/backend/src/config/Database.php';
    if (!file_exists($dbConfigFile)) {
        throw new Exception("Database config file not found: $dbConfigFile");
    }
    
    $configContent = file_get_contents($dbConfigFile);
    preg_match('/\$host\s*=\s*[\'"]([^\'"]*)[\'"]/', $configContent, $hostMatch);
    preg_match('/\$db_name\s*=\s*[\'"]([^\'"]*)[\'"]/', $configContent, $dbMatch);
    preg_match('/\$username\s*=\s*[\'"]([^\'"]*)[\'"]/', $configContent, $userMatch);
    preg_match('/\$password\s*=\s*[\'"]([^\'"]*)[\'"]/', $configContent, $passMatch);
    
    $host = $hostMatch[1] ?? 'localhost';
    $db_name = $dbMatch[1] ?? 'c2c_used_marketplace';
    $username = $userMatch[1] ?? 'root';
    $password = $passMatch[1] ?? '';

    echo "Connecting to MySQL server at $host...\n";
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Drop old database and recreate a fresh one based on project configuration
    echo "Dropping database if exists: `$db_name`...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$db_name`;");
    
    echo "Creating a fresh database: `$db_name`...\n";
    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$db_name`;");

    // Disable Foreign Key Checks temporarily for resetting database tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Import Schema
    $schemaFile = __DIR__ . '/database/001_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    echo "Importing schema from 001_schema.sql...\n";
    executeSqlFile($pdo, $schemaFile);

    // Import Seeds
    $seedFile = __DIR__ . '/database/002_seed.sql';
    if (!file_exists($seedFile)) {
        throw new Exception("Seed file not found: $seedFile");
    }

    echo "Importing seed data from 002_seed.sql...\n";
    executeSqlFile($pdo, $seedFile);

    echo "\nDatabase standard setup and seed completed successfully!\n";
} catch (Exception $e) {
    echo "\nError during database setup:\n" . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($pdo)) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
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

    // Split queries by semicolon, ignoring semicolons inside strings
    $queries = preg_split('/;(?=(?:[^\'"]*[\'"][^\'"]*[\'"])*[^\'"]*$)/', $sql);
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        try {
            echo "Running: " . substr($query, 0, 50) . "...\n";
            $pdo->exec($query);
        } catch (PDOException $e) {
            // Check if error is because of dropping database/tables which is normal on first run
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                echo "Warning or error executing query: " . substr($query, 0, 100) . "...\nError: " . $e->getMessage() . "\n";
            }
        }
    }
}