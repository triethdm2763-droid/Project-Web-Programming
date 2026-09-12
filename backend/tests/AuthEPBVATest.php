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
    public static function registrationCases(): array
    {
        // One field varies; all other fields stay at a valid baseline.
        $cases = [];
        foreach ([2, 3, 4, 26, 49, 50, 51] as $i => $length) {
            $cases['AUTH-BVA-U' . ($i + 1)] = ['username', str_repeat('a', $length),
                $length < 3 || $length > 50 ? 400 : 201, 'BVA', 'username length ' . $length];
        }
        foreach ([7, 8, 9, 11] as $i => $length) {
            $cases['AUTH-BVA-P' . ($i + 1)] = ['password', str_repeat('p', $length),
                $length < 8 ? 400 : 201, 'BVA', 'password length ' . $length];
        }
        foreach ([9, 10, 11] as $i => $length) {
            $cases['AUTH-BVA-T' . ($i + 1)] = ['phone', '0' . str_repeat('1', $length - 1),
                $length === 10 ? 201 : 400, 'BVA', 'phone length ' . $length];
        }
        return $cases + [
            'AUTH-EP-U1' => ['username', '', 400, 'EP', 'empty'],
            'AUTH-EP-U2' => ['username', null, 400, 'EP', 'missing key'],
            'AUTH-EP-U3' => ['username', '   ', 400, 'EP', 'empty after trim'],
            'AUTH-EP-U4' => ['username', "\u{00E9}\u{00E9}\u{00E9}", 201, 'EP', 'three Unicode characters'],
            'AUTH-EP-U5' => ['username', '  abc  ', 201, 'EP', 'valid after trim'],
            'AUTH-EP-P1' => ['password', '', 400, 'EP', 'empty'],
            'AUTH-EP-P2' => ['password', null, 400, 'EP', 'missing key'],
            'AUTH-EP-P3' => ['password', '        ', 400, 'EP', 'empty after trim'],
            'AUTH-EP-E1' => ['email', 'abc.com', 400, 'EP', 'invalid format'],
            'AUTH-EP-E2' => ['email', '', 400, 'EP', 'empty'],
            'AUTH-EP-E3' => ['email', null, 400, 'EP', 'missing key'],
            'AUTH-EP-E4' => ['email', '   ', 400, 'EP', 'empty after trim'],
            'AUTH-EP-E5' => ['email', '  auth.review@example.com  ', 201, 'EP', 'valid after trim'],
            'AUTH-EP-T1' => ['phone', '', 201, 'EP', 'optional empty'],
            'AUTH-EP-T2' => ['phone', null, 201, 'EP', 'optional missing key'],
            'AUTH-EP-T3' => ['phone', '1912345678', 400, 'EP', 'ten digits with wrong prefix'],
            'AUTH-EP-T4' => ['phone', '09123a5678', 400, 'EP', 'ten characters containing a letter'],
            'AUTH-EP-T5' => ['phone', '   ', 201, 'EP', 'optional empty after trim'],
        ];
    }

    #[DataProvider('registrationCases')]
    public function testRegistrationCase(string $field, ?string $value, int $code, string $technique, string $partition): void
    {
        $data = self::baseline();
        if ($value === null) {
            unset($data[$field]);
        } else {
            $data[$field] = $value;
        }
        $repo = $this->createMock(UserRepository::class);
        if ($code === 400) {
            $repo->expects(self::never())->method('findByUsername');
            $repo->expects(self::never())->method('findByEmail');
            $repo->expects(self::never())->method('create');
        } else {
            $repo->expects(self::once())->method('findByUsername')->with(trim($data['username']))->willReturn(null);
            $repo->expects(self::once())->method('findByEmail')->with(trim($data['email']))->willReturn(null);
            $repo->expects(self::once())->method('create')->with(
                trim($data['username']), trim($data['email']),
                self::callback(fn ($hash): bool => is_string($hash) && $hash !== $data['password']
                    && password_verify($data['password'], $hash)),
                isset($data['phone']) ? trim($data['phone']) : null
            )->willReturn(101);
        }
        $result = $this->service($repo)->register($data);
        self::assertSame($code, $result['code'], $technique . ': ' . $partition);
        self::assertSame($code === 201 ? 'success' : 'error', $result['status']);
        if ($code === 400) {
            self::assertSame([$field], array_keys($result['errors']));
            self::assertNotEmpty($result['errors'][$field]);
        } else {
            self::assertSame(101, $result['user_id']);
            self::assertArrayNotHasKey('errors', $result);
        }
    }

    public static function duplicateCases(): array
    {
        return ['AUTH-EP-D1' => ['username'], 'AUTH-EP-D2' => ['email']];
    }

    #[DataProvider('duplicateCases')]
    public function testDuplicateRejected(string $field): void
    {
        $data = self::baseline();
        $repo = $this->createMock(UserRepository::class);
        $repo->expects(self::once())->method('findByUsername')->with($data['username'])
            ->willReturn($field === 'username' ? ['ID' => 1] : null);
        if ($field === 'email') {
            $repo->expects(self::once())->method('findByEmail')->with($data['email'])->willReturn(['ID' => 2]);
        } else {
            $repo->expects(self::never())->method('findByEmail');
        }
        $repo->expects(self::never())->method('create');
        $result = $this->service($repo)->register($data);
        self::assertSame(409, $result['code']);
        self::assertSame('error', $result['status']);
        self::assertSame([$field], array_keys($result['errors']));
        self::assertNotEmpty($result['errors'][$field]);
    }

    public static function baseline(): array
    {
        return ['username' => 'valid_user', 'email' => 'auth.review@example.com',
            'password' => 'Password123', 'phone' => '0912345678'];
    }

    private function service(UserRepository $repo): AuthService
    {
        $reflection = new ReflectionClass(AuthService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('userRepository')->setValue($service, $repo);
        return $service;
    }
}
