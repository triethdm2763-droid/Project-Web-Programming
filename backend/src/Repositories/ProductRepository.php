<?php

namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class ProductRepository extends BaseRepository
{
    public function countAllActive(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM `products` p
                JOIN `categories` c ON p.Category_ID = c.ID
                JOIN `users` u ON p.Seller_ID = u.ID
                WHERE p.Status IN ('active', 'available')";

        $params = [];
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.Category_ID = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (p.Name LIKE :search_name OR p.Description LIKE :search_desc OR u.Username LIKE :search_seller)";
            $params['search_name'] = '%' . $filters['search'] . '%';
            $params['search_desc'] = '%' . $filters['search'] . '%';
            $params['search_seller'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.Price >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.Price <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND p.Description LIKE :location_filter";
            $params['location_filter'] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['condition_status'])) {
            if ($filters['condition_status'] === 'Mới') {
                $sql .= " AND p.Condition_status = 'Mới'";
            } elseif ($filters['condition_status'] === '99%') {
                $sql .= " AND (p.Condition_status LIKE '%99%' OR p.Condition_status LIKE '%like new%')";
            } elseif ($filters['condition_status'] === 'Đã sử dụng') {
                $sql .= " AND p.Condition_status != 'Mới' AND p.Condition_status NOT LIKE '%99%' AND p.Condition_status NOT LIKE '%like new%'";
            } else {
                $sql .= " AND p.Condition_status LIKE :condition_status";
                $params['condition_status'] = '%' . $filters['condition_status'] . '%';
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

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
            $sql .= " AND (p.Name LIKE :search_name OR p.Description LIKE :search_desc OR u.Username LIKE :search_seller)";
            $params['search_name'] = '%' . $filters['search'] . '%';
            $params['search_desc'] = '%' . $filters['search'] . '%';
            $params['search_seller'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.Price >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.Price <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }
        if (!empty($filters['location'])) {
            $sql .= " AND p.Description LIKE :location_filter";
            $params['location_filter'] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['condition_status'])) {
            if ($filters['condition_status'] === 'Mới') {
                $sql .= " AND p.Condition_status = 'Mới'";
            } elseif ($filters['condition_status'] === '99%') {
                $sql .= " AND (p.Condition_status LIKE '%99%' OR p.Condition_status LIKE '%like new%')";
            } elseif ($filters['condition_status'] === 'Đã sử dụng') {
                $sql .= " AND p.Condition_status != 'Mới' AND p.Condition_status NOT LIKE '%99%' AND p.Condition_status NOT LIKE '%like new%'";
            } else {
                $sql .= " AND p.Condition_status LIKE :condition_status";
                $params['condition_status'] = '%' . $filters['condition_status'] . '%';
            }
        }

        $sort = $filters['sort'] ?? 'newest';
        $sql .= ($sort === 'price_asc') ? " ORDER BY p.Price ASC" : 
                (($sort === 'price_desc') ? " ORDER BY p.Price DESC" : " ORDER BY p.created_at DESC");

        if (isset($filters['limit']) && is_numeric($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = isset($filters['offset']) && is_numeric($filters['offset']) ? (int)$filters['offset'] : 0;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveProducts(array $filters = []): array {
        return $this->findAllActive($filters);
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
            'warranty' => 'Warranty', 'used_duration' => 'Used_duration',
            'stock_quantity' => 'Stock_quantity', 'status' => 'Status'
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
        $stmt = $this->db->prepare("SELECT p.*, c.Name as CategoryName, u.Username as SellerName, u.Phone as SellerPhone, u.Email as SellerEmail, u.Avatar as SellerAvatar 
                                    FROM `products` p 
                                    JOIN `categories` c ON p.Category_ID = c.ID 
                                    JOIN `users` u ON p.Seller_ID = u.ID 
                                    WHERE p.ID = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findSellerProducts(int $sellerId, ?string $status = null): array {
        $sql = "SELECT p.*, c.Name as CategoryName FROM `products` p
                JOIN `categories` c ON p.Category_ID = c.ID
                WHERE p.Seller_ID = :seller_id AND p.Status != 'deleted'";
        $params = ['seller_id' => $sellerId];
        if ($status !== null) {
            if ($status === 'available') {
                $sql .= " AND p.Status IN ('active', 'available')";
            } else {
                $sql .= " AND p.Status = :status";
                $params['status'] = $status;
            }
        }
        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function softDelete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE `products` SET `Status` = 'deleted' WHERE `ID` = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getSellerStats(int $sellerId): array {
        $stmt = $this->db->prepare("SELECT SUM(Total_price) as revenue, COUNT(*) as delivered_count 
                                    FROM `orders` 
                                    WHERE Seller_ID = :seller_id AND Status = 'completed'");
        $stmt->execute(['seller_id' => $sellerId]);
        $row = $stmt->fetch();
        return [
            'revenue' => (float)($row['revenue'] ?? 0.0),
            'delivered_orders' => (int)($row['delivered_count'] ?? 0)
        ];
    }

    public function findAllForAdmin(array $filters = []): array {
        $sql = "SELECT p.*, c.Name as CategoryName, u.Username as SellerName, u.Phone as SellerPhone, u.Email as SellerEmail
                FROM `products` p
                JOIN `categories` c ON p.Category_ID = c.ID
                JOIN `users` u ON p.Seller_ID = u.ID
                WHERE p.Status != 'deleted'";

        $params = [];
        if (!empty($filters['search'])) {
            $sql .= " AND (p.Name LIKE :search_name OR u.Username LIKE :search_seller)";
            $params['search_name'] = '%' . $filters['search'] . '%';
            $params['search_seller'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= " AND p.Status IN ('active', 'available')";
            } else {
                $sql .= " AND p.Status = :status";
                $params['status'] = $filters['status'];
            }
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
