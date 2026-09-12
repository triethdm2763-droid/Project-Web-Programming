<?php

declare(strict_types=1);

namespace Tests\ControllerTechniques;

require_once __DIR__ . '/Support/ControllerProbes.php';

use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\OrderService;
use App\Services\ProductService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\AuthControllerProbe;
use Tests\Support\CategoryControllerProbe;
use Tests\Support\OrderControllerProbe;
use Tests\Support\ProductControllerProbe;

#[Group('controller')]
#[Group('bva')]
final class ControllerStandardBvaTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-controller-techniques-' . getmypid());
            session_start(['use_cookies' => false, 'cache_limiter' => '']);
        }
        $_SESSION = [
            'user_id' => 7,
            'role' => 'seller',
            'user' => ['role' => 'admin'],
        ];
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

    /** Standard BVA: n=1, therefore 4n+1 = 5 cases. */
    #[DataProvider('usernameStandardBoundaries')]
    public function testAuthRegisterUsesFiveStandardUsernameBoundaries(
        string $point,
        int $length
    ): void {
        $payload = [
            'username' => str_repeat('u', $length),
            'email' => 'bva@example.com',
            'password' => 'Password123',
            'phone' => '0912345678',
        ];
        $service = $this->createMock(AuthService::class);
        $service->expects(self::once())->method('register')->with($payload)->willReturn([
            'status' => 'success', 'code' => 201, 'user_id' => 101,
        ]);

        $response = (new AuthControllerProbe($service, $payload))->register();

        self::assertSame(201, $response['statusCode'], $point);
        self::assertSame(101, $response['body']['user_id']);
    }

    /** @return array<string, array{string, int}> */
    public static function usernameStandardBoundaries(): array
    {
        return [
            'Min = 3' => ['Min', 3],
            'Min+ = 4' => ['Min+', 4],
            'Nom = 26' => ['Nom', 26],
            'Max- = 49' => ['Max-', 49],
            'Max = 50' => ['Max', 50],
        ];
    }

    /** Standard BVA: n=1, therefore 4n+1 = 5 cases. */
    #[DataProvider('productNameStandardBoundaries')]
    public function testProductCreateUsesFiveStandardNameBoundaries(
        string $point,
        int $length
    ): void {
        $payload = [
            'name' => str_repeat('p', $length),
            'price' => 100000,
            'category_id' => 2,
            'stock_quantity' => 10,
        ];
        $service = $this->createMock(ProductService::class);
        $service->expects(self::once())->method('createProduct')->with($payload)->willReturn([
            'status' => 'success', 'code' => 201, 'product_id' => 42,
        ]);

        $response = (new ProductControllerProbe($service, $payload))->create();

        self::assertSame(201, $response['statusCode'], $point);
        self::assertSame(42, $response['body']['product_id']);
    }

    /** @return array<string, array{string, int}> */
    public static function productNameStandardBoundaries(): array
    {
        return [
            'Min = 3' => ['Min', 3],
            'Min+ = 4' => ['Min+', 4],
            'Nom = 129' => ['Nom', 129],
            'Max- = 254' => ['Max-', 254],
            'Max = 255' => ['Max', 255],
        ];
    }

    /** Standard BVA against stock=5: n=1, therefore 4n+1 = 5 cases. */
    #[DataProvider('quantityStandardBoundaries')]
    public function testOrderCreateUsesFiveStandardQuantityBoundaries(
        string $point,
        int $quantity
    ): void {
        $payload = [
            'product_id' => 10,
            'shipping_address' => '123 Đường kiểm thử',
            'payment_method' => 'COD',
            'quantity' => $quantity,
        ];
        $service = $this->createMock(OrderService::class);
        $service->expects(self::once())->method('checkout')->with($payload)->willReturn([
            'status' => 'success',
            'code' => 201,
            'order_id' => 500 + $quantity,
            'order_code' => 'ORD-BVA-' . $quantity,
        ]);

        $response = (new OrderControllerProbe($service, $payload))->create();

        self::assertSame(201, $response['statusCode'], $point);
        self::assertSame('ORD-BVA-' . $quantity, $response['body']['order_code']);
    }

    /** @return array<string, array{string, int}> */
    public static function quantityStandardBoundaries(): array
    {
        return [
            'Min = 1' => ['Min', 1],
            'Min+ = 2' => ['Min+', 2],
            'Nom = 3' => ['Nom', 3],
            'Max- = 4' => ['Max-', 4],
            'Max = 5' => ['Max', 5],
        ];
    }

    /** Standard BVA: n=1, therefore 4n+1 = 5 cases. */
    #[DataProvider('categoryNameStandardBoundaries')]
    public function testCategoryStoreUsesFiveStandardNameBoundaries(
        string $point,
        int $length
    ): void {
        $payload = ['name' => str_repeat('c', $length)];
        $service = $this->createMock(CategoryService::class);
        $service->expects(self::once())->method('createCategory')->with($payload)->willReturn([
            'id' => $length,
            'name' => $payload['name'],
        ]);

        $response = (new CategoryControllerProbe($service))->store($payload);

        self::assertSame(201, $response['statusCode'], $point);
        self::assertSame($payload['name'], $response['body']['data']['name']);
    }

    /** @return array<string, array{string, int}> */
    public static function categoryNameStandardBoundaries(): array
    {
        return [
            'Min = 1' => ['Min', 1],
            'Min+ = 2' => ['Min+', 2],
            'Nom = 50' => ['Nom', 50],
            'Max- = 99' => ['Max-', 99],
            'Max = 100' => ['Max', 100],
        ];
    }
}
