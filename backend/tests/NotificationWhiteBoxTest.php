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
        $_SESSION = [];
    }

    private function serviceWithResult(bool $markResult): NotificationService
    {
        $repo = new class($markResult) extends NotificationRepository {
            public bool $markResult;
            public function __construct(bool $markResult) { $this->markResult = $markResult; }
            public function markAsRead(int $notificationId, int $userId): bool { return $this->markResult; }
        };

        $ref = new ReflectionClass(NotificationService::class);
        /** @var NotificationService $service */
        $service = $ref->newInstanceWithoutConstructor();
        $prop = $ref->getProperty('notificationRepository');
        $prop->setAccessible(true);
        $prop->setValue($service, $repo);
        return $service;
    }

    // D1=True: empty($_SESSION['user_id'])
    public function test_WB01_D1_true_not_logged_in_returns_401(): void
    {
        $result = $this->serviceWithResult(true)->markAsRead(10);
        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    // D1=False, D2=True: repository update succeeds
    public function test_WB02_D1_false_D2_true_returns_200(): void
    {
        $_SESSION['user_id'] = 7;
        $result = $this->serviceWithResult(true)->markAsRead(10);
        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
    }

    // D1=False, D2=False: repository update fails
    public function test_WB03_D1_false_D2_false_returns_400(): void
    {
        $_SESSION['user_id'] = 7;
        $result = $this->serviceWithResult(false)->markAsRead(10);
        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }

    // DEF-NOTI-01: execute() can succeed while UPDATE affects zero rows.
    // Current repository returns execute() directly, so this test is expected to FAIL.
    public function test_DEF_NOTI_01_mark_as_read_should_be_false_when_zero_rows_updated(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(0);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $reflection = new ReflectionClass(NotificationRepository::class);
        /** @var NotificationRepository $repo */
        $repo = $reflection->newInstanceWithoutConstructor();
        $dbProp = new ReflectionProperty(BaseRepository::class, 'db');
        $dbProp->setAccessible(true);
        $dbProp->setValue($repo, $pdo);

        self::assertFalse($repo->markAsRead(999, 7));
    }
}
