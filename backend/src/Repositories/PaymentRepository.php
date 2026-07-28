<?php

namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class PaymentRepository extends BaseRepository
{

    /**
     * Create payment record inside transaction context
     * 
     * @param int $orderId
     * @param float $amount
     * @param string $method COD, Bank_Transfer, etc.
     * @param string $status pending, success, failed
     * @param PDO|null $customDb Optional transaction PDO
     * @return int
     */
    public function create(int $orderId, float $amount, string $method, string $status = 'pending', $customDb = null): int
    {
        $db = $customDb ?: $this->db;
        $sql = "INSERT INTO `payments` (`Order_ID`, `Amount`, `Payment_method`, `Status`) 
                VALUES (:order_id, :amount, :method, :status)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'order_id' => $orderId,
            'amount'   => $amount,
            'method'   => $method,
            'status'   => $status
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Update payment status
     * 
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $sql = "UPDATE `payments` SET `Status` = :status WHERE `Order_ID` = :order_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status'   => $status,
            'order_id' => $orderId
        ]);
    }
}
