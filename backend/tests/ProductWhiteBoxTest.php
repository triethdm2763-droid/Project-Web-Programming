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
    /*
     * =========================================================
     * CREATE PRODUCT - BASIS PATHS
     * =========================================================
     */

    public static function basisPaths(): array
    {
        // CFG scope: ProductService::createProduct()
        // Validator is treated as one call node.
        return [
            'PROD-WB-P1 session none then unauthenticated'
                => [false, false, [], 401, null, null],

            'PROD-WB-P2 session active but unauthenticated'
                => [true, false, [], 401, null, null],

            'PROD-WB-P3 validation errors'
                => [true, true, ['name' => ''], 400, 'name', null],

            'PROD-WB-P4 nonpositive price'
                => [true, true, ['price' => 0], 400, 'price', null],

            'PROD-WB-P5 successful creation with supplied stock'
                => [true, true, [], 201, null, 10],

            'PROD-WB-P6 successful creation with default stock'
                => [true, true, ['stock_quantity' => '__UNSET__'], 201, null, 1],
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
            session_start([
                'use_cookies' => false,
                'cache_limiter' => ''
            ]);
        }

        $_SESSION = $authenticated
            ? ['user_id' => 7, 'role' => 'seller']
            : [];

        $data = [
            'name' => 'Valid product',
            'price' => 50000,
            'category_id' => 1,
            'stock_quantity' => 10
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
                ->with(
                    self::callback(
                        function (array $stored) use ($expectedStock): bool {
                            self::assertSame(
                                'pending',
                                $stored['status']
                            );

                            self::assertSame(
                                $expectedStock,
                                $stored['stock_quantity']
                            );

                            return true;
                        }
                    )
                )
                ->willReturn(55);
        } else {
            $repository->expects(self::never())
                ->method('create');
        }

        $service = $this->serviceWith($repository);

        $result = $service->createProduct($data);

        self::assertSame(
            $expectedCode,
            $result['code']
        );

        self::assertSame(
            $expectedCode === 201 ? 'success' : 'error',
            $result['status']
        );

        if ($expectedErrorField !== null) {
            self::assertArrayHasKey(
                $expectedErrorField,
                $result['errors']
            );
        }

        if ($expectedCode === 201) {
            self::assertSame(
                55,
                $result['product_id']
            );
        }

        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header_remove();
        http_response_code(200);
    }

    /*
     * =========================================================
     * HELPER
     * =========================================================
     */

    private function serviceWith(
        ProductRepository $repository
    ): ProductService {
        $reflection = new \ReflectionClass(
            ProductService::class
        );

        $service = $reflection
            ->newInstanceWithoutConstructor();

        $reflection
            ->getProperty('productRepository')
            ->setValue($service, $repository);

        return $service;
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());

            session_start([
                'use_cookies' => false,
                'cache_limiter' => ''
            ]);
        }
    }

    /*
     * =========================================================
     * GET ACTIVE PRODUCTS
     * =========================================================
     */

    public function test_WB_getActiveProducts_without_limit(): void
    {
        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->once())
            ->method('findAllActive')
            ->with([])
            ->willReturn([
                [
                    'Product_ID' => 1,
                    'Name' => 'Product A'
                ],
                [
                    'Product_ID' => 2,
                    'Name' => 'Product B'
                ]
            ]);

        $repo->expects($this->never())
            ->method('countAllActive');

        $service = $this->serviceWith($repo);

        $result = $service->getActiveProducts([]);

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertCount(
            2,
            $result['data']
        );

        $this->assertArrayNotHasKey(
            'total',
            $result
        );
    }

    public function test_WB_getActiveProducts_with_limit(): void
    {
        $repo = $this->createMock(
            ProductRepository::class
        );

        $filters = [
            'limit' => 10,
            'offset' => 0
        ];

        $repo->expects($this->once())
            ->method('findAllActive')
            ->with($filters)
            ->willReturn([
                [
                    'Product_ID' => 1,
                    'Name' => 'Product A'
                ]
            ]);

        $repo->expects($this->once())
            ->method('countAllActive')
            ->with($filters)
            ->willReturn(25);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getActiveProducts($filters);

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertCount(
            1,
            $result['data']
        );

        $this->assertSame(
            25,
            $result['total']
        );
    }

    /*
     * =========================================================
     * GET PRODUCT DETAIL
     * =========================================================
     */

    public function test_WB_getProductDetail_found(): void
    {
        $repo = $this->createMock(
            ProductRepository::class
        );

        $product = [
            'Product_ID' => 1,
            'Name' => 'Product A',
            'Price' => 50000
        ];

        $repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($product);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getProductDetail(1);

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertSame(
            $product,
            $result['data']
        );
    }

    public function test_WB_getProductDetail_not_found(): void
    {
        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getProductDetail(999);

        $this->assertSame(
            'error',
            $result['status']
        );

        $this->assertSame(
            404,
            $result['code']
        );

        $this->assertSame(
            'Không tìm thấy sản phẩm.',
            $result['message']
        );
    }

    /*
     * =========================================================
     * GET SELLER PRODUCTS
     * =========================================================
     */

    public function test_WB_getSellerProducts_guest_returns_401(): void
    {
        $this->startSession();

        unset($_SESSION['user_id']);

        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->never())
            ->method('findSellerProducts');

        $service = $this->serviceWith($repo);

        $result = $service
            ->getSellerProducts();

        $this->assertSame(
            'error',
            $result['status']
        );

        $this->assertSame(
            401,
            $result['code']
        );
    }

    public function test_WB_getSellerProducts_logged_in_returns_products(): void
    {
        $this->startSession();

        $_SESSION['user_id'] = 5;

        $repo = $this->createMock(
            ProductRepository::class
        );

        $products = [
            [
                'Product_ID' => 1,
                'Name' => 'Product A'
            ],
            [
                'Product_ID' => 2,
                'Name' => 'Product B'
            ]
        ];

        $repo->expects($this->once())
            ->method('findSellerProducts')
            ->with(5, null)
            ->willReturn($products);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getSellerProducts();

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertSame(
            $products,
            $result['data']
        );
    }

    /*
     * =========================================================
     * GET MY PRODUCTS
     * =========================================================
     */

    public function test_WB_getMyProducts_returns_current_seller_products(): void
    {
        $this->startSession();

        $_SESSION['user_id'] = 7;

        $repo = $this->createMock(
            ProductRepository::class
        );

        $products = [
            [
                'Product_ID' => 10,
                'Name' => 'Iphone 15'
            ],
            [
                'Product_ID' => 11,
                'Name' => 'Macbook Air'
            ]
        ];

        $repo->expects($this->once())
            ->method('findSellerProducts')
            ->with(7, null)
            ->willReturn($products);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getMyProducts();

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertSame(
            $products,
            $result['data']
        );
    }

    /*
     * =========================================================
     * GET SELLER STATS
     * =========================================================
     */

    public function test_WB_getSellerStats_guest_returns_401(): void
    {
        $this->startSession();

        unset($_SESSION['user_id']);

        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->never())
            ->method('findSellerProducts');

        $repo->expects($this->never())
            ->method('getSellerStats');

        $service = $this->serviceWith($repo);

        $result = $service
            ->getSellerStats();

        $this->assertSame(
            'error',
            $result['status']
        );

        $this->assertSame(
            401,
            $result['code']
        );
    }

    public function test_WB_getSellerStats_without_sold_products(): void
    {
        $this->startSession();

        $_SESSION['user_id'] = 7;

        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->once())
            ->method('findSellerProducts')
            ->with(7)
            ->willReturn([
                [
                    'Status' => 'pending'
                ],
                [
                    'Status' => 'pending'
                ]
            ]);

        $repo->expects($this->once())
            ->method('getSellerStats')
            ->with(7)
            ->willReturn([
                'revenue' => 0.0,
                'delivered_orders' => 0
            ]);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getSellerStats();

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertSame(
            2,
            $result['data']['total_products']
        );

        $this->assertSame(
            0,
            $result['data']['sold_products']
        );

        $this->assertSame(
            0.0,
            $result['data']['revenue']
        );

        $this->assertSame(
            0,
            $result['data']['delivered_orders']
        );
    }

    public function test_WB_getSellerStats_with_sold_products(): void
    {
        $this->startSession();

        $_SESSION['user_id'] = 7;

        $repo = $this->createMock(
            ProductRepository::class
        );

        $repo->expects($this->once())
            ->method('findSellerProducts')
            ->with(7)
            ->willReturn([
                [
                    'Status' => 'sold'
                ],
                [
                    'Status' => 'sold'
                ],
                [
                    'Status' => 'pending'
                ]
            ]);

        $repo->expects($this->once())
            ->method('getSellerStats')
            ->with(7)
            ->willReturn([
                'revenue' => 100000.0,
                'delivered_orders' => 2
            ]);

        $service = $this->serviceWith($repo);

        $result = $service
            ->getSellerStats();

        $this->assertSame(
            'success',
            $result['status']
        );

        $this->assertSame(
            200,
            $result['code']
        );

        $this->assertSame(
            3,
            $result['data']['total_products']
        );

        $this->assertSame(
            2,
            $result['data']['sold_products']
        );

        $this->assertSame(
            100000.0,
            $result['data']['revenue']
        );

        $this->assertSame(
            2,
            $result['data']['delivered_orders']
        );
    }
    /*
 * =========================================================
 * DELETE PRODUCT
 * =========================================================
 */

