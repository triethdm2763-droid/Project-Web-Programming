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
final class ProductServiceTest extends TestCase
{
    protected function setUp(): void
    {
        session_save_path(sys_get_temp_dir());
        session_id('product-ep-bva-' . getmypid());
        session_start(['use_cookies' => false, 'cache_limiter' => '']);
        $_SESSION = ['user_id' => 7, 'role' => 'seller'];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header_remove();
        http_response_code(200);
    }

    public static function createProductCases(): array
    {
        return [
            // Robust BVA for name length: min=3, max=255, one field varied at a time.
            'PROD-BVA-N1 name min-1' => [['name' => str_repeat('n', 2)], 400, 'name', null, 'BVA'],
            'PROD-BVA-N2 name min' => [['name' => str_repeat('n', 3)], 201, null, null, 'BVA'],
            'PROD-BVA-N3 name min+1' => [['name' => str_repeat('n', 4)], 201, null, null, 'BVA'],
            'PROD-BVA-N4 name nominal' => [['name' => str_repeat('n', 20)], 201, null, null, 'BVA'],
            'PROD-BVA-N5 name max-1' => [['name' => str_repeat('n', 254)], 201, null, null, 'BVA'],
            'PROD-BVA-N6 name max' => [['name' => str_repeat('n', 255)], 201, null, null, 'BVA'],
            'PROD-BVA-N7 name max+1' => [['name' => str_repeat('n', 256)], 400, 'name', null, 'BVA'],

            // Price has only the source boundary price > 0. DECIMAL(15,2) motivates epsilon=0.01.
            'PROD-BVA-P1 price below boundary' => [['price' => -0.01], 400, 'price', null, 'BVA'],
            'PROD-BVA-P2 price boundary' => [['price' => 0], 400, 'price', null, 'BVA'],
            'PROD-BVA-P3 price above boundary' => [['price' => 0.01], 201, null, null, 'BVA'],
//'PROD-BVA-P4 price nominal' => [['price' => 50000], 201, null, null, 'BVA'],

            // EP cases follow current Validator/createProduct behavior.
            'PROD-EP-N1 name missing' => [['name' => '__UNSET__'], 400, 'name', null, 'EP'],
            'PROD-EP-N2 name whitespace' => [['name' => '   '], 400, 'name', null, 'EP'],
            'PROD-EP-P1 price missing' => [['price' => '__UNSET__'], 400, 'price', null, 'EP'],
            'PROD-EP-P2 price whitespace' => [['price' => '   '], 400, 'price', null, 'EP'],
            'PROD-EP-P3 price nonnumeric' => [['price' => 'abc'], 400, 'price', null, 'EP'],
            'PROD-EP-C1 category missing' => [['category_id' => '__UNSET__'], 400, 'category_id', null, 'EP'],
            'PROD-EP-C2 category whitespace' => [['category_id' => '   '], 400, 'category_id', null, 'EP'],

            // Characterization cases: source accepts them; the report records validation gaps.
            'PROD-GAP-C1 category nonnumeric becomes zero' => [['category_id' => 'abc'], 201, null, null, 'GAP'],
            'PROD-GAP-C2 category existence not checked' => [['category_id' => 9999], 201, null, null, 'GAP'],
            'PROD-GAP-S1 stock missing defaults to one' => [['stock_quantity' => '__UNSET__'], 201, null, 1, 'GAP'],
            'PROD-GAP-S2 stock zero accepted' => [['stock_quantity' => 0], 201, null, 0, 'GAP'],
            'PROD-GAP-S3 stock negative accepted' => [['stock_quantity' => -1], 201, null, -1, 'GAP'],
            'PROD-GAP-S4 stock nonnumeric becomes zero' => [['stock_quantity' => 'abc'], 201, null, 0, 'GAP'],
        ];
    }

    #[DataProvider('createProductCases')]
    public function testCreateProductEpBvaAgainstProductionSource(
        array $changes,
        int $expectedCode,
        ?string $expectedErrorField,
        ?int $expectedStock,
        string $technique
    ): void {
        $data = [
            'name' => str_repeat('n', 20),
            'price' => 50000,
            'category_id' => 1,
            'stock_quantity' => 10,
        ];
        foreach ($changes as $field => $value) {
            if ($value === '__UNSET__') {
                unset($data[$field]);
            } else {
                $data[$field] = $value;
            }
        }

        $repository = $this->createMock(ProductRepository::class);
        if ($expectedCode === 201) {
            $repository->expects(self::once())
                ->method('create')
                ->with(self::callback(function (array $stored) use ($data, $expectedStock): bool {
                    self::assertSame(trim((string)$data['name']), $stored['name']);
                    self::assertSame((float)$data['price'], $stored['price']);
                    self::assertSame((int)$data['category_id'], $stored['category_id']);
                    self::assertSame(7, $stored['seller_id']);
                    self::assertSame('pending', $stored['status']);
                    $stock = $expectedStock ?? (isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 1);
                    self::assertSame($stock, $stored['stock_quantity']);
                    return true;
                }))
                ->willReturn(91);
        } else {
            $repository->expects(self::never())->method('create');
        }

        $result = $this->serviceWith($repository)->createProduct($data);

        self::assertSame($expectedCode, $result['code'], $technique . ' case returned an unexpected code.');
        self::assertSame($expectedCode === 201 ? 'success' : 'error', $result['status']);
        if ($expectedErrorField !== null) {
            self::assertArrayHasKey('errors', $result);
            self::assertArrayHasKey($expectedErrorField, $result['errors']);
            self::assertNotEmpty($result['errors'][$expectedErrorField]);
        } else {
            self::assertSame(91, $result['product_id']);
        }
    }

    private function serviceWith(ProductRepository $repository): ProductService
    {
        $reflection = new \ReflectionClass(ProductService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('productRepository')->setValue($service, $repository);
        return $service;
    }
}
