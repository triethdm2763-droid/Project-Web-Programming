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
            // Standard BVA 4n+1, n=2 (name length and price): one nominal case
            // plus min, min+, max-, max for each variable. All other fields stay nominal.
            'PROD-BVA-01 all nominal' => [[], 201, null, null, 'BVA', true],
            'PROD-BVA-02 name min' => [['name' => str_repeat('n', 3)], 201, null, null, 'BVA', true],
            'PROD-BVA-03 name min+' => [['name' => str_repeat('n', 4)], 201, null, null, 'BVA', true],
            'PROD-BVA-04 name max-' => [['name' => str_repeat('n', 254)], 201, null, null, 'BVA', true],
            'PROD-BVA-05 name max' => [['name' => str_repeat('n', 255)], 201, null, null, 'BVA', true],
            'PROD-BVA-06 price min' => [['price' => 0.01], 201, null, null, 'BVA', true],
            'PROD-BVA-07 price min+' => [['price' => 0.02], 201, null, null, 'BVA', true],
            'PROD-BVA-08 price max-' => [['price' => 9999999999999.98], 201, null, null, 'BVA', true],
            'PROD-BVA-09 price max' => [['price' => 9999999999999.99], 201, null, null, 'BVA', true],

            // Invalid equivalence classes sit outside Standard BVA.
            'PROD-EP-N1 name missing' => [['name' => '__UNSET__'], 400, 'name', null, 'EP', true],
            'PROD-EP-N2 name whitespace' => [['name' => '   '], 400, 'name', null, 'EP', true],
            'PROD-EP-N3 name shorter than min' => [['name' => str_repeat('n', 2)], 400, 'name', null, 'EP', true],
            'PROD-EP-N4 name longer than max' => [['name' => str_repeat('n', 256)], 400, 'name', null, 'EP', true],
            'PROD-EP-P1 price missing' => [['price' => '__UNSET__'], 400, 'price', null, 'EP', true],
            'PROD-EP-P2 price whitespace' => [['price' => '   '], 400, 'price', null, 'EP', true],
            'PROD-EP-P3 price nonnumeric' => [['price' => 'abc'], 400, 'price', null, 'EP', true],
            'PROD-EP-P4 price below min' => [['price' => 0], 400, 'price', null, 'EP', true],
            'PROD-EP-P5 price above max' => [['price' => 10000000000000], 400, 'price', null, 'EP', true],
            'PROD-EP-C1 category missing' => [['category_id' => '__UNSET__'], 400, 'category_id', null, 'EP', true],
            'PROD-EP-C2 category whitespace' => [['category_id' => '   '], 400, 'category_id', null, 'EP', true],
            'PROD-EP-C3 category nonnumeric' => [['category_id' => 'abc'], 400, 'category_id', null, 'EP', true],
            'PROD-EP-C4 category nonpositive' => [['category_id' => 0], 400, 'category_id', null, 'EP', true],
            'PROD-EP-C5 category does not exist' => [['category_id' => 9999], 400, 'category_id', null, 'EP', false],
            'PROD-EP-S1 stock omitted uses default' => [['stock_quantity' => '__UNSET__'], 201, null, 1, 'EP', true],
            'PROD-EP-S2 stock zero' => [['stock_quantity' => 0], 400, 'stock_quantity', null, 'EP', true],
            'PROD-EP-S3 stock negative' => [['stock_quantity' => -1], 400, 'stock_quantity', null, 'EP', true],
            'PROD-EP-S4 stock nonnumeric' => [['stock_quantity' => 'abc'], 400, 'stock_quantity', null, 'EP', true],
        ];
    }

    #[DataProvider('createProductCases')]
    public function testCreateProductEpBvaAgainstProductionSource(
        array $changes,
        int $expectedCode,
        ?string $expectedErrorField,
        ?int $expectedStock,
        string $technique,
        bool $categoryExists
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
        $repository->method('categoryExists')->willReturn($categoryExists);
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

    public function testCreateProductReturns500WhenRepositoryCannotCreate(): void
    {
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('categoryExists')->with(1)->willReturn(true);
        $repository->expects(self::once())->method('create')->willReturn(0);

        $result = $this->serviceWith($repository)->createProduct([
            'name' => 'Valid product',
            'price' => 50000,
            'category_id' => 1,
            'stock_quantity' => 10,
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(500, $result['code']);
        self::assertSame('Không thể tạo tin đăng.', $result['message']);
    }

    private function serviceWith(ProductRepository $repository): ProductService
    {
        return new ProductService($repository);
    }
}
