<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Utils\JWT;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AuthStateTransitionTest extends TestCase
{
    protected function setUp(): void
    {
        session_save_path(sys_get_temp_dir());
        session_id('auth-st-' . getmypid());
        session_start(['use_cookies' => false, 'cache_limiter' => '']);
        $_SESSION = [];
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header_remove();
        http_response_code(200);
    }

    public static function transitions(): array
    {
        // Session model: G = no user_id; A = authenticated session for user 7.
        return [
            'AUTH-ST-01' => ['G', 'login-valid', 'A', 200],
            'AUTH-ST-02' => ['G', 'login-wrong', 'G', 401],
            'AUTH-ST-03' => ['G', 'login-banned', 'G', 403],
            'AUTH-ST-04' => ['G', 'login-empty', 'G', 400],
            'AUTH-ST-05' => ['A', 'logout', 'G', 200],
            'AUTH-ST-06' => ['A', 'me', 'A', 200],
            'AUTH-ST-07' => ['G', 'me', 'G', 401],
            'AUTH-ST-08' => ['G', 'register-valid', 'G', 201],
            'AUTH-ST-09' => ['G', 'register-invalid', 'G', 400],
            'AUTH-ST-10' => ['A', 'session-lost', 'G', 401],
        ];
    }

    #[DataProvider('transitions')]
    public function testTransition(string $start, string $event, string $end, int $code): void
    {
        $user = ['ID' => 7, 'Username' => 'user01', 'Email' => 'user01@example.com',
            'Role' => 'user', 'Status' => $event === 'login-banned' ? 'banned' : 'active',
            'Password' => password_hash('Password123', PASSWORD_BCRYPT)];
        $repo = $this->createMock(UserRepository::class);
        $register = str_starts_with($event, 'register');
        $lookUp = !in_array($event, ['login-empty', 'register-invalid'], true)
            && (str_starts_with($event, 'login') || $start === 'A' || $register);
        $repo->expects($lookUp ? self::once() : self::never())->method('findByUsername')
            ->with('user01')->willReturn($register ? null : $user);
        $repo->expects($event === 'register-valid' ? self::once() : self::never())->method('findByEmail')
            ->with('user01@example.com')->willReturn(null);
        if ($event === 'register-valid') {
            $repo->expects(self::once())->method('create')->with('user01', 'user01@example.com',
                self::callback(fn ($hash): bool => password_verify('Password123', $hash)), null)->willReturn(77);
        } else {
            $repo->expects(self::never())->method('create');
        }
        $profile = $user;
        unset($profile['Password']);
        $repo->expects($end === 'A' ? self::once() : self::never())->method('findById')
            ->with(7)->willReturn($profile);
        $r = new ReflectionClass(AuthService::class);
        $service = $r->newInstanceWithoutConstructor();
        $r->getProperty('userRepository')->setValue($service, $repo);
        $controller = new AuthTransitionController($service);
        $valid = ['username' => 'user01', 'password' => 'Password123'];
        self::assertSame('G', $this->state());
        if ($start === 'A') {
            $controller->body = $valid;
            $initial = $controller->login();
            self::assertSame(200, $initial['code']);
            $this->assertIdentity($initial);
        }
        self::assertSame($start, $this->state());
        $controller->body = $valid;
        switch ($event) {
            case 'login-empty':
                $controller->body['username'] = '';
                $response = $controller->login();
                self::assertSame(['username'], array_keys($response['body']['errors']));
                break;
            case 'login-wrong':
                $controller->body['password'] = 'WrongPassword';
                $response = $controller->login();
                break;
            case 'login-valid':
            case 'login-banned':
                $response = $controller->login();
                break;
            case 'logout':
                $response = $controller->logout();
                self::assertSame(PHP_SESSION_NONE, session_status());
                break;
            case 'register-valid':
            case 'register-invalid':
                $controller->body['email'] = 'user01@example.com';
                if ($event === 'register-invalid') {
                    $controller->body['username'] = '';
                }
                $response = $controller->register();
                if ($code === 201) {
                    self::assertSame(77, $response['body']['user_id']);
                } else {
                    self::assertSame(['username'], array_keys($response['body']['errors']));
                }
                break;
            case 'session-lost':
                // External session loss is simulated; automatic timeout is outside this test.
                $_SESSION = [];
                session_destroy();
                $response = $controller->me();
                break;
            default:
                $response = $controller->me();
        }
        self::assertSame($code, $response['code']);
        self::assertSame($end, $this->state());
        if ($event === 'login-valid') {
            $this->assertIdentity($response);
        } elseif (str_starts_with($event, 'login')) {
            self::assertArrayNotHasKey('token', $response['body']);
        }
        $access = in_array($event, ['me', 'session-lost'], true) ? $response : $controller->me();
        self::assertSame($end === 'A' ? 200 : 401, $access['code']);
        if ($end === 'A') {
            self::assertSame($profile, $access['body']['user']);
        } else {
            self::assertSame([], $_SESSION);
            self::assertArrayNotHasKey('user', $access['body']);
        }
    }

    private function state(): string
    {
        return empty($_SESSION['user_id']) ? 'G' : 'A';
    }

    private function assertIdentity(array $response): void
    {
        self::assertSame(7, $_SESSION['user_id']);
        self::assertSame('user01', $_SESSION['username']);
        self::assertSame('user', $_SESSION['role']);
        self::assertArrayNotHasKey('Password', $response['body']['user']);
        $payload = JWT::decode($response['body']['token']);
        self::assertNotNull($payload);
        self::assertSame(7, $payload['user_id']);
        self::assertSame('user01', $payload['username']);
        self::assertSame('user', $payload['role']);
    }
}

final class AuthTransitionController extends AuthController
{
    public array $body = [];

    public function __construct(AuthService $service)
    {
        (new ReflectionClass(AuthController::class))->getProperty('authService')->setValue($this, $service);
    }

    protected function getRequestBody(): array
    {
        return $this->body;
    }

    protected function json($data, int $statusCode = 200): array
    {
        return ['code' => $statusCode, 'body' => $data];
    }
}
