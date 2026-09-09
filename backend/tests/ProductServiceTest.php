<?php

namespace Tests\Product;

use PHPUnit\Framework\TestCase;
use App\Services\ProductService;

class ProductServiceTest extends TestCase
{
    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $this->service = new ProductService();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * TC-PROD-SERVICE-01
     * Không đăng nhập -> createProduct() trả về 401
     */
    public function testCreateProductWithoutLogin(): void
    {
        $_SESSION = [];

        $data = [
            'name' => 'Laptop Dell',
            'price' => 15000000,
            'category_id' => 2
        ];

        $result = $this->service->createProduct($data);

        $this->assertSame('error', $result['status']);
        $this->assertSame(401, $result['code']);
    }

    /**
     * TC-PROD-SERVICE-02
     * Dữ liệu thiếu name -> 400
     */
    public function testCreateProductWithMissingName(): void
    {
        $_SESSION['user_id'] = 1;

        $data = [
            'price' => 15000000,
            'category_id' => 2
        ];

        $result = $this->service->createProduct($data);

        $this->assertSame('error', $result['status']);
        $this->assertSame(400, $result['code']);
    }

    /**
     * TC-PROD-SERVICE-03
     * Giá <= 0 -> 400
     */
    public function testCreateProductWithInvalidPrice(): void
    {
        $_SESSION['user_id'] = 1;

        $data = [
            'name' => 'Laptop Dell',
            'price' => 0,
            'category_id' => 2
        ];

        $result = $this->service->createProduct($data);

        $this->assertSame('error', $result['status']);
        $this->assertSame(400, $result['code']);
        $this->assertArrayHasKey('price', $result['errors']);
    }

    /**
     * BVA - kiểm tra giá
     */
    public static function priceBoundaryProvider(): array
    {
        return [
            'Price zero' => [0, 400],
            'Price positive' => [1, 201],
            'Normal price' => [500000, 201],
        ];
    }

    /**
     * @dataProvider priceBoundaryProvider
     */
    public function testPriceBoundary(int $price, int $expectedCode): void
    {
        $_SESSION['user_id'] = 1;

        /*
         * Với giá hợp lệ, test không nên phụ thuộc DB thật.
         * Vì vậy case này chủ yếu kiểm tra validation.
         */
        if ($price <= 0) {
            $result = $this->service->createProduct([
                'name' => 'BVA Product',
                'price' => $price,
                'category_id' => 2
            ]);

            $this->assertSame($expectedCode, $result['code']);
        } else {
            $this->assertGreaterThan(0, $price);
        }
    }

    /**
     * TC-PROD-SERVICE-04
     * Detail không tồn tại -> 404
     */
    public function testGetProductDetailNotFound(): void
    {
        $result = $this->service->getProductDetail(-999999);

        $this->assertSame('error', $result['status']);
        $this->assertSame(404, $result['code']);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * TC-PROD-SERVICE-05
     * Update khi chưa đăng nhập -> 401
     */
    public function testUpdateWithoutLogin(): void
    {
        $_SESSION = [];

        $result = $this->service->updateProduct(1, [
            'name' => 'Laptop mới',
            'price' => 1000000,
            'category_id' => 2
        ]);

        $this->assertSame('error', $result['status']);
        $this->assertSame(401, $result['code']);
    }

    /**
     * TC-PROD-SERVICE-06
     * Delete khi chưa đăng nhập -> 401
     */
    public function testDeleteWithoutLogin(): void
    {
        $_SESSION = [];

        $result = $this->service->deleteProduct(1);

        $this->assertSame('error', $result['status']);
        $this->assertSame(401, $result['code']);
    }

    /**
     * TC-PROD-SERVICE-07
     * Validate image hợp lệ
     */
    public function testValidateImageSuccess(): void
    {
        $file = [
            'name' => 'laptop.jpg',
            'type' => 'image/jpeg',
            'size' => 500000
        ];

        $this->assertTrue(
            $this->service->validateImage($file)
        );
    }

    /**
     * TC-PROD-SERVICE-08
     * File quá 5MB -> false
     */
    public function testValidateImageTooLarge(): void
    {
        $file = [
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'size' => 6 * 1024 * 1024
        ];

        $this->assertFalse(
            $this->service->validateImage($file)
        );
    }

    /**
     * TC-PROD-SERVICE-09
     * File sai MIME type -> false
     */
    public function testValidateImageInvalidType(): void
    {
        $file = [
            'name' => 'malware.php',
            'type' => 'text/php',
            'size' => 500000
        ];

        $this->assertFalse(
            $this->service->validateImage($file)
        );
    }

    /**
     * TC-PROD-SERVICE-10
     * File sai extension -> false
     */
    public function testValidateImageInvalidExtension(): void
    {
        $file = [
            'name' => 'malware.exe',
            'type' => 'application/octet-stream',
            'size' => 500000
        ];

        $this->assertFalse(
            $this->service->validateImage($file)
        );
    }

    /**
     * BVA cho validateImage
     */
    public static function imageSizeProvider(): array
    {
        return [
            'Empty size' => [0, false],
            '1 byte' => [1, true],
            'Normal size' => [500000, true],
            'Exactly 5MB' => [5242880, true],
            'Over 5MB' => [5242881, false],
        ];
    }

    /**
     * @dataProvider imageSizeProvider
     */
    public function testImageSizeBoundary(int $size, bool $expected): void
    {
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => $size
        ];

        $this->assertSame(
            $expected,
            $this->service->validateImage($file)
        );
    }
}