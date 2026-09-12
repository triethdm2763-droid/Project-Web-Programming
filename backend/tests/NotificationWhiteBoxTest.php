<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Core\BaseRepository;
use App\Config\Database;
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

    private function serviceWithResult(
        bool $markResult,
        array $notifications = [],
        int $createdId = 1
    ): array
    {
        $repo = new class($markResult, $notifications, $createdId) extends NotificationRepository {
            public bool $markResult;
            public array $notifications;
            public int $createdId;
            public int $calls = 0;
            public ?int $lastNotificationId = null;
            public ?int $lastUserId = null;
            public int $findByUserCalls = 0;
            public ?int $lastFindByUserId = null;
            public int $createCalls = 0;
            public ?array $lastCreateArguments = null;

            public function __construct(
                bool $markResult,
                array $notifications,
                int $createdId
            )
            {
                $this->markResult = $markResult;
                $this->notifications = $notifications;
                $this->createdId = $createdId;
            }

            public function findByUser(int $userId): array
            {
                $this->findByUserCalls++;
                $this->lastFindByUserId = $userId;

                return $this->notifications;
            }

            public function create(
                int $userId,
                string $title,
                string $content,
                $customDb = null
            ): int {
                $this->createCalls++;
                $this->lastCreateArguments = [
                    $userId,
                    $title,
                    $content,
                    $customDb
                ];

                return $this->createdId;
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

    public function test_constructor_creates_default_repository(): void
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $database = $databaseReflection->newInstanceWithoutConstructor();
        $connection = $this->createMock(PDO::class);

        $connectionProperty = $databaseReflection->getProperty('conn');
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue($database, $connection);

        $instanceProperty = $databaseReflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $previousInstance = $instanceProperty->getValue();
        $instanceProperty->setValue(null, $database);

        try {
            $service = new NotificationService();
            $serviceReflection = new ReflectionClass($service);
            $repositoryProperty = $serviceReflection->getProperty(
                'notificationRepository'
            );
            $repositoryProperty->setAccessible(true);

            self::assertInstanceOf(
                NotificationRepository::class,
                $repositoryProperty->getValue($service)
            );
        } finally {
            $instanceProperty->setValue(null, $previousInstance);
        }
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

    public function test_get_notifications_returns_current_users_data(): void
    {
        $_SESSION['user_id'] = '7';
        $notifications = [
            ['ID' => 11, 'Title' => 'Đơn hàng mới'],
            ['ID' => 12, 'Title' => 'Đã thanh toán']
        ];

        [$service, $repo] = $this->serviceWithResult(
            true,
            $notifications
        );

        $result = $service->getMyNotifications();

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame($notifications, $result['data']);
        self::assertSame(1, $repo->findByUserCalls);
        self::assertSame(7, $repo->lastFindByUserId);
    }

    public function test_send_returns_true_when_repository_creates_positive_id(): void
    {
        $transaction = $this->createMock(PDO::class);
        [$service, $repo] = $this->serviceWithResult(true, [], 25);

        $result = $service->send(
            7,
            'Đơn hàng mới',
            'Bạn vừa nhận được một đơn hàng.',
            $transaction
        );

        self::assertTrue($result);
        self::assertSame(1, $repo->createCalls);
        self::assertSame(
            [
                7,
                'Đơn hàng mới',
                'Bạn vừa nhận được một đơn hàng.',
                $transaction
            ],
            $repo->lastCreateArguments
        );
    }

    public function test_send_returns_false_when_repository_does_not_create_row(): void
    {
        [$service, $repo] = $this->serviceWithResult(true, [], 0);

        $result = $service->send(7, 'Tiêu đề', 'Nội dung');

        self::assertFalse($result);
        self::assertSame(1, $repo->createCalls);
        self::assertSame(
            [7, 'Tiêu đề', 'Nội dung', null],
            $repo->lastCreateArguments
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
