<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Controllers\AdminController;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AdminUserNotificationStateTransitionTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        http_response_code(200);
    }

    private function userController(string $startState, int $userId = 7): array
    {
        $userRepo = new class($startState, $userId) extends UserRepository {
            public string $state;
            public int $id;
            public int $updateCalls = 0;
            public function __construct(string $state, int $id) { $this->state = $state; $this->id = $id; }
            public function updateStatus(int $id, string $status): bool
            {
                $this->updateCalls++;
                if ($id !== $this->id) return false;
                $this->state = $status;
                return true;
            }
        };
        $orderRepo = new class extends OrderRepository { public function __construct() {} };
        $productRepo = new class extends ProductRepository { public function __construct() {} };

        return [new AdminController($userRepo, $orderRepo, $productRepo), $userRepo];
    }

    private function notificationService(int $notificationId, int $ownerId, int $startIsRead): array
    {
        $repo = new class($notificationId, $ownerId, $startIsRead) extends NotificationRepository {
            public int $notificationId;
            public int $ownerId;
            public int $isRead;
            public function __construct(int $notificationId, int $ownerId, int $isRead)
            { $this->notificationId = $notificationId; $this->ownerId = $ownerId; $this->isRead = $isRead; }
            public function markAsRead(int $notificationId, int $userId): bool
            {
                if ($notificationId !== $this->notificationId || $userId !== $this->ownerId) return false;
                $this->isRead = 1;
                return true;
            }
        };

        $ref = new ReflectionClass(NotificationService::class);
        /** @var NotificationService $service */
        $service = $ref->newInstanceWithoutConstructor();
        $prop = $ref->getProperty('notificationRepository');
        $prop->setAccessible(true);
        $prop->setValue($service, $repo);
        return [$service, $repo];
    }

    public function test_UST01_active_to_banned(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('active');
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'banned']);
        self::assertSame(200, $response['status_code']);
        self::assertSame('banned', $repo->state);
    }

    public function test_UST02_banned_to_active(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('banned');
        $controller->updateUserStatus(['id' => 7, 'status' => 'active']);
        self::assertSame('active', $repo->state);
    }

    public function test_UST03_active_to_active_is_allowed(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('active');
        $controller->updateUserStatus(['id' => 7, 'status' => 'active']);
        self::assertSame('active', $repo->state);
    }

    public function test_UST04_banned_to_banned_is_allowed(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('banned');
        $controller->updateUserStatus(['id' => 7, 'status' => 'banned']);
        self::assertSame('banned', $repo->state);
    }

    public function test_UST05_invalid_status_does_not_change_state(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('active');
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'suspended']);
        self::assertSame(400, $response['status_code']);
        self::assertSame('active', $repo->state);
        self::assertSame(0, $repo->updateCalls);
    }

    public function test_UST06_admin_cannot_ban_own_account(): void
    {
        $_SESSION = ['user_id' => 7, 'role' => 'admin'];
        [$controller, $repo] = $this->userController('active', 7);
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'banned']);
        self::assertSame(400, $response['status_code']);
        self::assertSame('active', $repo->state);
        self::assertSame(0, $repo->updateCalls);
    }

    public function test_NST01_unread_to_read(): void
    {
        $_SESSION = ['user_id' => 7];
        [$service, $repo] = $this->notificationService(11, 7, 0);
        $result = $service->markAsRead(11);
        self::assertSame(200, $result['code']);
        self::assertSame(1, $repo->isRead);
    }

    public function test_NST02_read_to_read_is_idempotent(): void
    {
        $_SESSION = ['user_id' => 7];
        [$service, $repo] = $this->notificationService(11, 7, 1);
        $result = $service->markAsRead(11);
        self::assertSame(200, $result['code']);
        self::assertSame(1, $repo->isRead);
    }

    public function test_NST03_without_login_state_does_not_change(): void
    {
        [$service, $repo] = $this->notificationService(11, 7, 0);
        $result = $service->markAsRead(11);
        self::assertSame(401, $result['code']);
        self::assertSame(0, $repo->isRead);
    }

    public function test_NST04_wrong_owner_does_not_change_state(): void
    {
        $_SESSION = ['user_id' => 8];
        [$service, $repo] = $this->notificationService(11, 7, 0);
        $result = $service->markAsRead(11);
        self::assertSame(400, $result['code']);
        self::assertSame(0, $repo->isRead);
    }
}
