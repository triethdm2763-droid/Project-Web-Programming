<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\AuthController;
use App\Services\AuthService;
use App\Utils\JWT;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthControllerTest extends TestCase
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

    public function testRegisterReturns201ForSuccessfulRegistration(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->expects(self::once())
            ->method('register')
            ->with([
                'username' => 'new_user',
                'email' => 'new@example.com',
                'password' => 'Password123',
            ])
            ->willReturn([
                'status' => 'success',
                'code' => 201,
                'user_id' => 101,
            ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => 'new_user',
            'email' => 'new@example.com',
            'password' => 'Password123',
        ]);

        $response = $controller->register();

        self::assertSame(201, $response['statusCode']);
        self::assertSame(101, $response['body']['user_id']);
        self::assertSame('Đăng ký tài khoản thành công!', $response['body']['message']);
    }

    public function testRegisterReturns409AndCorrectFieldForDuplicateUsername(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('register')->willReturn([
            'status' => 'error',
            'code' => 409,
            'errors' => [
                'username' => ['Tên đăng nhập này đã tồn tại trên hệ thống.'],
            ],
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => 'existing_user',
            'email' => 'new@example.com',
            'password' => 'Password123',
        ]);

        $response = $controller->register();

        self::assertSame(409, $response['statusCode']);
        self::assertArrayHasKey('username', $response['body']['errors']);
    }

    public function testLoginSuccessReturnsJwtAndCreatesSession(): void
    {
        $user = [
            'ID' => 7,
            'Username' => 'buyer_a',
            'Email' => 'buyer@example.com',
            'Role' => 'user',
            'Status' => 'active',
        ];

        $service = $this->createMock(AuthService::class);
        $service->expects(self::once())
            ->method('login')
            ->with([
                'username' => 'buyer_a',
                'password' => 'Password123',
            ])
            ->willReturn([
                'status' => 'success',
                'code' => 200,
                'user' => $user,
            ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => 'buyer_a',
            'password' => 'Password123',
        ]);

        $response = $controller->login();

        self::assertSame(200, $response['statusCode']);
        self::assertSame($user, $response['body']['user']);
        self::assertNotEmpty($response['body']['token']);
        self::assertSame(7, $_SESSION['user_id']);
        self::assertSame('buyer_a', $_SESSION['username']);
        self::assertSame('user', $_SESSION['role']);

        $payload = JWT::decode($response['body']['token']);
        self::assertNotNull($payload);
        self::assertSame(7, $payload['user_id']);
        self::assertSame('buyer_a', $payload['username']);
        self::assertSame('user', $payload['role']);
    }

    public function testLoginReturns401ForWrongPassword(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('login')->willReturn([
            'status' => 'error',
            'code' => 401,
            'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => 'buyer_a',
            'password' => 'wrong',
        ]);

        $response = $controller->login();

        self::assertSame(401, $response['statusCode']);
        self::assertSame(
            'Tên đăng nhập hoặc mật khẩu không chính xác.',
            $response['body']['error']
        );
    }

    public function testLoginReturns400WhenUsernameIsEmpty(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('login')->willReturn([
            'status' => 'error',
            'code' => 400,
            'errors' => ['username' => ['Trường này là bắt buộc.']],
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => '',
            'password' => 'Password123',
        ]);

        $response = $controller->login();

        self::assertSame(400, $response['statusCode']);
        self::assertArrayHasKey('username', $response['body']['errors']);
    }

    public function testLoginReturns403ForBannedAccount(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('login')->willReturn([
            'status' => 'error',
            'code' => 403,
            'message' => 'Tài khoản của bạn hiện đang bị khóa hoặc ngưng hoạt động.',
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'username' => 'buyer_e',
            'password' => 'Password123',
        ]);

        $response = $controller->login();

        self::assertSame(403, $response['statusCode']);
        self::assertStringContainsString('bị khóa', $response['body']['error']);
    }

    public function testRequestResetReturnsOtpOnSuccess(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('requestPasswordReset')->willReturn([
            'status' => 'success',
            'code' => 200,
            'message' => 'Mã OTP khôi phục mật khẩu đã được tạo (Mô phỏng).',
            'otp' => '123456',
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody(['email' => 'buyer@example.com']);

        $response = $controller->requestReset();

        self::assertSame(200, $response['statusCode']);
        self::assertSame('123456', $response['body']['otp']);
    }

    public function testPerformResetReturns400ForWrongOtp(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('resetPassword')->willReturn([
            'status' => 'error',
            'code' => 400,
            'message' => 'Mã OTP không chính xác. Vui lòng thử lại.',
        ]);

        $controller = new TestableAuthController($service);
        $controller->setRequestBody([
            'otp' => '999999',
            'password' => 'NewPassword123',
        ]);

        $response = $controller->performReset();

        self::assertSame(400, $response['statusCode']);
        self::assertStringContainsString('không chính xác', $response['body']['error']);
    }
}

final class TestableAuthController extends AuthController
{
    /** @var array<string, mixed> */
    private array $requestBody = [];

    public function __construct(AuthService $authService)
    {
        $reflection = new ReflectionClass(AuthController::class);
        $property = $reflection->getProperty('authService');
        $property->setAccessible(true);
        $property->setValue($this, $authService);
    }

    /**
     * @param array<string, mixed> $requestBody
     */
    public function setRequestBody(array $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{statusCode: int, body: array<string, mixed>}
     */
    protected function json($data, int $statusCode = 200): array
    {
        return [
            'statusCode' => $statusCode,
            'body' => $data,
        ];
    }
}
