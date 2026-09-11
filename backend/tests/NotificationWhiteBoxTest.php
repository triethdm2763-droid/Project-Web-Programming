<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Core\BaseRepository;
use App\Repositories\NotificationRepository;
use App\Services\NotificationService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class NotificationWhiteBoxTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-notification-' . getmypid());

            if (!session_start([
                'use_cookies' => false,
                'cache_limiter' => ''
            ])) {
                self::fail(
                    'Không thể khởi tạo session dành cho kiểm thử.'
                );
            }
        }

        $_SESSION = [];
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        http_response_code(200);
    }

    private function serviceWithResult(bool $markResult): array
    {
        $repo = new class($markResult) extends NotificationRepository {
            public bool $markResult;
            public int $calls = 0;
            public ?int $lastNotificationId = null;
            public ?int $lastUserId = null;

            public function __construct(bool $markResult)
            {
                $this->markResult = $markResult;
            }

            public function markAsRead(
                int $notificationId,
                int $userId
            ): bool {
                $this->calls++;
                $this->lastNotificationId = $notificationId;
                $this->lastUserId = $userId;

                return $this->markResult;
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

    public function test_WB00_D0_true_starts_session_then_D1_true_returns_401(): void
    {
        $_SESSION = [];

        session_write_close();
        session_id('phpunit-wb00-' . getmypid());

        [$service, $repo] = $this->serviceWithResult(true);

        $result = $service->markAsRead(10);

        self::assertSame(
            PHP_SESSION_ACTIVE,
            session_status()
        );

        self::assertSame(
            'error',
            $result['status']
        );

        self::assertSame(
            401,
            $result['code']
        );

        self::assertSame(
            0,
            $repo->calls
        );
    }

    public function test_WB01_D1_true_not_logged_in_returns_401(): void
    {
        [$service, $repo] = $this->serviceWithResult(true);

        $result = $service->markAsRead(10);

        self::assertSame(
            'error',
            $result['status']
        );

        self::assertSame(
            401,
            $result['code']
        );

        self::assertSame(
            0,
            $repo->calls
        );
    }

    public function test_WB02_D1_false_D2_true_returns_200(): void
    {
        $_SESSION['user_id'] = 7;

        [$service, $repo] = $this->serviceWithResult(true);

        $result = $service->markAsRead(10);

        self::assertSame(
            'success',
            $result['status']
        );

        self::assertSame(
            200,
            $result['code']
        );

        self::assertSame(
            1,
            $repo->calls
        );

        self::assertSame(
            10,
            $repo->lastNotificationId
        );

        self::assertSame(
            7,
            $repo->lastUserId
        );
    }

    public function test_WB03_D1_false_D2_false_returns_400(): void
    {
        $_SESSION['user_id'] = 7;

        [$service, $repo] = $this->serviceWithResult(false);

        $result = $service->markAsRead(10);

        self::assertSame(
            'error',
            $result['status']
        );

        self::assertSame(
            400,
            $result['code']
        );

        self::assertSame(
            1,
            $repo->calls
        );

        self::assertSame(
            10,
            $repo->lastNotificationId
        );

        self::assertSame(
            7,
            $repo->lastUserId
        );
    }

    public function test_WB04_get_notifications_starts_session_then_returns_401(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        session_id(
            'phpunit-get-notification-' . getmypid()
        );

        [$service, $repo] = $this->serviceWithResult(true);

        $result = $service->getMyNotifications();

        self::assertSame(
            PHP_SESSION_ACTIVE,
            session_status()
        );

        self::assertSame(
            'error',
            $result['status']
        );

        self::assertSame(
            401,
            $result['code']
        );

        self::assertSame(
            'Chưa đăng nhập.',
            $result['message']
        );

        self::assertSame(
            0,
            $repo->calls
        );
    }

    public function test_DEF_NOTI_01_mark_as_read_should_be_false_when_zero_rows_updated(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt
            ->method('execute')
            ->willReturn(true);

        $stmt
            ->method('rowCount')
            ->willReturn(0);

        $pdo = $this->createMock(PDO::class);

        $pdo
            ->method('prepare')
            ->willReturn($stmt);

        $reflection = new ReflectionClass(
            NotificationRepository::class
        );

        /** @var NotificationRepository $repo */
        $repo = $reflection
            ->newInstanceWithoutConstructor();

        $dbProp = new ReflectionProperty(
            BaseRepository::class,
            'db'
        );

        $dbProp->setAccessible(true);
        $dbProp->setValue($repo, $pdo);

        self::assertFalse(
            $repo->markAsRead(999, 7)
        );
    }
}