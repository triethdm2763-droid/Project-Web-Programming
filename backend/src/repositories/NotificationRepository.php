<?php

namespace App\Repositories;

use App\Core\BaseRepository;
use PDO;

class NotificationRepository extends BaseRepository
{

    /**
     * Get all notifications for a specific user
     * 
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array
    {
        $sql = "SELECT * FROM `notifications` WHERE `User_ID` = :user_id ORDER BY `created_at` DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create a notification record (can be used inside transactions)
     * 
     * @param int $userId
     * @param string $title
     * @param string $content
     * @param PDO|null $customDb (optional custom PDO connection if within a transaction)
     * @return int
     */
    public function create(int $userId, string $title, string $content, $customDb = null): int
    {
        $db = $customDb ?: $this->db;
        $sql = "INSERT INTO `notifications` (`User_ID`, `Title`, `Content`, `Is_read`) 
                VALUES (:user_id, :title, :content, 0)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'title'   => $title,
            'content' => $content
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Mark a specific notification as read for security validation
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE `notifications` SET `Is_read` = 1 WHERE `ID` = :id AND `User_ID` = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'      => $notificationId,
            'user_id' => $userId
        ]);
    }
}
