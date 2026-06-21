<?php

namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class ProductRepository extends BaseRepository
{
    public function findAllActive(array $filters = []): array {
        $sql = "SELECT p.*, c.Name as CategoryName, u.Username as SellerName,
                (SELECT o.Shipping_address FROM orders o WHERE o.Product_ID = p.ID ORDER BY o.created_at DESC LIMIT 1) AS Location
                FROM `products` p
                JOIN `categories` c ON p.Category_ID = c.ID
                JOIN `users` u ON p.Seller_ID = u.ID
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
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.Price >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.Price <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }

        $sort = $filters['sort'] ?? 'newest';
        $sql .= ($sort === 'price_asc') ? " ORDER BY p.Price ASC" : 
                (($sort === 'price_desc') ? " ORDER BY p.Price DESC" : " ORDER BY p.created_at DESC");

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO `products` (`Name`, `Description`, `Image`, `Category_ID`, `Seller_ID`, `Price`, `Stock_quantity`, `Status`, `Condition_status`, `Accessories`, `Warranty`, `Used_duration`) 
                VALUES (:name, :description, :image, :category_id, :seller_id, :price, :stock_quantity, :status, :condition_status, :accessories, :warranty, :used_duration)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'image'            => $data['image'] ?? null,
            'category_id'      => (int)$data['category_id'],
            'seller_id'        => (int)$data['seller_id'],
            'price'            => $data['price'],
            'stock_quantity'   => $data['stock_quantity'] ?? 1,
            'status'           => $data['status'] ?? 'pending',
            'condition_status' => $data['condition_status'] ?? '',
            'accessories'      => $data['accessories'] ?? '',
            'warranty'         => $data['warranty'] ?? 'Không bảo hành',
            'used_duration'    => $data['used_duration'] ?? ''
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $map = [
            'name' => 'Name', 'description' => 'Description', 'image' => 'Image', 
            'category_id' => 'Category_ID', 'price' => 'Price', 
            'condition_status' => 'Condition_status', 'accessories' => 'Accessories', 
            'warranty' => 'Warranty', 'used_duration' => 'Used_duration'
        ];

        foreach ($map as $key => $column) {
            if (array_key_exists($key, $data)) {
                $fields[] = "`{$column}` = :{$key}";
                $params[$key] = $data[$key];
            }
        }
        
        if (empty($fields)) return false;
        
        $sql = "UPDATE `products` SET " . implode(', ', $fields) . ", `updated_at` = CURRENT_TIMESTAMP WHERE `ID` = :id";
        return $this->db->prepare($sql)->execute($params);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE `products` SET `Status` = :status WHERE `ID` = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT p.*, c.Name as CategoryName FROM `products` p JOIN `categories` c ON p.Category_ID = c.ID WHERE p.ID = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}