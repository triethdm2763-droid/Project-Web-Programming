<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Controllers\AdminController;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class AdminControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Xóa Session trước mỗi bài test
        $_SESSION = [];
    }

    /**
     * TC-ADMIN-01: Chưa đăng nhập -> Trả về 401
     */
    public function test_unauthenticated_user_access_returns_401()
    {
        $controller = new AdminController();
        $response = $controller->users();

        $this->assertEquals(401, $response['status_code']);
        $this->assertFalse($response['body']['success']);
    }

    /**
     * TC-ADMIN-01: User thường (role = 'user') gọi API Admin -> Bị chặn 403
     */
    public function test_normal_user_access_returns_403()
    {
        $_SESSION['user_id'] = 10;
        $_SESSION['role'] = 'user';

        $controller = new AdminController();
        $response = $controller->users();

        $this->assertEquals(403, $response['status_code']);
        $this->assertFalse($response['body']['success']);
    }

    /**
     * TC-ADMIN-02: Admin xem thống kê Dashboard (Reports)
     */
    public function test_admin_can_get_dashboard_reports()
    {
        // 1. Giả lập Admin đăng nhập thành công
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // 2. Mock các Repository để không gọi vào DB thật
        $userRepoMock = $this->createMock(UserRepository::class);
        $userRepoMock->method('findAll')->willReturn([['id' => 1], ['id' => 2]]);

        $productRepoMock = $this->createMock(ProductRepository::class);
        $productRepoMock->method('findAllActive')->willReturn([['id' => 10]]);

        $orderRepoMock = $this->createMock(OrderRepository::class);
        $orderRepoMock->method('findAll')->willReturn([]);

        // 3. Inject Mocks vào Controller
        $controller = new AdminController($userRepoMock, $orderRepoMock, $productRepoMock);

        // 4. Gọi hàm và Assert kết quả
        $response = $controller->reports();

        $this->assertEquals(200, $response['status_code']);
        $this->assertEquals(1, $response['body']['products']);
        $this->assertEquals(2, $response['body']['users']);
        $this->assertEquals(0, $response['body']['orders']);
    }

    /**
     * Quản lý hệ thống: Admin không được phép tự khóa chính mình (HTTP 400)
     */
    public function test_admin_cannot_ban_themselves()
    {
        $_SESSION['user_id'] = 1; // Admin ID là 1
        $_SESSION['role'] = 'admin';

        $controller = new AdminController();

        // Giả lập gửi payload khóa chính ID 1
        $response = $controller->updateUserStatus([
            'id' => 1,
            'status' => 'banned'
        ]);

        $this->assertEquals(400, $response['status_code']);
        $this->assertEquals('Không thể tự khóa tài khoản của chính mình.', $response['body']['message']);
    }
}