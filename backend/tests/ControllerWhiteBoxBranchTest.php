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
#[Group('whitebox')]
final class ControllerWhiteBoxBranchTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-controller-whitebox-' . getmypid());
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

    public function testAuthMeCoversSuccessAndErrorBranches(): void
    {
        $success = $this->createMock(AuthService::class);
        $success->method('getCurrentUser')->willReturn([
            'status' => 'success', 'code' => 200, 'user' => ['ID' => 7],
        ]);
        $error = $this->createMock(AuthService::class);
        $error->method('getCurrentUser')->willReturn([
            'status' => 'error', 'code' => 401, 'message' => 'Chưa đăng nhập.',
        ]);

        self::assertSame(7, (new AuthControllerProbe($success))->me()['body']['user']['ID']);
        self::assertSame(401, (new AuthControllerProbe($error))->me()['statusCode']);
    }

    public function testAuthResetActionsCoverSuccessAndErrorBranches(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->method('requestPasswordReset')->willReturn([
            'status' => 'error', 'code' => 404, 'message' => 'Email không tồn tại.',
        ]);
        $request = (new AuthControllerProbe($service, ['email' => 'missing@example.com']))->requestReset();

        $resetService = $this->createMock(AuthService::class);
        $resetService->method('resetPassword')->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã đổi mật khẩu.',
        ]);
        $reset = (new AuthControllerProbe($resetService, ['otp' => '123456', 'password' => 'NewPassword123']))->performReset();

        self::assertSame(404, $request['statusCode']);
        self::assertSame(200, $reset['statusCode']);
    }

    public function testAuthUpdateProfileCoversAuthenticationAndServiceBranches(): void
    {
        $service = $this->createMock(AuthService::class);
        $service->expects(self::never())->method('updateProfile');
        self::assertSame(401, (new AuthControllerProbe($service))->updateProfile()['statusCode']);

        $_SESSION['user_id'] = 7;
        $_POST = ['fullname' => 'Người dùng'];
        $success = $this->createMock(AuthService::class);
        $success->expects(self::once())->method('updateProfile')->with(7, $_POST)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã cập nhật.',
        ]);
        self::assertSame(200, (new AuthControllerProbe($success))->updateProfile()['statusCode']);

        $error = $this->createMock(AuthService::class);
        $error->method('updateProfile')->willReturn([
            'status' => 'error', 'code' => 400, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => ['fullname' => ['invalid']],
        ]);
        self::assertSame(400, (new AuthControllerProbe($error))->updateProfile()['statusCode']);
    }

    public function testAuthAvatarValidationCoversUploadErrorExtensionAndSizeBranches(): void
    {
        $_SESSION['user_id'] = 7;
        $service = $this->createMock(AuthService::class);
        $service->expects(self::never())->method('updateProfile');

        $_FILES['avatar'] = ['error' => UPLOAD_ERR_PARTIAL, 'name' => 'a.jpg', 'size' => 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new AuthControllerProbe($service))->updateProfile()['statusCode']);

        $_FILES['avatar'] = ['error' => UPLOAD_ERR_OK, 'name' => 'a.exe', 'size' => 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new AuthControllerProbe($service))->updateProfile()['statusCode']);

        $_FILES['avatar'] = ['error' => UPLOAD_ERR_OK, 'name' => 'a.jpg', 'size' => 2 * 1024 * 1024 + 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new AuthControllerProbe($service))->updateProfile()['statusCode']);
    }

    public function testProductListCoversSellerAndPublicPaginationBranches(): void
    {
        $_GET = ['status' => 'pending'];
        $seller = $this->createMock(ProductService::class);
        $seller->expects(self::once())->method('getSellerProducts')->with('pending')->willReturn([
            'status' => 'error', 'code' => 401, 'message' => 'Chưa đăng nhập.',
        ]);
        self::assertSame(401, (new ProductControllerProbe($seller))->list()['statusCode']);

        $_GET = ['category_id' => '3', 'search' => 'xe', 'min_price' => '10', 'max_price' => '100', 'sort' => 'price_asc', 'location' => 'HN', 'condition_status' => 'new', 'limit' => '10', 'page' => '0'];
        $expected = $_GET;
        unset($expected['page']);
        $expected['limit'] = 10;
        $expected['offset'] = 0;
        $public = $this->createMock(ProductService::class);
        $public->expects(self::once())->method('getActiveProducts')->with($expected)->willReturn([
            'status' => 'success', 'code' => 200, 'data' => [['ID' => 1]], 'total' => 1,
        ]);
        $response = (new ProductControllerProbe($public))->list();
        self::assertSame(1, $response['body']['total']);
    }

    public function testProductMineAndSellerStatsCoverSuccessAndErrorBranches(): void
    {
        $_GET['status'] = 'active';
        $mine = $this->createMock(ProductService::class);
        $mine->expects(self::once())->method('getMyProducts')->with('active')->willReturn([
            'status' => 'success', 'code' => 200, 'data' => [['ID' => 1]],
        ]);
        self::assertSame(200, (new ProductControllerProbe($mine))->mine()['statusCode']);

        $stats = $this->createMock(ProductService::class);
        $stats->method('getSellerStats')->willReturn([
            'status' => 'error', 'code' => 401, 'message' => 'Chưa đăng nhập.',
        ]);
        self::assertSame(401, (new ProductControllerProbe($stats))->sellerStats()['statusCode']);
    }

    public function testProductUpdateAndDeleteCoverMissingAndResultBranches(): void
    {
        $never = $this->createMock(ProductService::class);
        $never->expects(self::never())->method('updateProduct');
        self::assertSame(400, (new ProductControllerProbe($never, []))->update()['statusCode']);

        $delete = $this->createMock(ProductService::class);
        $delete->expects(self::once())->method('deleteProduct')->with(9)->willReturn([
            'status' => 'success', 'code' => 200, 'message' => 'Đã xóa.',
        ]);
        self::assertSame(200, (new ProductControllerProbe($delete, ['id' => 9]))->delete()['statusCode']);
    }

    public function testProductUploadCoversEarlyWhiteBoxBranches(): void
    {
        $service = $this->createMock(ProductService::class);
        self::assertSame(401, (new ProductControllerProbe($service))->uploadImage()['statusCode']);

        $_SESSION['user_id'] = 7;
        self::assertSame(400, (new ProductControllerProbe($service))->uploadImage()['statusCode']);

        $_FILES['image'] = ['error' => UPLOAD_ERR_PARTIAL, 'name' => 'a.jpg', 'size' => 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new ProductControllerProbe($service))->uploadImage()['statusCode']);

        $_FILES['image'] = ['error' => UPLOAD_ERR_OK, 'name' => 'a.exe', 'size' => 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new ProductControllerProbe($service))->uploadImage()['statusCode']);

        $_FILES['image'] = ['error' => UPLOAD_ERR_OK, 'name' => 'a.jpg', 'size' => 5 * 1024 * 1024 + 1, 'tmp_name' => 'none'];
        self::assertSame(400, (new ProductControllerProbe($service))->uploadImage()['statusCode']);
    }

    public function testOrderActionsCoverSuccessAndErrorResponseBranches(): void
    {
        $cancelOk = $this->createMock(OrderService::class);
        $cancelOk->method('cancelOrder')->willReturn(['status' => 'success', 'code' => 200]);
        self::assertSame(200, (new OrderControllerProbe($cancelOk, ['id' => 1]))->cancel()['statusCode']);

        $cancelError = $this->createMock(OrderService::class);
        $cancelError->method('cancelOrder')->willReturn(['status' => 'error', 'code' => 409, 'message' => 'Không thể hủy.']);
        self::assertSame(409, (new OrderControllerProbe($cancelError, ['id' => 1]))->cancel()['statusCode']);

        $buyer = $this->createMock(OrderService::class);
        $buyer->method('getBuyerHistory')->willReturn(['status' => 'success', 'code' => 200, 'data' => []]);
        self::assertSame(200, (new OrderControllerProbe($buyer))->buyerOrders()['statusCode']);

        $seller = $this->createMock(OrderService::class);
        $seller->method('getSellerOrders')->willReturn(['status' => 'error', 'code' => 401, 'message' => 'Chưa đăng nhập.']);
        self::assertSame(401, (new OrderControllerProbe($seller))->sellerOrders()['statusCode']);
    }

    public function testNotificationListCoversBothBranches(): void
    {
        $success = $this->createMock(NotificationService::class);
        $success->method('getMyNotifications')->willReturn(['status' => 'success', 'code' => 200, 'data' => [['ID' => 1]]]);
        $error = $this->createMock(NotificationService::class);
        $error->method('getMyNotifications')->willReturn(['status' => 'error', 'code' => 401, 'message' => 'Chưa đăng nhập.']);

        self::assertSame(200, (new NotificationControllerProbe($success))->list()['statusCode']);
        self::assertSame(401, (new NotificationControllerProbe($error))->list()['statusCode']);
    }

    public function testCategoryWhiteBoxCoversListUpdateAndDestroyBranches(): void
    {
        $service = $this->createMock(CategoryService::class);
        $service->method('getAllCategories')->willReturn([['ID' => 1]]);
        self::assertSame(200, (new CategoryControllerProbe($service))->list()['statusCode']);

        $_SESSION['user'] = ['role' => 'admin'];
        $service->expects(self::once())->method('updateCategory')->with(1, ['name' => 'Mới'])->willReturn(true);
        $service->expects(self::once())->method('deleteCategory')->with(1)->willReturn(false);
        $controller = new CategoryControllerProbe($service);
        self::assertTrue($controller->update(1, ['name' => 'Mới'])['body']['success']);
        self::assertFalse($controller->destroy(1)['body']['success']);
    }

    public function testAdminWhiteBoxCoversListsFiltersAndUpdateProductStatus(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        $users = $this->createMock(UserRepository::class);
        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $orders->expects(self::once())->method('findAll')->willReturn([['ID' => 1]]);
        $users->expects(self::once())->method('getWallets')->willReturn([['UserID' => 1]]);
        $products->expects(self::once())->method('findAllForAdmin')->with(['search' => 'xe', 'status' => 'pending'])->willReturn([]);
        $products->expects(self::once())->method('updateStatus')->with(9, 'active')->willReturn(true);
        $controller = new AdminController($users, $orders, $products);

        self::assertSame(200, $controller->orders()['status_code']);
        self::assertSame(200, $controller->wallets()['status_code']);
        self::assertSame(200, $controller->products(['search' => 'xe', 'status' => 'pending'])['status_code']);
        self::assertTrue($controller->updateProductStatus(['id' => 9, 'status' => 'active'])['body']['success']);
        self::assertSame(400, $controller->updateProductStatus([])['status_code']);
    }
}
