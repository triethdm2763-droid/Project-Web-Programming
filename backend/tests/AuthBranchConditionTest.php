<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Repositories\UserRepository;

final class AuthBranchConditionTest extends TestCase
{
    private function serviceWithRepository($repo): AuthService
    {
        $service = new AuthService();

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('userRepository');
        $property->setAccessible(true);
        $property->setValue($service, $repo);

        return $service;
    }


    /**
     * AUTH-WB-01
     * Branch:
     * Validation error = True
     */
    public function testLoginValidationFailed(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $service = $this->serviceWithRepository($repo);

        $result = $service->login([
            'username' => '',
            'password' => ''
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }


    /**
     * AUTH-WB-02
     * Branch:
     * username not found
     * user === null = True
     */
    public function testLoginUserNotFound(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->willReturn(null);

        $repo->expects(self::once())
            ->method('findByEmail')
            ->willReturn(null);

        $service = $this->serviceWithRepository($repo);

        $result = $service->login([
            'username' => 'unknown',
            'password' => 'Password123'
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }


    /**
     * AUTH-WB-03
     * Branch:
     * password_verify = False
     */
    public function testLoginWrongPassword(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->willReturn([
                'ID' => 1,
                'Username' => 'user01',
                'Password' => password_hash(
                    'CorrectPassword123',
                    PASSWORD_BCRYPT
                ),
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
     * AUTH-WB-04
     * Branch:
     * Status !== active = True
     */
    public function testLoginInactiveAccount(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->willReturn([
                'ID' => 1,
                'Username' => 'user01',
                'Password' => password_hash(
                    'Password123',
                    PASSWORD_BCRYPT
                ),
                'Status' => 'inactive'
            ]);

        $service = $this->serviceWithRepository($repo);

        $result = $service->login([
            'username' => 'user01',
            'password' => 'Password123'
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(403, $result['code']);
    }


    /**
     * AUTH-WB-05
     * Branch:
     * All conditions False
     * Login success
     */
    public function testLoginSuccess(): void
    {
        $repo = $this->createMock(UserRepository::class);

        $repo->expects(self::once())
            ->method('findByUsername')
            ->willReturn([
                'ID' => 1,
                'Username' => 'user01',
                'Email' => 'user01@gmail.com',
                'Password' => password_hash(
                    'Password123',
                    PASSWORD_BCRYPT
                ),
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
}