<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Services\OrderService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CheckoutBoundaryValueAnalysisTest extends TestCase
{
    protected function setUp(): void
    {
        $this->ensureSession();
        $_SESSION = [];
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
    }

    public static function shippingAddressBoundaries(): array
    {
        // Source rule: shipping_address has only a lower boundary, min = 10.
        return [
            'ORDER-BVA-ADDR-01 min-1' => [9, 400],
            'ORDER-BVA-ADDR-02 min' => [10, 201],
            'ORDER-BVA-ADDR-03 min+1' => [11, 201],
            'ORDER-BVA-ADDR-04 nominal' => [20, 201],
        ];
    }

    #[DataProvider('shippingAddressBoundaries')]
    public function testShippingAddressLowerBoundary(int $length, int $expectedCode): void
    {
        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $users = $this->createMock(UserRepository::class);
        $notifications = $this->createMock(NotificationService::class);

        $address = str_repeat('A', $length);
        if ($expectedCode === 400) {
            $products->expects(self::never())->method('findById');
            $orders->expects(self::never())->method('createWithTransaction');
        } else {
            $products->expects(self::once())->method('findById')->with(10)->willReturn($this->product());
            $orders->expects(self::once())
                ->method('createWithTransaction')
                ->with(
                    self::callback(function (array $orderData) use ($address): bool {
                        self::assertSame(1, $orderData['quantity']);
                        self::assertSame('pending', $orderData['status']);
                        self::assertStringEndsWith($address, $orderData['shipping_address']);
                        return true;
                    }),
                    self::callback(function (array $paymentData): bool {
                        self::assertSame('COD', $paymentData['payment_method']);
                        self::assertSame('pending', $paymentData['status']);
                        return true;
                    })
                )
                ->willReturn(501);
            $notifications->expects(self::once())->method('send')->willReturn(true);
        }

        $result = $this->serviceWith($orders, $products, $users, $notifications)->checkout([
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => $address,
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ]);

        self::assertSame($expectedCode, $result['code']);
        self::assertSame($expectedCode === 201 ? 'success' : 'error', $result['status']);
        if ($expectedCode === 400) {
            self::assertArrayHasKey('shipping_address', $result['errors']);
        } else {
            self::assertSame(501, $result['order_id']);
        }
    }

    public static function quantityBoundaries(): array
    {
        // Product stock is fixed at 5. Source normalizes quantity < 1 to 1.
        return [
            'ORDER-BVA-QTY-01 lower-1 normalized' => [0, 201, 1],
            'ORDER-BVA-QTY-02 lower' => [1, 201, 1],
            'ORDER-BVA-QTY-03 lower+1' => [2, 201, 2],
            'ORDER-BVA-QTY-04 nominal' => [3, 201, 3],
            'ORDER-BVA-QTY-05 stock-1' => [4, 201, 4],
            'ORDER-BVA-QTY-06 stock' => [5, 201, 5],
            'ORDER-BVA-QTY-07 stock+1' => [6, 400, null],
        ];
    }

    #[DataProvider('quantityBoundaries')]
    public function testQuantityBoundariesAgainstStock(
        int $inputQuantity,
        int $expectedCode,
        ?int $expectedStoredQuantity
    ): void {
        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $users = $this->createMock(UserRepository::class);
        $notifications = $this->createMock(NotificationService::class);

        $products->expects(self::once())->method('findById')->with(10)->willReturn($this->product(stock: 5));
        if ($expectedCode === 201) {
            $orders->expects(self::once())
                ->method('createWithTransaction')
                ->with(
                    self::callback(function (array $orderData) use ($expectedStoredQuantity): bool {
                        self::assertSame($expectedStoredQuantity, $orderData['quantity']);
                        self::assertSame(100000.0 * $expectedStoredQuantity, $orderData['total_price']);
                        return true;
                    }),
                    self::callback(function (array $paymentData) use ($expectedStoredQuantity): bool {
                        self::assertSame(100000.0 * $expectedStoredQuantity, $paymentData['amount']);
                        return true;
                    })
                )
                ->willReturn(502);
            $notifications->expects(self::once())->method('send')->willReturn(true);
        } else {
            $orders->expects(self::never())->method('createWithTransaction');
            $notifications->expects(self::never())->method('send');
        }

        $result = $this->serviceWith($orders, $products, $users, $notifications)->checkout([
            'product_id' => 10,
            'quantity' => $inputQuantity,
            'shipping_address' => '123 Nguyen Trai, District 1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ]);

        self::assertSame($expectedCode, $result['code']);
        self::assertSame($expectedCode === 201 ? 'success' : 'error', $result['status']);
        if ($expectedCode === 400) {
            self::assertStringContainsString('tồn kho', $result['message']);
        }
    }

    private function product(int $stock = 5): array
    {
        return [
            'ID' => 10,
            'Name' => 'Test Product',
            'Price' => 100000,
            'Stock_quantity' => $stock,
            'Status' => 'active',
            'Seller_ID' => 99,
        ];
    }

    private function serviceWith(
        OrderRepository $orders,
        ProductRepository $products,
        UserRepository $users,
        NotificationService $notifications
    ): OrderService {
        $reflection = new ReflectionClass(OrderService::class);
        /** @var OrderService $service */
        $service = $reflection->newInstanceWithoutConstructor();
        foreach ([
            'orderRepository' => $orders,
            'productRepository' => $products,
            'userRepository' => $users,
            'notificationService' => $notifications,
        ] as $property => $value) {
            $reflection->getProperty($property)->setValue($service, $value);
        }
        return $service;
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_save_path(sys_get_temp_dir());
            session_id('phpunit-order-bva-' . getmypid());
            if (!session_start(['use_cookies' => false, 'cache_limiter' => ''])) {
                self::fail('Không thể khởi tạo session kiểm thử Order BVA.');
            }
        }
    }
}