public function test_WB_deleteProduct_session_none_guest_returns_401(): void
{
    // Không gọi startSession() để đi qua nhánh session_status() === PHP_SESSION_NONE
    $_SESSION = [];

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->never())
        ->method('findById');

    $repo->expects($this->never())
        ->method('softDelete');

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(1);

    $this->assertSame('error', $result['status']);
    $this->assertSame(401, $result['code']);
}


public function test_WB_deleteProduct_session_active_guest_returns_401(): void
{
    $this->startSession();

    unset($_SESSION['user_id']);

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->never())
        ->method('findById');

    $repo->expects($this->never())
        ->method('softDelete');

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(1);

    $this->assertSame('error', $result['status']);
    $this->assertSame(401, $result['code']);
}


public function test_WB_deleteProduct_not_found_returns_404(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(999)
        ->willReturn(null);

    $repo->expects($this->never())
        ->method('softDelete');

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(999);

    $this->assertSame('error', $result['status']);
    $this->assertSame(404, $result['code']);
}


public function test_WB_deleteProduct_not_owner_not_admin_returns_403(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 99,
            'Status' => 'active'
        ]);

    $repo->expects($this->never())
        ->method('softDelete');

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(10);

    $this->assertSame('error', $result['status']);
    $this->assertSame(403, $result['code']);
}


