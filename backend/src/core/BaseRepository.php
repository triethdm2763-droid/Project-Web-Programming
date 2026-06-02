<?php
namespace App\Core;

use App\Config\Database;

class BaseRepository {
    /**
     * @var \PDO Connection instance to the database
     */
    protected $db;

    public function __construct() {
        // Retrieve singleton database connection
        $this->db = Database::getInstance()->getConnection();
    }
}
