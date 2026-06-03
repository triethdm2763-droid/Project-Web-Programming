<?php
namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;
use Exception;

class OrderRepository extends BaseRepository {

    /**
     * Create order and payment transactions with row lock for race condition prevention
     * 
     * @param array $orderData
     * @param array $paymentData
     * @return int Created Order ID
     * @throws Exception
     */
    public function createWithTransaction(array $orderData, array $paymentData): int {
        $this->db->beginTransaction();

        try {
            // 1. Lock the product row and verify stock
            $prodSql = "SELECT * FROM `products` WHERE `ID` = :id LIMIT 1 FOR UPDATE";
            $prodStmt = $this->db->prepare($prodSql);
            $prodStmt->execute(['id' => $orderData['product_id']]);
            $product = $prodStmt->fetch();

            if (!$product || $product['Status'] !== 'active' || $product['Stock_quantity'] < 1) {
                throw new Exception("Sản phẩm đã bán hoặc không còn khả dụng.");
            }

            // 2. Insert Order
            $orderSql = "INSERT INTO `orders` (`Buyer_ID`, `Seller_ID`, `Product_ID`, `Total_price`, `Shipping_address`, `Status`) 
                         VALUES (:buyer_id, :seller_id, :product_id, :total_price, :shipping_address, :status)";
            
            $orderStmt = $this->db->prepare($orderSql);
            $orderStmt->execute([
                'buyer_id'         => $orderData['buyer_id'],
                'seller_id'        => $orderData['seller_id'],
                'product_id'       => $orderData['product_id'],
                'total_price'      => $orderData['total_price'],
                'shipping_address' => $orderData['shipping_address'],
                'status'           => $orderData['status'] ?? 'pending'
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // 3. Insert Payment using the same transaction database instance
            $paymentSql = "INSERT INTO `payments` (`Order_ID`, `Amount`, `Payment_method`, `Status`) 
                           VALUES (:order_id, :amount, :payment_method, :status)";
            
            $payStmt = $this->db->prepare($paymentSql);
            $payStmt->execute([
                'order_id'       => $orderId,
                'amount'         => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'status'         => $paymentData['status'] ?? 'pending'
            ]);

            // 4. Update Product status to 'sold' and stock to 0
            $updateProdSql = "UPDATE `products` SET `Status` = 'sold', `Stock_quantity` = 0 WHERE `ID` = :id";
            $updateProdStmt = $this->db->prepare($updateProdSql);
            $updateProdStmt->execute(['id' => $orderData['product_id']]);

            $this->db->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find order details by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id) {
        $sql = "SELECT o.*, p.Name as ProductName, p.Image as ProductImage, 
                       b.Username as BuyerName, s.Username as SellerName,
                       pm.Payment_method, pm.Status as PaymentStatus
                FROM `orders` o
                JOIN `products` p ON o.Product_ID = p.ID
                JOIN `users` b ON o.Buyer_ID = b.ID
                JOIN `users` s ON o.Seller_ID = s.ID
                LEFT JOIN `payments` pm ON pm.Order_ID = o.ID
                WHERE o.ID = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Find list of orders purchased by a buyer
     * 
     * @param int $buyerId
     * @return array
     */
    public function findByBuyer(int $buyerId): array {
        $sql = "SELECT o.*, p.Name as ProductName, p.Image as ProductImage, s.Username as SellerName
                FROM `orders` o
                JOIN `products` p ON o.Product_ID = p.ID
                JOIN `users` s ON o.Seller_ID = s.ID
                WHERE o.Buyer_ID = :buyer_id
                ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['buyer_id' => $buyerId]);
        return $stmt->fetchAll();
    }

    /**
     * Find list of orders received by a seller
     * 
     * @param int $sellerId
     * @return array
     */
    public function findBySeller(int $sellerId): array {
        $sql = "SELECT o.*, p.Name as ProductName, p.Image as ProductImage, b.Username as BuyerName
                FROM `orders` o
                JOIN `products` p ON o.Product_ID = p.ID
                JOIN `users` b ON o.Buyer_ID = b.ID
                WHERE o.Seller_ID = :seller_id
                ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll();
    }

    /**
     * Update order status
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE `orders` SET `Status` = :status WHERE `ID` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'id'     => $id
        ]);
    }
}