public function test_WB_deleteProduct_owner_sold_returns_400(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'sold'
        ]);

    $repo->expects($this->never())
        ->method('softDelete');

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(10);

    $this->assertSame('error', $result['status']);
    $this->assertSame(400, $result['code']);
}


public function test_WB_deleteProduct_owner_success(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('softDelete')
        ->with(10);

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(10);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}


public function test_WB_deleteProduct_admin_success(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 99,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('softDelete')
        ->with(10);

    $service = $this->serviceWith($repo);

    $result = $service->deleteProduct(10);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}
/*
 * =========================================================
 * UPDATE PRODUCT
 * =========================================================
 */

public function test_WB_updateProduct_session_none_guest_returns_401(): void
{
    // Test chạy separate process nên session ban đầu chưa active.
    $_SESSION = [];

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->never())
        ->method('findById');

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, []);

    $this->assertSame('error', $result['status']);
    $this->assertSame(401, $result['code']);
}


public function test_WB_updateProduct_session_active_guest_returns_401(): void
{
    $this->startSession();

    unset($_SESSION['user_id']);

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->never())
        ->method('findById');

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, []);

    $this->assertSame('error', $result['status']);
    $this->assertSame(401, $result['code']);
}


public function test_WB_updateProduct_not_found_returns_404(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(999)
        ->willReturn(null);

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(999, [
        'name' => 'Updated product',
        'price' => 60000,
        'category_id' => 2
    ]);

    $this->assertSame('error', $result['status']);
    $this->assertSame(404, $result['code']);
}


public function test_WB_updateProduct_not_owner_not_admin_returns_403(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 99,
            'Status' => 'active'
        ]);

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Updated product',
        'price' => 60000,
        'category_id' => 2
    ]);

    $this->assertSame('error', $result['status']);
    $this->assertSame(403, $result['code']);
}


