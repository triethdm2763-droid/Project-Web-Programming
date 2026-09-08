<?php

declare(strict_types=1);

use App\Controllers\ProductController;
use App\Services\ProductService;
use PHPUnit\Framework\TestCase;

final class ProductControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-product-controller-' . getmypid());
            session_start(['use_cookies' => false, 'cache_limiter' => '']);
        }
        $_SESSION = ['user_id' => 1, 'role' => 'seller'];
        $_GET = [];
        $_FILES = [];
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_FILES = [];
        http_response_code(200);
        header_remove();
    }

    public function testTC_PROD_01_CreateProductSuccess(): void
    {
        $body = [
            'name' => 'Sản phẩm thử nghiệm chuẩn',
            'price' => 150000,
            'description' => 'Mô tả sản phẩm hợp lệ',
            'category_id' => 2,
        ];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('createProduct')->with($body)->willReturn([
            'status' => 'success', 'code' => 201, 'product_id' => 42,
        ]);

        $output = $this->capture(fn() => $this->controllerWith($service, $body)->create());

        self::assertSame(201, http_response_code());
        self::assertSame([
            'message' => 'Đăng bán sản phẩm thành công!',
            'product_id' => 42,
        ], json_decode($output, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testTC_PROD_02_UploadSecurityFailure(): void
    {
        $_FILES['image'] = [
            'name' => 'malware.jpg',
            'type' => 'text/php',
            'tmp_name' => sys_get_temp_dir() . '/missing-malware-file',
            'error' => UPLOAD_ERR_OK,
            'size' => 15_000_000,
        ];

        $output = $this->capture(fn() => $this->controllerWith($this->createMock(ProductService::class))->uploadImage());
        $json = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, http_response_code());
        self::assertArrayHasKey('error', $json);
        self::assertStringContainsString('5MB', $json['error']);
    }

    public function testTC_PROD_03_IDORProtection(): void
    {
        $body = ['id' => 999, 'name' => 'Sản phẩm', 'price' => 1000, 'category_id' => 2];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('updateProduct')->with(999, $body)->willReturn([
            'status' => 'error', 'code' => 403, 'message' => 'Bạn không có quyền chỉnh sửa.',
        ]);

        $output = $this->capture(fn() => $this->controllerWith($service, $body)->update());
        $json = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(403, http_response_code());
        self::assertSame('Bạn không có quyền chỉnh sửa.', $json['error']);
    }

    public function testFR_07_ProductBusinessLogicConstraint(): void
    {
        $body = ['id' => 5];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('deleteProduct')->with(5)->willReturn([
            'status' => 'error', 'code' => 400, 'message' => 'Sản phẩm đã bán, không thể xóa.',
        ]);

        $output = $this->capture(fn() => $this->controllerWith($service, $body)->delete());
        $json = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, http_response_code());
        self::assertSame('Sản phẩm đã bán, không thể xóa.', $json['error']);
    }

    public function testTC_SEARCH_01_FilterAndSearchSuccess(): void
    {
        $_GET = [
            'search' => 'Laptop',
            'category_id' => 3,
            'min_price' => 5_000_000,
            'max_price' => 20_000_000,
        ];
        $products = [['ID' => 10, 'Name' => 'Laptop A', 'Price' => 12_000_000]];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('getActiveProducts')->with($_GET)->willReturn([
            'status' => 'success', 'code' => 200, 'data' => $products,
        ]);

        $output = $this->capture(fn() => $this->controllerWith($service)->list());

        self::assertSame(200, http_response_code());
        self::assertSame($products, json_decode($output, true, 512, JSON_THROW_ON_ERROR));
    }

    private function controllerWith(ProductService $service, array $body = []): ProductController
    {
        $controller = new class($body) extends ProductController {
            public function __construct(private array $body) {}
            protected function getRequestBody(): array { return $this->body; }
        };
        $property = (new \ReflectionClass(ProductController::class))->getProperty('productService');
        $property->setValue($controller, $service);
        return $controller;
    }

    private function capture(callable $action): string
    {
        ob_start();
        $action();
        return (string)ob_get_clean();
    }
}
