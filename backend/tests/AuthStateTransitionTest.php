<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use ReflectionClass;

final class AuthStateTransitionTest extends TestCase
{
    /**
     * AUTH-ST-02
     * S1 -> S2
     * Login thành công
     */
    public function testValidLoginTransitionToAuthenticatedState(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->with('user01')
            ->willReturn([
                'ID' => 1,
                'Username' => 'user01',
                'Email' => 'user01@gmail.com',
                'Password' => password_hash('Password123', PASSWORD_BCRYPT),
                'Role' => 'user',
                'Status' => 'active'
            ]);

        $service = $this->serviceWithRepository($repo);

        $result = $service->login([
            'username' => 'user01',
            'password' => 'Password123'
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
    }


    /**
     * AUTH-ST-03
     * S1 -> S3
     * Login sai mật khẩu
     */
    public function testWrongPasswordTransitionToFailedState(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->willReturn([
                'ID' => 1,
                'Username' => 'user01',
                'Password' => password_hash('CorrectPassword', PASSWORD_BCRYPT),
                'Status' => 'active'
            ]);

        $service = $this->serviceWithRepository($repo);

        $result = $service->login([
            'username' => 'user01',
            'password' => 'WrongPassword'
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }


    /**
     * AUTH-ST-05
     * S0 -> S5
     * Register thành công
     */
    public function testRegisterSuccessTransition(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->method('findByUsername')
            ->willReturn(null);

        $repo->method('findByEmail')
            ->willReturn(null);

        $repo->method('create')
            ->willReturn(10);

        $service = $this->serviceWithRepository($repo);

        $result = $service->register([
            'username' => 'newuser',
            'email' => 'newuser@gmail.com',
            'password' => 'Password123',
            'phone' => '0912345678'
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(201, $result['code']);
    }


    /**
     * AUTH-ST-06
     * S0 -> S6
     * Register thất bại do validation
     */
    public function testRegisterFailedTransition(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $service = $this->serviceWithRepository($repo);

        $result = $service->register([
            'username' => '',
            'email' => 'wrong-email',
            'password' => ''
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }


    /**
     * AUTH-ST-08
     * S0 -> S0
     * Lấy user khi chưa login
     */
    public function testGetCurrentUserWithoutSession(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $service = $this->serviceWithRepository($repo);

        $_SESSION = [];

        $result = $service->getCurrentUser();

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }


    private function serviceWithRepository(
        UserRepository $repository
    ): AuthService {

        $reflection = new ReflectionClass(AuthService::class);

        $service = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getProperty('userRepository');
        $property->setAccessible(true);
        $property->setValue($service, $repository);

        return $service;
    }
}