public function test_WB_updateProduct_owner_sold_returns_400(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'sold'
        ]);

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Updated product',
        'price' => 60000,
        'category_id' => 2
    ]);

    $this->assertSame('error', $result['status']);
    $this->assertSame(400, $result['code']);
}


public function test_WB_updateProduct_validation_error_returns_400(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'active'
        ]);

    $repo->expects($this->never())
        ->method('update');

    $service = $this->serviceWith($repo);

    // name rỗng -> Validator fail
    $result = $service->updateProduct(10, [
        'name' => '',
        'price' => 60000,
        'category_id' => 2
    ]);

    $this->assertSame('error', $result['status']);
    $this->assertSame(400, $result['code']);
    $this->assertSame(
        'Dữ liệu không hợp lệ.',
        $result['message']
    );
}


public function test_WB_updateProduct_owner_success_with_stock(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('update')
        ->with(
            10,
            $this->callback(function (array $data): bool {
                $this->assertSame(
                    'Updated product',
                    $data['name']
                );

                $this->assertSame(
                    60000.0,
                    $data['price']
                );

                $this->assertSame(
                    2,
                    $data['category_id']
                );

                $this->assertSame(
                    5,
                    $data['stock_quantity']
                );

                // Seller sửa sản phẩm -> reset pending
                $this->assertSame(
                    'pending',
                    $data['status']
                );

                // Không truyền image -> không được tự thêm image
                $this->assertArrayNotHasKey(
                    'image',
                    $data
                );

                return true;
            })
        )
        ->willReturn(true);

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Updated product',
        'description' => 'Updated description',
        'price' => 60000,
        'category_id' => 2,
        'stock_quantity' => 5
    ]);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}


public function test_WB_updateProduct_owner_success_default_stock(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('update')
        ->with(
            10,
            $this->callback(function (array $data): bool {
                // Không truyền stock_quantity
                // source thật default = 1
                $this->assertSame(
                    1,
                    $data['stock_quantity']
                );

                $this->assertSame(
                    'pending',
                    $data['status']
                );

                return true;
            })
        )
        ->willReturn(true);

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Updated product',
        'price' => 60000,
        'category_id' => 2
    ]);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}


public function test_WB_updateProduct_owner_success_with_image(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'seller';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('update')
        ->with(
            10,
            $this->callback(function (array $data): bool {
                $this->assertSame(
                    'new-image.jpg',
                    $data['image']
                );

                $this->assertSame(
                    'pending',
                    $data['status']
                );

                return true;
            })
        )
        ->willReturn(true);

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Updated product',
        'price' => 60000,
        'category_id' => 2,
        'image' => 'new-image.jpg'
    ]);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}


public function test_WB_updateProduct_admin_non_owner_keeps_status(): void
{
    $this->startSession();

    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 99,
            'Status' => 'active'
        ]);

    $repo->expects($this->once())
        ->method('update')
        ->with(
            10,
            $this->callback(function (array $data): bool {
                // Admin không bị reset về pending
                $this->assertSame(
                    'active',
                    $data['status']
                );

                return true;
            })
        )
        ->willReturn(true);

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Admin updated product',
        'price' => 70000,
        'category_id' => 2
    ]);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}


public function test_WB_updateProduct_owner_admin_keeps_status(): void
{
    $this->startSession();

    // Vừa là owner vừa có role admin.
    // Case này giúp đi qua nhánh:
    // $isOwner = true nhưng !$isAdmin = false.
    $_SESSION['user_id'] = 7;
    $_SESSION['role'] = 'admin';

    $repo = $this->createMock(ProductRepository::class);

    $repo->expects($this->once())
        ->method('findById')
        ->with(10)
        ->willReturn([
            'Product_ID' => 10,
            'Seller_ID' => 7,
            'Status' => 'available'
        ]);

    $repo->expects($this->once())
        ->method('update')
        ->with(
            10,
            $this->callback(function (array $data): bool {
                // Vì là admin nên dù cũng là owner,
                // status vẫn giữ nguyên.
                $this->assertSame(
                    'available',
                    $data['status']
                );

                return true;
            })
        )
        ->willReturn(true);

    $service = $this->serviceWith($repo);

    $result = $service->updateProduct(10, [
        'name' => 'Owner admin update',
        'price' => 80000,
        'category_id' => 2
    ]);

    $this->assertSame('success', $result['status']);
    $this->assertSame(200, $result['code']);
}
}