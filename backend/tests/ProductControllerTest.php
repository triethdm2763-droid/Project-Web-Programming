<?php

use PHPUnit\Framework\TestCase;
use App\controllers\ProductController;

class ProductControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mồi sẵn phiên đăng nhập mặc định để vượt qua bộ lọc Auth cơ bản
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = 1; // Giả lập User A (ID = 1)
        $_SESSION['role'] = 'seller';
    }

    /**
     * TC-PROD-01: Đăng sản phẩm thành công khi dữ liệu hợp lệ và hình ảnh lưu đúng thư mục
     */
    public function testTC_PROD_01_CreateProductSuccess()
    {
        $_POST = [
            'name' => 'Sản phẩm thử nghiệm chuẩn',
            'price' => 150000,
            'description' => 'Mô tả sản phẩm hợp lệ',
            'category_id' => 2
        ];
        
        // Giả lập file ảnh gửi lên hợp lệ
        $_FILES['image'] = [
            'name' => 'phone.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/phpYzd95x',
            'error' => 0,
            'size' => 500000 // 500KB
        ];

        ob_start();
        $controller = new ProductController();
        
        try {
            $controller->create(); // Hàm đăng bán sản phẩm
        } catch (\Throwable $e) {}
        
        $output = ob_get_clean();
        
        // Khẳng định: Hệ thống phải phản hồi thành công hoặc in ra cấu trúc dữ liệu tạo mới
        $this->assertJson($output);
        $this->assertStringNotContainsString('error', $output);
    }

    /**
     * TC-PROD-02 & TC-FILE-01: Bảo mật Upload - Từ chối tệp ảnh sai định dạng, vượt dung lượng, đổi đuôi giả mạo
     */
    public function testTC_PROD_02_UploadSecurityFailure()
    {
        // Kịch bản gửi file ảnh giả mạo (.script đổi đuôi thành .jpg)
        $_FILES['image'] = [
            'name' => 'malware.jpg',
            'type' => 'text/php', // Định dạng thực tế bị phát hiện là mã độc
            'tmp_name' => '/tmp/phpMalware',
            'error' => 0,
            'size' => 15000000 // Vượt dung lượng cho phép (>10MB)
        ];

        ob_start();
        $controller = new ProductController();
        
        try {
            $controller->uploadImage();
        } catch (\Throwable $e) {}
        
        $output = ob_get_clean();
        
        // Khẳng định: Hệ thống bắt buộc phải từ chối và trả về thông báo lỗi bảo mật
        $this->assertJson($output);
        $this->assertStringContainsString('error', $output);
    }

    /**
     * TC-PROD-03: Bảo mật IDOR - Kiểm tra User A thao tác sửa/xóa sản phẩm của User B phải bị từ chối (403)
     */
    public function testTC_PROD_03_IDORProtection()
    {
        $_SESSION['user_id'] = 1; // User A đang đăng nhập
        $_POST['id'] = 999;       // ID sản phẩm thuộc quyền sở hữu của User B (ID = 2)

        ob_start();
        $controller = new ProductController();
        
        try {
            $controller->update(); // Hoặc hàm delete() tùy nhóm đặt tên
        } catch (\Throwable $e) {}
        
        $output = ob_get_clean();

        // Khẳng định: Hệ thống phải từ chối quyền truy cập (mã lỗi hoặc chữ từ chối/403)
        $this->assertJson($output);
        $this->assertStringContainsString('error', $output);
    }

    /**
     * FR-07 & FR-08: Ràng buộc logic - Chỉ cho phép xóa/sửa sản phẩm khi chưa có đơn hàng/giao dịch
     */
    public function testFR_07_ProductBusinessLogicConstraint()
    {
        $_POST['id'] = 5; // Giả lập sản phẩm ID số 5 đã có người đặt mua trong DB

        ob_start();
        $controller = new ProductController();
        
        try {
            $controller->delete();
        } catch (\Throwable $e) {}
        
        $output = ob_get_clean();

        // Khẳng định: Hệ thống chặn lại không cho xóa và báo lỗi ràng buộc
        $this->assertJson($output);
        $this->assertStringContainsString('error', $output);
    }

    /**
     * TC-SEARCH-01: Tìm kiếm và Lọc - Kiểm tra kết quả trả về đúng khi tìm theo từ khóa, danh mục, khoảng giá, tình trạng
     */
    public function testTC_SEARCH_01_FilterAndSearchSuccess()
    {
        // Giả lập các tham số lọc gửi lên từ URL
        $_GET['search'] = 'Laptop';
        $_GET['category_id'] = 3;
        $_GET['min_price'] = 5000000;
        $_GET['max_price'] = 20000000;
        $_GET['status'] = 'active';

        ob_start();
        $controller = new ProductController();
        
        try {
            $controller->list(); // Hàm hiển thị danh sách sản phẩm kèm bộ lọc
        } catch (\Throwable $e) {}
        
        $output = ob_get_clean();

        // Khẳng định: Hệ thống trả về danh sách sản phẩm dạng JSON khớp bộ lọc
        $this->assertJson($output);
        $this->assertStringContainsString('data', $output);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        unset($_SESSION['user_id']);
        unset($_SESSION['role']);
        parent::tearDown();
    }
}
                