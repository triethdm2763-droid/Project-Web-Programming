<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Core\Session;

class NotificationService
{
    private $notificationRepository;

    public function __construct()
    {
        $this->notificationRepository = new NotificationRepository();
    }

    /**
     * Get all notifications of currently logged in user
     * 
     * @return array
     */
    public function getMyNotifications(): array
    {
        Session::start();
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Chưa đăng nhập.'
            ];
        }

        $notifications = $this->notificationRepository->findByUser((int)$_SESSION['user_id']);
        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $notifications
        ];
    }

    /**
     * Send a notification helper
     * 
     * @param int $userId
     * @param string $title
     * @param string $content
     * @param mixed $dbTransaction Optional db instance inside transaction
     * @return bool
     */
    public function send(int $userId, string $title, string $content, $dbTransaction = null): bool
    {
        return $this->notificationRepository->create($userId, $title, $content, $dbTransaction) > 0;
    }

    /**
     * Mark a notification as read
     * 
     * @param int $notificationId
     * @return array
     */
    public function markAsRead(int $notificationId): array
    {
        Session::start();
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Chưa đăng nhập.'
            ];
        }

        $userId = (int)$_SESSION['user_id'];
        $success = $this->notificationRepository->markAsRead($notificationId, $userId);

        if ($success) {
            return [
                'status' => 'success',
                'code'   => 200,
                'message' => 'Đã đánh dấu thông báo là đã đọc.'
            ];
        }

        return [
            'status'  => 'error',
            'code'    => 400,
            'message' => 'Không thể cập nhật trạng thái thông báo.'
        ];
    }
}
