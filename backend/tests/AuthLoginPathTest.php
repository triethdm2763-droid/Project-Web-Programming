<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthLoginPathTest extends TestCase
{
    /**
     * P1:
     * Thiếu username -> validation fail -> HTTP 400
     * Path: N1-N2-N3-N4-N16
     */
    public function testP1MissingRequiredFieldReturns400(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::never())
            ->method('findByUsername');

        $repository->expects(self::never())
            ->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => '',
            'password' => 'Password123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }

    /**
     * P2:
     * Không tìm thấy bằng username
     * -> fallback sang email
     * -> vẫn không tìm thấy
     * -> HTTP 401
     *
     * Path: N1-N2-N3-N5-N6-N7-N8-N9-N16
     */
    public function testP2UserNotFoundReturns401AfterEmailFallbackMisses(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('missing_user')
            ->willReturn(null);

        $repository->expects(self::once())
            ->method('findByEmail')
            ->with('missing_user')
            ->willReturn(null);

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'missing_user',
            'password' => 'Password123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    /**
     * P3:
     * User tồn tại nhưng password sai
     * -> HTTP 401
     *
     * Path: N1-N2-N3-N5-N6-N8-N10-N11-N16
     */
    public function testP3WrongPasswordReturns401(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('buyer_a')
            ->willReturn(
                $this->activeUser('buyer_a', 'CorrectPassword')
            );

        $repository->expects(self::never())
            ->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_a',
            'password' => 'WrongPassword',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    /**
     * P4:
     * User tồn tại
     * -> password đúng
     * -> Status = banned
     * -> HTTP 403
     *
     * Path: N1-N2-N3-N5-N6-N8-N10-N12-N13-N16
     */
    public function testP4BannedAccountReturns403(): void
    {
        $user = $this->activeUser('buyer_e', 'Password123');
        $user['Status'] = 'banned';

        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('buyer_e')
            ->willReturn($user);

        $repository->expects(self::never())
            ->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_e',
            'password' => 'Password123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(403, $result['code']);
    }

    /**
     * P5:
     * Login thành công bằng username
     * -> HTTP 200
     * -> Password phải bị loại khỏi response
     *
     * Path: N1-N2-N3-N5-N6-N8-N10-N12-N14-N15-N16
     */
    public function testP5ValidUsernameLoginReturns200AndRemovesPassword(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('buyer_a')
            ->willReturn(
                $this->activeUser('buyer_a', 'Password123')
            );

        $repository->expects(self::never())
            ->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_a',
            'password' => 'Password123',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame('buyer_a', $result['user']['Username']);
        self::assertArrayNotHasKey('Password', $result['user']);
    }

    /**
     * P6:
     * Không tìm thấy bằng username
     * -> fallback sang email
     * -> tìm thấy user
     * -> password đúng
     * -> active
     * -> HTTP 200
     *
     * Path: N1-N2-N3-N5-N6-N7-N8-N10-N12-N14-N15-N16
     */
    public function testP6EmailFallbackLoginReturns200(): void
    {
        $email = 'buyera@gmail.com';

        $user = $this->activeUser('buyer_a', 'Password123');
        $user['Email'] = $email;

        $repository = $this->createMock(UserRepository::class);

        $repository->expects(self::once())
            ->method('findByUsername')
            ->with($email)
            ->willReturn(null);

        $repository->expects(self::once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $result = $this->serviceWithRepository($repository)->login([
            'username' => $email,
            'password' => 'Password123',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame('buyer_a', $result['user']['Username']);
        self::assertSame($email, $result['user']['Email']);
        self::assertArrayNotHasKey('Password', $result['user']);
    }

    /**
     * Tạo AuthService nhưng thay UserRepository thật bằng mock.
     */
    private function serviceWithRepository(
        UserRepository $repository
    ): AuthService {
        $reflection = new ReflectionClass(AuthService::class);

        /** @var AuthService $service */
        $service = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getProperty('userRepository');
        $property->setValue($service, $repository);

        return $service;
    }

    /**
     * Tạo dữ liệu user active phục vụ test.
     *
     * @return array<string, mixed>
     */
    private function activeUser(
        string $username,
        string $plainPassword
    ): array {
        return [
            'ID' => 7,
            'Username' => $username,
            'Email' => $username . '@example.com',
            'Password' => password_hash(
                $plainPassword,
                PASSWORD_BCRYPT
            ),
            'Role' => 'user',
            'Status' => 'active',
        ];
    }
}