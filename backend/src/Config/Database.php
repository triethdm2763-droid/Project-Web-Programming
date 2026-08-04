<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    // Database connection parameters
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $ssl_ca;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->loadConfig();

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            // PDO configuration options for security and error handling
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Disabling emulation prevents SQL Injection in older mysql versions
            ];

            if (!empty($this->ssl_ca) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $this->ssl_ca;
            }

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Send JSON error and halt execution if DB connection fails
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Database connection failed: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    private function loadConfig(): void {
        $url = getenv('DATABASE_URL') ?: getenv('MYSQL_URI') ?: '';

        if ($url) {
            $parts = parse_url($url);
            $this->host = $parts['host'] ?? '127.0.0.1';
            $this->port = (string)($parts['port'] ?? 3306);
            $this->username = isset($parts['user']) ? urldecode($parts['user']) : 'root';
            $this->password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
            $this->db_name = !empty($parts['path']) ? ltrim($parts['path'], '/') : 'c2c_used_marketplace';
        } else {
            $this->host = getenv('DB_HOST') ?: '127.0.0.1';
            $this->port = getenv('DB_PORT') ?: '3306';
            $this->db_name = getenv('DB_NAME') ?: 'c2c_used_marketplace';
            $this->username = getenv('DB_USER') ?: 'root';
            $this->password = getenv('DB_PASS') ?: ''; // XAMPP default is empty string
        }

        $this->ssl_ca = getenv('DB_SSL_CA') ?: '';
    }

    // Get the database instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get the PDO connection object
    public function getConnection() {
        return $this->conn;
    }
}
