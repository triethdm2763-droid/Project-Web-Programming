<?php

declare(strict_types=1);

namespace Tests\ControllerTechniques;

require_once __DIR__ . '/Support/ControllerProbes.php';

use App\Controllers\AdminController;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\ProductService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\AuthControllerProbe;
use Tests\Support\CategoryControllerProbe;
use Tests\Support\NotificationControllerProbe;
use Tests\Support\OrderControllerProbe;
use Tests\Support\ProductControllerProbe;

#[Group('controller')]
#[Group('state-transition')]
final class ControllerStateTransitionTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-controller-state-' . getmypid());
            session_start(['use_cookies' => false, 'cache_limiter' => '']);
        }
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        http_response_code(200);
        header_remove();
    }

    public function testAuthAnonymousToAuthenticatedTransitionCreatesSession(): void
    {
        $user = ['ID' => 7, 'Username' => 'buyer', 'Role' => 'user'];
        $service = $this->createMock(AuthService::class);
        $service->expects(self::once())->method('login')->willReturn([
            'status' => 'success', 'code' => 200, 'user' => $user,
        ]);

        $response = (new AuthControllerProbe($service, ['username' => 'buyer', 'password' => 'Password123']))->login();

        self::assertSame(200, $response['statusCode']);
        self::assertSame(7, $_SESSION['user_id']);
        self::assertSame('buyer', $_SESSION['username']);
        self::assertSame('user', $_SESSION['role']);
    }

    public function testAuthFailedLoginKeepsAnonymousState(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('login')->willReturn([
            'status' => 'error', 'code' => 401, 'message' => 'Sai thông tin đăng nhập.',
        ]);

        $response = (new AuthControllerProbe($service, ['username' => 'buyer', 'password' => 'wrong']))->login();

        self::assertSame(401, $response['statusCode']);
        self::assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testAdminCanTransitionAnotherUserFromActiveToBanned(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        $users = $this->createMock(UserRepository::class);
        $users->expects(self::once())->method('updateStatus')->with(2, 'banned')->willReturn(true);
        $controller = new AdminController(
            $users,
            $this->createMock(OrderRepository::class),
            $this->createMock(ProductRepository::class)
        );

        $response = $controller->updateUserStatus(['id' => 2, 'status' => 'banned']);

        self::assertSame(200, $response['status_code']);
        self::assertTrue($response['body']['success']);
    }

    public function testAdminSelfBanTransitionIsRejectedWithoutMutation(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        $users = $this->createMock(UserRepository::class);
        $users->expects(self::never())->method('updateStatus');
        $controller = new AdminController(
            $users,
            $this->createMock(OrderRepository::class),
            $this->createMock(ProductRepository::class)
        );

        $response = $controller->updateUserStatus(['id' => 1, 'status' => 'banned']);

        self::assertSame(400, $response['status_code']);
    }

    public function testProductPendingToUpdatedTransitionReturnsSuccess(): void
    {
        $body = ['id' => 10, 'name' => 'Tên mới'];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('updateProduct')->with(10, $body)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã cập nhật.',
        ]);

        $response = (new ProductControllerProbe($service, $body))->update();

        self::assertSame(200, $response['statusCode']);
        self::assertSame('Đã cập nhật.', $response['body']['message']);
    }

    public function testOrderPendingToShippingTransitionReturnsSuccess(): void
    {
        $body = ['id' => 15, 'status' => 'shipping'];
        $service = $this->createMock(OrderService::class);
        $service->expects(self::once())->method('updateStatus')->with($body)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã cập nhật trạng thái.',
        ]);

        $response = (new OrderControllerProbe($service, $body))->updateOrderStatus();

        self::assertSame(200, $response['statusCode']);
    }

    public function testInvalidOrderTransitionReturnsConflict(): void
    {
        $body = ['id' => 15, 'status' => 'pending'];
        $service = $this->createMock(OrderService::class);
        $service->method('updateStatus')->willReturn([
            'status' => 'error', 'code' => 409, 'message' => 'Chuyển trạng thái không hợp lệ.',
        ]);

        $response = (new OrderControllerProbe($service, $body))->updateOrderStatus();

        self::assertSame(409, $response['statusCode']);
    }

    public function testNotificationUnreadToReadTransitionReturnsSuccess(): void
    {
        $service = $this->createMock(NotificationService::class);
        $service->expects(self::once())->method('markAsRead')->with(20)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã đánh dấu đã đọc.',
        ]);

        $response = (new NotificationControllerProbe($service, ['id' => 20]))->markRead();

        self::assertSame(200, $response['statusCode']);
    }

    public function testCategoryCrudStateSequenceIsForwardedToService(): void
    {
        $_SESSION['user'] = ['role' => 'admin'];
        $service = new class extends CategoryService {
            /** @var array<int, string> */
            public array $state = [];
            public function __construct() {}
            public function createCategory(array $data): array
            {
                $this->state[5] = $data['name'];
                return ['id' => 5, 'name' => $data['name']];
            }
            public function updateCategory(int $id, array $data): bool
            {
                if (!isset($this->state[$id])) return false;
                $this->state[$id] = $data['name'];
                return true;
            }
            public function deleteCategory(int $id): bool
            {
                if (!isset($this->state[$id])) return false;
                unset($this->state[$id]);
                return true;
            }
        };
        $controller = new CategoryControllerProbe($service);

        $created = $controller->store(['name' => 'Xe']);
        $updated = $controller->update(5, ['name' => 'Đồng hồ']);
        $deleted = $controller->destroy(5);

        self::assertSame('Xe', $created['body']['data']['name']);
        self::assertTrue($updated['body']['success']);
        self::assertTrue($deleted['body']['success']);
        self::assertSame([], $service->state);
    }
}
