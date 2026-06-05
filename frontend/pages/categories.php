<?php
namespace App\Api\Products;

use App\Config\Database;

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $query = "SELECT ID, Name FROM categories ORDER BY Name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $categories = $stmt->fetchAll();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $categories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi lấy danh mục: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
