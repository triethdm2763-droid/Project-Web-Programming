<?php
namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class ProductRepository extends BaseRepository {

    /**
     * Find active products with filters (category, search keyword)
     * 
     * @param array $filters
     * @return array
     */
    public function findAllActive(array $filters = []): array {
    $sql = "SELECT p.*, c.Name as CategoryName, u.Username as SellerName,
        (SELECT o.Shipping_address FROM orders o WHERE o.Product_ID = p.ID ORDER BY o.created_at DESC LIMIT 1) AS Location
        FROM `products` p
        JOIN `categories` c ON p.Category_ID = c.ID
        JOIN `users` u ON p.Seller_ID = u.ID
        -- Treat both 'active' and legacy 'available' as visible
        WHERE p.Status IN ('active', 'available')";
        
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.Category_ID = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.Name LIKE :search OR p.Description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a product by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id) {
    $sql = "SELECT p.*, c.Name as CategoryName, u.Username as SellerName, u.Email as SellerEmail, u.Phone as SellerPhone,
        (SELECT o.Shipping_address FROM orders o WHERE o.Product_ID = p.ID ORDER BY o.created_at DESC LIMIT 1) AS Location
        FROM `products` p
        JOIN `categories` c ON p.Category_ID = c.ID
        JOIN `users` u ON p.Seller_ID = u.ID
        WHERE p.ID = :id LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Find a product by ID and lock the row (For transaction concurrency control)
     * 
     * @param int $id
     * @return array|null
     */
    public function findByIdForUpdate(int $id) {
        $sql = "SELECT * FROM `products` WHERE `ID` = :id LIMIT 1 FOR UPDATE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Create a new product listing
     * 
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $sql = "INSERT INTO `products` (`Name`, `Description`, `Image`, `Category_ID`, `Seller_ID`, `Price`, `Stock_quantity`, `Status`) 
                VALUES (:name, :description, :image, :category_id, :seller_id, :price, :stock_quantity, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'image'          => $data['image'] ?? null,
            'category_id'    => (int)$data['category_id'],
            'seller_id'      => (int)$data['seller_id'],
            'price'          => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 1,
            'status'         => $data['status'] ?? 'pending'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update product status
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE `products` SET `Status` = :status WHERE `ID` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'id'     => $id
        ]);
    }
}
