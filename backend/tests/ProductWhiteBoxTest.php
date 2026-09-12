<?php

declare(strict_types=1);

use App\Repositories\ProductRepository;
use App\Services\ProductService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ProductWhiteBoxTest extends TestCase
{
    public static function basisPaths(): array
    {
        // CFG scope: ProductService::createProduct(). Validator is treated as one call node.
        return [
            'PROD-WB-P1 session none then unauthenticated' => [false, false, [], 401, null, null],
            'PROD-WB-P2 session active but unauthenticated' => [true, false, [], 401, null, null],
            'PROD-WB-P3 validation errors' => [true, true, ['name' => ''], 400, 'name', null],
            'PROD-WB-P4 nonpositive price' => [true, true, ['price' => 0], 400, 'price', null],
            'PROD-WB-P5 successful creation with supplied stock' => [true, true, [], 201, null, 10],
            'PROD-WB-P6 successful creation with default stock' => [true, true, ['stock_quantity' => '__UNSET__'], 201, null, 1],
        ];
    }

    #[DataProvider('basisPaths')]
    public function testCreateProductBasisPath(
        bool $startSession,
        bool $authenticated,
        array $changes,
        int $expectedCode,
        ?string $expectedErrorField,
        ?int $expectedStock
    ): void {
        session_save_path(sys_get_temp_dir());
        session_id('product-whitebox-' . getmypid());
        if ($startSession) {
            session_start(['use_cookies' => false, 'cache_limiter' => '']);
        }
        $_SESSION = $authenticated ? ['user_id' => 7, 'role' => 'seller'] : [];

        $data = ['name' => 'Valid product', 'price' => 50000, 'category_id' => 1, 'stock_quantity' => 10];
        foreach ($changes as $field => $value) {
            if ($value === '__UNSET__') {
                unset($data[$field]);
            } else {
                $data[$field] = $value;
            }
        }

        $repository = $this->createMock(ProductRepository::class);
        if ($expectedCode === 201) {
            $repository->expects(self::once())->method('create')
                ->with(self::callback(function (array $stored) use ($expectedStock): bool {
                    self::assertSame('pending', $stored['status']);
                    self::assertSame($expectedStock, $stored['stock_quantity']);
                    return true;
                }))
                ->willReturn(55);
        } else {
            $repository->expects(self::never())->method('create');
        }

        $result = $this->serviceWith($repository)->createProduct($data);

        self::assertSame($expectedCode, $result['code']);
        self::assertSame($expectedCode === 201 ? 'success' : 'error', $result['status']);
        if ($expectedErrorField !== null) {
            self::assertArrayHasKey($expectedErrorField, $result['errors']);
        }
        if ($expectedCode === 201) {
            self::assertSame(55, $result['product_id']);
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header_remove();
        http_response_code(200);
    }

    private function serviceWith(ProductRepository $repository): ProductService
    {
        $reflection = new \ReflectionClass(ProductService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('productRepository')->setValue($service, $repository);
        return $service;
    }
}
