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

            if (!$product || !in_array($product['Status'], ['active', 'available']) || $product['Stock_quantity'] < 1) {
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
     * Cancel order and release product stock/status under transaction
     * 
     * @param int $orderId
     * @param int $productId
     * @throws Exception
     */
    public function cancelWithTransaction(int $orderId, int $productId) {
        $this->db->beginTransaction();

        try {
            // 1. Update order status to 'cancelled'
            $orderSql = "UPDATE `orders` SET `Status` = 'cancelled' WHERE `ID` = :order_id";
            $orderStmt = $this->db->prepare($orderSql);
            $orderStmt->execute(['order_id' => $orderId]);

            // 2. Update payment status to 'cancelled' (if matching record exists)
            $paySql = "UPDATE `payments` SET `Status` = 'cancelled' WHERE `Order_ID` = :order_id";
            $payStmt = $this->db->prepare($paySql);
            $payStmt->execute(['order_id' => $orderId]);

            // 3. Revert product status to 'available' and stock quantity to 1
            $prodSql = "UPDATE `products` SET `Status` = 'available', `Stock_quantity` = 1 WHERE `ID` = :product_id";
            $prodStmt = $this->db->prepare($prodSql);
            $prodStmt->execute(['product_id' => $productId]);

            $this->db->commit();
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

    /**
     * Lấy toàn bộ đơn hàng trong hệ thống kèm tên sản phẩm và tên người mua,
     * dùng cho trang quản lý đơn hàng của Admin.
     *
     * @return array
     */
    public function findAll()
    {
        $sql = "
            SELECT
                o.*,
                p.Name AS ProductName,
                u.Username AS BuyerName
            FROM orders o
            JOIN products p
                ON o.Product_ID = p.ID
            JOIN users u
                ON o.Buyer_ID = u.ID
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
