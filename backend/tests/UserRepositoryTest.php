<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\BaseRepository;
use App\Repositories\UserRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class UserRepositoryTest extends TestCase
{
    public function testFindByUsernameReturnsUser(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['username' => 'buyer_a'])
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([
                'ID' => 7,
                'Username' => 'buyer_a',
                'Email' => 'buyer@example.com',
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT * FROM `users` WHERE `Username` = :username LIMIT 1')
            ->willReturn($statement);

        $repository = $this->repositoryWithPdo($pdo);
        $user = $repository->findByUsername('buyer_a');

        self::assertNotNull($user);
        self::assertSame(7, $user['ID']);
        self::assertSame('buyer_a', $user['Username']);
    }

    public function testFindByUsernameReturnsNullWhenNoUserExists(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        $repository = $this->repositoryWithPdo($pdo);

        self::assertNull($repository->findByUsername('missing_user'));
    }

    public function testFindByEmailUsesPreparedStatementAndReturnsUser(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['email' => 'buyer@example.com'])
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([
                'ID' => 7,
                'Email' => 'buyer@example.com',
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT * FROM `users` WHERE `Email` = :email LIMIT 1')
            ->willReturn($statement);

        $user = $this->repositoryWithPdo($pdo)->findByEmail('buyer@example.com');

        self::assertNotNull($user);
        self::assertSame('buyer@example.com', $user['Email']);
    }

    public function testCreateInsertsUserAndReturnsGeneratedId(): void
    {
        $hash = password_hash('Password123', PASSWORD_BCRYPT);

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'username' => 'new_user',
                'password' => $hash,
                'email' => 'new@example.com',
                'phone' => '0901234567',
                'role' => 'user',
            ])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(static function (string $sql): bool {
                return str_contains($sql, 'INSERT INTO `users`')
                    && str_contains($sql, "'active'");
            }))
            ->willReturn($statement);
        $pdo->expects(self::once())
            ->method('lastInsertId')
            ->willReturn('101');

        $id = $this->repositoryWithPdo($pdo)->create(
            'new_user',
            'new@example.com',
            $hash,
            '0901234567'
        );

        self::assertSame(101, $id);
    }

    public function testUpdatePasswordUsesHashAndUserId(): void
    {
        $hash = password_hash('NewPassword123', PASSWORD_BCRYPT);

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'password' => $hash,
                'id' => 7,
            ])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('UPDATE `users` SET `Password` = :password WHERE `ID` = :id')
            ->willReturn($statement);

        $updated = $this->repositoryWithPdo($pdo)->updatePassword(7, $hash);

        self::assertTrue($updated);
    }

    public function testUpdateStatusUsesExpectedParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'status' => 'banned',
                'id' => 7,
            ])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('UPDATE `users` SET `Status` = :status WHERE `ID` = :id')
            ->willReturn($statement);

        $updated = $this->repositoryWithPdo($pdo)->updateStatus(7, 'banned');

        self::assertTrue($updated);
    }

    private function repositoryWithPdo(PDO $pdo): UserRepository
    {
        $reflection = new ReflectionClass(UserRepository::class);
        /** @var UserRepository $repository */
        $repository = $reflection->newInstanceWithoutConstructor();

        $property = new \ReflectionProperty(BaseRepository::class, 'db');
        $property->setAccessible(true);
        $property->setValue($repository, $pdo);

        return $repository;
    }
}
