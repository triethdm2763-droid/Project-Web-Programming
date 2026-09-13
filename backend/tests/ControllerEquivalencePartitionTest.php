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
#[Group('equivalence-partitioning')]
final class ControllerEquivalencePartitionTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-controller-techniques-' . getmypid());
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

    public function testAuthRegisterValidPartitionReturnsCreated(): void
    {
        $body = ['username' => 'valid_user', 'email' => 'valid@example.com', 'password' => 'Password123'];
        $service = $this->createMock(AuthService::class);
        $service->expects(self::once())->method('register')->with($body)->willReturn([
            'status' => 'success', 'code' => 201, 'user_id' => 8,
        ]);

        $response = (new AuthControllerProbe($service, $body))->register();

        self::assertSame(201, $response['statusCode']);
        self::assertSame(8, $response['body']['user_id']);
    }

    public function testAuthRegisterInvalidPartitionReturnsValidationErrors(): void
    {
        $body = ['username' => '', 'email' => 'bad', 'password' => 'short'];
        $service = $this->createMock(AuthService::class);
        $service->method('register')->willReturn([
            'status' => 'error', 'code' => 400, 'errors' => ['username' => ['required']],
        ]);

        $response = (new AuthControllerProbe($service, $body))->register();

        self::assertSame(400, $response['statusCode']);
        self::assertArrayHasKey('username', $response['body']['errors']);
    }

    public function testProductDetailMissingIdPartitionDoesNotCallService(): void
    {
        $service = $this->createMock(ProductService::class);
        $service->expects(self::never())->method('getProductDetail');

        $response = (new ProductControllerProbe($service))->detail();

        self::assertSame(400, $response['statusCode']);
    }

    public function testProductDetailExistingIdPartitionReturnsProduct(): void
    {
        $_GET['id'] = '12';
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('getProductDetail')->with(12)->willReturn([
            'status' => 'success', 'code' => 200, 'data' => ['ID' => 12],
        ]);

        $response = (new ProductControllerProbe($service))->detail();

        self::assertSame(200, $response['statusCode']);
        self::assertSame(12, $response['body']['ID']);
    }

    public function testProductDetailUnknownIdPartitionReturnsNotFound(): void
    {
        $_GET['id'] = '999';
        $service = $this->createMock(ProductService::class);
        $service->method('getProductDetail')->willReturn([
            'status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.',
        ]);

        $response = (new ProductControllerProbe($service))->detail();

        self::assertSame(404, $response['statusCode']);
        self::assertSame('Không tìm thấy sản phẩm.', $response['body']['error']);
    }

    public function testCategoryDetailNonPositiveIdPartitionDoesNotCallService(): void
    {
        $_GET['id'] = '0';
        $service = $this->createMock(CategoryService::class);
        $service->expects(self::never())->method('getCategory');

        $response = (new CategoryControllerProbe($service))->detail();

        self::assertSame(400, $response['statusCode']);
    }

    public function testCategoryDetailExistingIdPartitionReturnsCategory(): void
    {
        $_GET['id'] = '2';
        $service = $this->createMock(CategoryService::class);
        $service->expects(self::once())->method('getCategory')->with(2)->willReturn(['ID' => 2, 'Name' => 'Xe']);

        $response = (new CategoryControllerProbe($service))->detail();

        self::assertSame(200, $response['statusCode']);
        self::assertSame('Xe', $response['body']['Name']);
    }

    public function testCategoryDetailUnknownIdPartitionReturnsNotFound(): void
    {
        $_GET['id'] = '999';
        $service = $this->createMock(CategoryService::class);
        $service->method('getCategory')->willThrowException(new \Exception('Danh mục không tồn tại'));

        $response = (new CategoryControllerProbe($service))->detail();

        self::assertSame(404, $response['statusCode']);
    }

    public function testOrderTrackMissingLookupPartitionDoesNotCallService(): void
    {
        $service = $this->createMock(OrderService::class);
        $service->expects(self::never())->method('trackOrder');

        $response = (new OrderControllerProbe($service))->track();

        self::assertSame(400, $response['statusCode']);
    }

    public function testOrderTrackExistingCodePartitionReturnsOrder(): void
    {
        $_GET['code'] = ' ORD-001 ';
        $service = $this->createMock(OrderService::class);
        $service->expects(self::once())->method('trackOrder')->with('ORD-001')->willReturn([
            'status' => 'success', 'code' => 200, 'data' => ['OrderCode' => 'ORD-001'],
        ]);

        $response = (new OrderControllerProbe($service))->track();

        self::assertSame(200, $response['statusCode']);
        self::assertSame('ORD-001', $response['body']['OrderCode']);
    }

    public function testOrderTrackUnknownCodePartitionReturnsNotFound(): void
    {
        $_GET['code'] = 'UNKNOWN';
        $service = $this->createMock(OrderService::class);
        $service->method('trackOrder')->willReturn([
            'status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy đơn hàng.',
        ]);

        $response = (new OrderControllerProbe($service))->track();

        self::assertSame(404, $response['statusCode']);
    }

    public function testNotificationMarkReadMissingIdPartitionDoesNotCallService(): void
    {
        $service = $this->createMock(NotificationService::class);
        $service->expects(self::never())->method('markAsRead');

        $response = (new NotificationControllerProbe($service, []))->markRead();

        self::assertSame(400, $response['statusCode']);
    }

    public function testNotificationMarkReadExistingOwnedIdPartitionReturnsSuccess(): void
    {
        $service = $this->createMock(NotificationService::class);
        $service->expects(self::once())->method('markAsRead')->with(7)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã đọc.',
        ]);

        $response = (new NotificationControllerProbe($service, ['id' => '7']))->markRead();

        self::assertSame(200, $response['statusCode']);
        self::assertSame('Đã đọc.', $response['body']['message']);
    }

    public function testNotificationMarkReadInvalidOwnershipPartitionReturnsForbidden(): void
    {
        $service = $this->createMock(NotificationService::class);
        $service->method('markAsRead')->willReturn([
            'status' => 'error', 'code' => 403, 'message' => 'Không có quyền.',
        ]);

        $response = (new NotificationControllerProbe($service, ['id' => 8]))->markRead();

        self::assertSame(403, $response['statusCode']);
    }

    public function testAdminGuestPartitionIsUnauthorizedWithoutRepositoryCalls(): void
    {
        [$controller, $users] = $this->adminController();
        $users->expects(self::never())->method('findAll');

        $response = $controller->users();

        self::assertSame(401, $response['status_code']);
    }

    public function testAdminNormalUserPartitionIsForbiddenWithoutRepositoryCalls(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'user'];
        [$controller, $users] = $this->adminController();
        $users->expects(self::never())->method('findAll');

        $response = $controller->users();

        self::assertSame(403, $response['status_code']);
    }

    public function testAdminPartitionCanListUsers(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        [$controller, $users] = $this->adminController();
        $users->expects(self::once())->method('findAll')->willReturn([['ID' => 1]]);

        $response = $controller->users();

        self::assertSame(200, $response['status_code']);
        self::assertCount(1, $response['body']);
    }

    /** @return array{AdminController, UserRepository, OrderRepository, ProductRepository} */
    private function adminController(): array
    {
        $users = $this->createMock(UserRepository::class);
        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        return [new AdminController($users, $orders, $products), $users, $orders, $products];
    }
}
