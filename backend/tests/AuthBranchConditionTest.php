<?php

declare(strict_types=1);

namespace Tests;

use App\Services\AuthService;
use App\Repositories\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthBranchConditionTest extends TestCase
{
    public static function decisionCases(): array
    {
        // route, password, status, code, D1..D5 (null = not evaluated).
        return [
            'AUTH-WB-01' => ['validation', 'Password123', 'active', 400, [true, null, null, null, null]],
            'AUTH-WB-02' => ['missing', 'Password123', 'active', 401, [false, true, true, null, null]],
            'AUTH-WB-03' => ['username', 'WrongPassword', 'active', 401, [false, false, false, true, null]],
            'AUTH-WB-04' => ['username', 'Password123', 'banned', 403, [false, false, false, false, true]],
            'AUTH-WB-05' => ['username', 'Password123', 'active', 200, [false, false, false, false, false]],
        ];
    }

    #[DataProvider('decisionCases')]
    public function testDecisionCase(string $route, string $password, string $status, int $code, array $outcomes): void
    {
        $repo = $this->createMock(UserRepository::class);
        $user = ['ID' => 7, 'Username' => 'user01', 'Email' => 'user01@example.com',
            'Role' => 'user', 'Status' => $status, 'Password' => password_hash('Password123', PASSWORD_BCRYPT)];
        if ($route === 'validation') {
            $repo->expects(self::never())->method('findByUsername');
            $repo->expects(self::never())->method('findByEmail');
        } else {
            $repo->expects(self::once())->method('findByUsername')->with('user01')
                ->willReturn($route === 'missing' ? null : $user);
            if ($route === 'missing') {
                $repo->expects(self::once())->method('findByEmail')->with('user01')->willReturn(null);
            } else {
                $repo->expects(self::never())->method('findByEmail');
            }
        }
        $reflection = new ReflectionClass(AuthService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('userRepository')->setValue($service, $repo);
        $result = $service->login(['username' => $route === 'validation' ? '' : 'user01', 'password' => $password]);
        // This metadata documents the source trace; it is not a measured coverage counter.
        self::assertSame($code, $result['code'], json_encode($outcomes));
        self::assertSame($code === 200 ? 'success' : 'error', $result['status']);
        if ($code === 200) {
            unset($user['Password']);
            self::assertSame($user, $result['user']);
            self::assertArrayNotHasKey('Password', $result['user']);
        } else {
            self::assertArrayNotHasKey('user', $result);
            if ($code === 400) {
                self::assertSame(['username'], array_keys($result['errors']));
            } else {
                self::assertNotEmpty($result['message']);
            }
        }
    }
}
