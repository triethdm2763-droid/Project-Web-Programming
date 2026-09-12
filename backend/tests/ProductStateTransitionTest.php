<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\ProductService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ProductStateTransitionTest extends TestCase
{
    protected function setUp(): void
    {
        session_save_path(sys_get_temp_dir());
        session_id('product-state-' . getmypid());
        session_start(['use_cookies' => false, 'cache_limiter' => '']);
        $_SESSION = [];
        http_response_code(200);
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

    public function testCreateTransitionsNewListingToPending(): void
    {
        $_SESSION = ['user_id' => 7, 'role' => 'seller'];
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('categoryExists')->with(1)->willReturn(true);
        $repository->expects(self::once())->method('create')
            ->with(self::callback(fn (array $data): bool => $data['status'] === 'pending'))
            ->willReturn(31);

        $result = $this->serviceWith($repository)->createProduct($this->validData());

        self::assertSame(201, $result['code']);
        self::assertSame(31, $result['product_id']);
    }

    public static function updateTransitions(): array
    {
        return [
            'PROD-ST-02 owner keeps pending as pending' => ['pending', 'seller', 7, 'pending'],
            'PROD-ST-03 owner sends active back to pending' => ['active', 'seller', 7, 'pending'],
            'PROD-ST-04 admin retains active' => ['active', 'admin', 99, 'active'],
            'PROD-ST-05 admin retains rejected' => ['rejected', 'admin', 99, 'rejected'],
        ];
    }

    #[DataProvider('updateTransitions')]
    public function testUpdateTransitionsFollowProductionRules(
        string $start,
        string $role,
        int $sessionUser,
        string $expectedEnd
    ): void {
        $_SESSION = ['user_id' => $sessionUser, 'role' => $role];
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('categoryExists')->with(1)->willReturn(true);
        $repository->expects(self::once())->method('findById')->with(10)->willReturn([
            'ID' => 10,
            'Seller_ID' => 7,
            'Status' => $start,
        ]);
        $repository->expects(self::once())->method('update')->with(
            10,
            self::callback(fn (array $data): bool => $data['status'] === $expectedEnd)
        )->willReturn(true);

        $result = $this->serviceWith($repository)->updateProduct(10, $this->validData());

        self::assertSame(200, $result['code']);
        self::assertSame('success', $result['status']);
    }

    public function testSoldProductUpdateIsBlockedAndStateRemainsSold(): void
    {
        $_SESSION = ['user_id' => 7, 'role' => 'seller'];
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('findById')->with(10)->willReturn([
            'ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'sold',
        ]);
        $repository->expects(self::never())->method('update');

        $result = $this->serviceWith($repository)->updateProduct(10, $this->validData());

        self::assertSame(400, $result['code']);
        self::assertSame('Sản phẩm đã bán.', $result['message']);
    }

    public static function deleteTransitions(): array
    {
        return [
            'PROD-ST-07 owner deletes active product' => ['active', 'seller', 7],
            'PROD-ST-08 admin deletes pending product' => ['pending', 'admin', 99],
        ];
    }

    #[DataProvider('deleteTransitions')]
    public function testAuthorizedDeleteTransitionsToDeleted(string $start, string $role, int $sessionUser): void
    {
        $_SESSION = ['user_id' => $sessionUser, 'role' => $role];
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('findById')->with(10)->willReturn([
            'ID' => 10,
            'Seller_ID' => 7,
            'Status' => $start,
        ]);
        $repository->expects(self::once())->method('softDelete')->with(10)->willReturn(true);

        $result = $this->serviceWith($repository)->deleteProduct(10);

        self::assertSame(200, $result['code']);
        self::assertSame('success', $result['status']);
    }

    public function testSoldProductDeleteIsBlockedAndStateRemainsSold(): void
    {
        $_SESSION = ['user_id' => 7, 'role' => 'seller'];
        $repository = $this->createMock(ProductRepository::class);
        $repository->expects(self::once())->method('findById')->with(10)->willReturn([
            'ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'sold',
        ]);
        $repository->expects(self::never())->method('softDelete');

        $result = $this->serviceWith($repository)->deleteProduct(10);

        self::assertSame(400, $result['code']);
        self::assertSame('Sản phẩm đã bán, không thể xóa.', $result['message']);
    }

    public static function adminStatusTransitions(): array
    {
        return [
            'PROD-ST-10 pending approved' => ['active', false],
            'PROD-ST-11 pending rejected' => ['rejected', false],
            // The source currently accepts an arbitrary status. This exposes a validation gap.
            'PROD-ST-GAP-01 arbitrary status accepted' => ['archived', true],
        ];
    }

    #[DataProvider('adminStatusTransitions')]
    public function testAdminStatusEndpointActualTransition(string $requestedStatus, bool $isGap): void
    {
        $_SESSION = ['user_id' => 99, 'role' => 'admin'];
        $productRepository = new class ('pending') extends ProductRepository {
            public string $state;

            public function __construct(string $state)
            {
                $this->state = $state;
            }

            public function updateStatus(int $id, string $status): bool
            {
                TestCase::assertSame(10, $id);
                $this->state = $status;
                return true;
            }
        };
        $controller = new AdminController(
            $this->createMock(UserRepository::class),
            $this->createMock(OrderRepository::class),
            $productRepository
        );

        self::assertSame('pending', $productRepository->state);
        $response = $controller->updateProductStatus(['id' => 10, 'status' => $requestedStatus]);

        self::assertSame(200, $response['status_code']);
        self::assertTrue($response['body']['success']);
        self::assertSame($requestedStatus, $productRepository->state);
        if ($isGap) {
            self::assertSame('archived', $productRepository->state, 'Source has no product-status allowlist.');
        }
    }

    private function validData(): array
    {
        return ['name' => 'Valid product', 'price' => 50000, 'category_id' => 1, 'stock_quantity' => 10];
    }

    private function serviceWith(ProductRepository $repository): ProductService
    {
        return new ProductService($repository);
    }
}
