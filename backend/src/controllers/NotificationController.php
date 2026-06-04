<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\NotificationService;

class NotificationController extends BaseController {
    private $notificationService;

    public function __construct() {
        $this->notificationService = new NotificationService();
    }

    /**
     * GET /api/notifications
     * Retrieve all notifications for active user session
     */
    public function list() {
        $result = $this->notificationService->getMyNotifications();
        if ($result['status'] === 'success') {
            return $this->json($result['data'], 200);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }

    /**
     * POST /api/notifications/read
     * Mark a specific notification as read
     */
    public function markRead() {
        $data = $this->getRequestBody();
        if (empty($data['id'])) {
            return $this->json(['error' => 'Thiếu tham số ID thông báo.'], 400);
        }

        $notificationId = intval($data['id']);
        $result = $this->notificationService->markAsRead($notificationId);

        if ($result['status'] === 'success') {
            return $this->json(['message' => $result['message']], 200);
        }

        return $this->json(['error' => $result['message']], $result['code']);
    }
}
