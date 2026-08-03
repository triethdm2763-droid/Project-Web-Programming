<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Utils\JWT;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testRegisterValidDataCreatesUserWithBcryptPassword(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('new_user')
            ->willReturn(null);
        $repository->expects(self::once())
            ->method('findByEmail')
            ->with('new@example.com')
            ->willReturn(null);
        $repository->expects(self::once())
            ->method('create')
            ->with(
                'new_user',
                'new@example.com',
                self::callback(static function (string $hash): bool {
                    return password_verify('Password123', $hash)
                        && password_get_info($hash)['algoName'] === 'bcrypt';
                }),
                '0901234567'
            )
            ->willReturn(101);

        $result = $this->serviceWithRepository($repository)->register([
            'username' => ' new_user ',
            'email' => ' new@example.com ',
            'password' => 'Password123',
            'phone' => '0901234567',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(201, $result['code']);
        self::assertSame(101, $result['user_id']);
    }

    public function testRegisterRejectsDuplicateUsernameWith409AndUsernameError(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('existing_user')
            ->willReturn(['ID' => 1]);
        $repository->expects(self::never())->method('findByEmail');
        $repository->expects(self::never())->method('create');

        $result = $this->serviceWithRepository($repository)->register([
            'username' => 'existing_user',
            'email' => 'new@example.com',
            'password' => 'Password123',
            'phone' => '0901234567',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(409, $result['code']);
        self::assertArrayHasKey('username', $result['errors']);
    }

    public function testRegisterRejectsDuplicateEmailWith409AndEmailError(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByUsername')
            ->willReturn(null);
        $repository->expects(self::once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn(['ID' => 2]);
        $repository->expects(self::never())->method('create');

        $result = $this->serviceWithRepository($repository)->register([
            'username' => 'new_user',
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'phone' => '0901234567',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(409, $result['code']);
        self::assertArrayHasKey('email', $result['errors']);
    }

    public function testLoginSuccessReturnsUserWithoutPassword(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByUsername')
            ->with('buyer_a')
            ->willReturn($this->activeUser('buyer_a', 'Password123'));
        $repository->expects(self::never())->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_a',
            'password' => 'Password123',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame('buyer_a', $result['user']['Username']);
        self::assertArrayNotHasKey('Password', $result['user']);
    }

    public function testLoginRejectsWrongPasswordWith401(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->method('findByUsername')
            ->willReturn($this->activeUser('buyer_a', 'CorrectPassword'));

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_a',
            'password' => 'WrongPassword',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    public function testLoginRejectsEmptyUsernameWith400(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::never())->method('findByUsername');
        $repository->expects(self::never())->method('findByEmail');

        $result = $this->serviceWithRepository($repository)->login([
            'username' => '',
            'password' => 'Password123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertArrayHasKey('username', $result['errors']);
    }

    public function testLoginRejectsBannedAccountWith403(): void
    {
        $user = $this->activeUser('buyer_e', 'Password123');
        $user['Status'] = 'banned';

        $repository = $this->createMock(UserRepository::class);
        $repository->method('findByUsername')->willReturn($user);

        $result = $this->serviceWithRepository($repository)->login([
            'username' => 'buyer_e',
            'password' => 'Password123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(403, $result['code']);
    }

    public function testRequestPasswordResetCreatesSixDigitOtpValidForAbout300Seconds(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByEmail')
            ->with('buyer@example.com')
            ->willReturn(['ID' => 7, 'Email' => 'buyer@example.com']);

        $before = time();
        $result = $this->serviceWithRepository($repository)->requestPasswordReset([
            'email' => 'buyer@example.com',
        ]);
        $after = time();

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertMatchesRegularExpression('/^\d{6}$/', $result['otp']);
        self::assertSame('buyer@example.com', $_SESSION['reset_email']);
        self::assertSame($result['otp'], $_SESSION['reset_otp']);
        self::assertGreaterThanOrEqual($before + 300, $_SESSION['reset_expiry']);
        self::assertLessThanOrEqual($after + 300, $_SESSION['reset_expiry']);
    }

    public function testResetPasswordSucceedsWithCorrectUnexpiredOtpAndBcryptHash(): void
    {
        $_SESSION['reset_email'] = 'buyer@example.com';
        $_SESSION['reset_otp'] = '123456';
        $_SESSION['reset_expiry'] = time() + 300;

        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findByEmail')
            ->with('buyer@example.com')
            ->willReturn(['ID' => 7]);
        $repository->expects(self::once())
            ->method('updatePassword')
            ->with(
                7,
                self::callback(static function (string $hash): bool {
                    return password_verify('NewPassword123', $hash)
                        && password_get_info($hash)['algoName'] === 'bcrypt';
                })
            )
            ->willReturn(true);

        $result = $this->serviceWithRepository($repository)->resetPassword([
            'otp' => '123456',
            'password' => 'NewPassword123',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertArrayNotHasKey('reset_email', $_SESSION);
        self::assertArrayNotHasKey('reset_otp', $_SESSION);
        self::assertArrayNotHasKey('reset_expiry', $_SESSION);
    }

    public function testResetPasswordRejectsWrongOtp(): void
    {
        $_SESSION['reset_email'] = 'buyer@example.com';
        $_SESSION['reset_otp'] = '123456';
        $_SESSION['reset_expiry'] = time() + 300;

        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::never())->method('findByEmail');
        $repository->expects(self::never())->method('updatePassword');

        $result = $this->serviceWithRepository($repository)->resetPassword([
            'otp' => '999999',
            'password' => 'NewPassword123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertStringContainsString('không chính xác', $result['message']);
    }

    public function testResetPasswordRejectsExpiredOtpAfter300Seconds(): void
    {
        $_SESSION['reset_email'] = 'buyer@example.com';
        $_SESSION['reset_otp'] = '123456';
        $_SESSION['reset_expiry'] = time() - 1;

        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::never())->method('findByEmail');
        $repository->expects(self::never())->method('updatePassword');

        $result = $this->serviceWithRepository($repository)->resetPassword([
            'otp' => '123456',
            'password' => 'NewPassword123',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertStringContainsString('hết hạn', $result['message']);
        self::assertArrayNotHasKey('reset_email', $_SESSION);
        self::assertArrayNotHasKey('reset_otp', $_SESSION);
        self::assertArrayNotHasKey('reset_expiry', $_SESSION);
    }

    public function testJwtValidTokenCanBeDecoded(): void
    {
        $token = JWT::encode([
            'user_id' => 7,
            'username' => 'buyer_a',
            'role' => 'user',
        ], 60);

        $payload = JWT::decode($token);

        self::assertNotNull($payload);
        self::assertSame(7, $payload['user_id']);
        self::assertSame('buyer_a', $payload['username']);
    }

    public function testJwtRejectsTamperedSignature(): void
    {
        $token = JWT::encode(['user_id' => 7], 60);
        [$header, $payload, $signature] = explode('.', $token);
        $tampered = $header . '.' . $payload . '.' . substr($signature, 0, -1)
            . ($signature[-1] === 'a' ? 'b' : 'a');

        self::assertNull(JWT::decode($tampered));
    }

    public function testJwtRejectsExpiredToken(): void
    {
        $token = JWT::encode(['user_id' => 7], -1);

        self::assertNull(JWT::decode($token));
    }

    /**
     * @return UserRepository&MockObject
     */
    private function repositoryMock(): UserRepository
    {
        return $this->createMock(UserRepository::class);
    }

    private function serviceWithRepository(UserRepository $repository): AuthService
    {
        $reflection = new ReflectionClass(AuthService::class);
        /** @var AuthService $service */
        $service = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getProperty('userRepository');
        $property->setAccessible(true);
        $property->setValue($service, $repository);

        return $service;
    }

    /**
     * @return array<string, mixed>
     */
    private function activeUser(string $username, string $plainPassword): array
    {
        return [
            'ID' => 7,
            'Username' => $username,
            'Email' => $username . '@example.com',
            'Password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'Role' => 'user',
            'Status' => 'active',
        ];
    }
}
