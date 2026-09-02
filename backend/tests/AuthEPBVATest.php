<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthEPBVATest extends TestCase
{
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


    private function successRepository(): UserRepository
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->method('findByUsername')
            ->willReturn(null);

        $repository->method('findByEmail')
            ->willReturn(null);

        $repository->method('create')
            ->willReturn(1);

        return $repository;
    }


    /*
     * ============================
     * USERNAME BVA
     * Range: 3 - 50 characters
     * ============================
     */

    public static function usernameBoundaryProvider(): array
    {
        return [
            'min-1: 2 chars' => [
                'ab',
                400
            ],

            'min: 3 chars' => [
                'abc',
                201
            ],

            'min+1: 4 chars' => [
                'abcd',
                201
            ],

            'nominal: 26 chars' => [
                str_repeat('a', 26),
                201
            ],

            'max-1: 49 chars' => [
                str_repeat('a', 49),
                201
            ],

            'max: 50 chars' => [
                str_repeat('a', 50),
                201
            ],

            'max+1: 51 chars' => [
                str_repeat('a', 51),
                400
            ],
        ];
    }


    #[DataProvider('usernameBoundaryProvider')]
    public function testUsernameBoundary(
        string $username,
        int $expectedCode
    ): void {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => $username,
                'email' => uniqid() . '@gmail.com',
                'password' => 'Password123',
                'phone' => '0912345678'
            ]);

        self::assertSame(
            $expectedCode,
            $result['code']
        );
    }


    /*
     * ============================
     * PASSWORD BVA
     * Minimum = 8 characters
     * ============================
     */

    public static function passwordBoundaryProvider(): array
    {
        return [
            'min-1: 7 chars' => [
                '1234567',
                400
            ],

            'min: 8 chars' => [
                '12345678',
                201
            ],

            'min+1: 9 chars' => [
                '123456789',
                201
            ],

            'nominal: 12 chars' => [
                'Password123',
                201
            ],
        ];
    }


    #[DataProvider('passwordBoundaryProvider')]
    public function testPasswordBoundary(
        string $password,
        int $expectedCode
    ): void {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => 'user_test',
                'email' => uniqid() . '@gmail.com',
                'password' => $password,
                'phone' => '0912345678'
            ]);

        self::assertSame(
            $expectedCode,
            $result['code']
        );
    }


    /*
     * ============================
     * PHONE BVA
     * Rule:
     * exactly 10 digits
     * start with 0
     * ============================
     */

    public static function phoneBoundaryProvider(): array
    {
        return [
            '9 digits' => [
                '091234567',
                400
            ],

            '10 digits valid' => [
                '0912345678',
                201
            ],

            '11 digits' => [
                '09123456789',
                400
            ],
        ];
    }


    #[DataProvider('phoneBoundaryProvider')]
    public function testPhoneBoundary(
        string $phone,
        int $expectedCode
    ): void {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => 'phone_test',
                'email' => uniqid() . '@gmail.com',
                'password' => 'Password123',
                'phone' => $phone
            ]);

        self::assertSame(
            $expectedCode,
            $result['code']
        );
    }


    /*
     * ============================
     * EP TESTS
     * ============================
     */


    public function testInvalidEmailFormat(): void
    {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => 'user123',
                'email' => 'abc.com',
                'password' => 'Password123',
                'phone' => '0912345678'
            ]);

        self::assertSame(400, $result['code']);
    }


    public function testEmptyUsername(): void
    {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => '',
                'email' => 'test@gmail.com',
                'password' => 'Password123',
                'phone' => '0912345678'
            ]);

        self::assertSame(400, $result['code']);
    }


    public function testValidRegister(): void
    {
        $repository = $this->successRepository();

        $result = $this->serviceWithRepository($repository)
            ->register([
                'username' => 'valid_user',
                'email' => uniqid() . '@gmail.com',
                'password' => 'Password123',
                'phone' => '0912345678'
            ]);

        self::assertSame(201, $result['code']);
    }
}