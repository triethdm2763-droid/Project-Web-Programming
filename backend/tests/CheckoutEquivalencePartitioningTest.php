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

final class CheckoutEquivalencePartitioningTest extends TestCase
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

    public static function checkoutPartitions(): array
    {
        return [
            'ORDER-EP-01 product_id missing' => [['product_id' => '__UNSET__'], 'default', false, 400, 'product_id', null, null, 'EP'],
            'ORDER-EP-02 product_id blank' => [['product_id' => '   '], 'default', false, 400, 'product_id', null, null, 'EP'],
            'ORDER-EP-03 product_id nonnumeric maps to missing product' => [['product_id' => 'abc'], 'missing', false, 404, null, null, null, 'EP'],
            'ORDER-EP-04 product not found' => [['product_id' => 999999], 'missing', false, 404, null, null, null, 'EP'],
            'ORDER-EP-05 active product' => [[], 'active', false, 201, null, 1, 'pending', 'EP'],
            'ORDER-EP-06 available product' => [[], 'available', false, 201, null, 1, 'pending', 'EP'],
            'ORDER-EP-07 inactive product' => [[], 'inactive', false, 400, null, null, null, 'EP'],
            'ORDER-EP-08 zero stock' => [[], 'zero-stock', false, 400, null, null, null, 'EP'],
            'ORDER-EP-09 shipping_address missing' => [['shipping_address' => '__UNSET__'], 'default', false, 400, 'shipping_address', null, null, 'EP'],
            'ORDER-EP-10 shipping_address whitespace' => [['shipping_address' => '   '], 'default', false, 400, 'shipping_address', null, null, 'EP'],
            'ORDER-EP-11 shipping_address too short' => [['shipping_address' => '123456789'], 'default', false, 400, 'shipping_address', null, null, 'EP'],
            'ORDER-EP-12 shipping_address valid' => [['shipping_address' => '1234567890'], 'default', false, 201, null, 1, 'pending', 'EP'],
            'ORDER-EP-13 payment_method missing' => [['payment_method' => '__UNSET__'], 'default', false, 400, 'payment_method', null, null, 'EP'],
            'ORDER-EP-14 payment_method whitespace' => [['payment_method' => '   '], 'default', false, 400, 'payment_method', null, null, 'EP'],
            'ORDER-EP-15 COD payment' => [['payment_method' => 'COD'], 'default', false, 201, null, 1, 'pending', 'EP'],
            'ORDER-EP-16 bank payment' => [['payment_method' => 'Bank Transfer'], 'default', false, 201, null, 1, 'success', 'EP'],
            'ORDER-GAP-01 unsupported payment accepted' => [['payment_method' => 'Crypto'], 'default', false, 201, null, 1, 'success', 'GAP'],
            'ORDER-EP-17 quantity missing defaults to one' => [['quantity' => '__UNSET__'], 'default', false, 201, null, 1, 'pending', 'EP'],
            'ORDER-GAP-02 quantity zero normalized to one' => [['quantity' => 0], 'default', false, 201, null, 1, 'pending', 'GAP'],
            'ORDER-GAP-03 negative quantity normalized to one' => [['quantity' => -5], 'default', false, 201, null, 1, 'pending', 'GAP'],
            'ORDER-GAP-04 nonnumeric quantity normalized to one' => [['quantity' => 'abc'], 'default', false, 201, null, 1, 'pending', 'GAP'],
            'ORDER-EP-18 quantity within stock' => [['quantity' => 5], 'default', false, 201, null, 5, 'pending', 'EP'],
            'ORDER-EP-19 quantity over stock' => [['quantity' => 6], 'default', false, 400, null, null, null, 'EP'],
            'ORDER-GAP-05 empty guest contact accepted' => [['fullname' => '', 'phone' => ''], 'default', false, 201, null, 1, 'pending', 'GAP'],
            'ORDER-EP-20 buyer cannot buy own product' => [[], 'self-owned', true, 400, null, null, null, 'EP'],
        ];
    }

    #[DataProvider('checkoutPartitions')]
    public function testCheckoutPartitionsAgainstCurrentSource(
        array $changes,
        string $productCase,
        bool $loggedIn,
        int $expectedCode,
        ?string $expectedErrorField,
        ?int $expectedQuantity,
        ?string $expectedPaymentStatus,
        string $classification
    ): void {
        $data = $this->validData();
        foreach ($changes as $field => $value) {
            if ($value === '__UNSET__') {
                unset($data[$field]);
            } else {
                $data[$field] = $value;
            }
        }

        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $users = $this->createMock(UserRepository::class);
        $notifications = $this->createMock(NotificationService::class);

        if ($loggedIn) {
            $_SESSION = ['user_id' => 7, 'username' => 'Buyer A'];
            $users->expects(self::once())->method('findById')->with(7)->willReturn($this->user());
        }

        if ($expectedErrorField !== null) {
            $products->expects(self::never())->method('findById');
            $orders->expects(self::never())->method('createWithTransaction');
        } else {
            $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
            $products->expects(self::once())
                ->method('findById')
                ->with($productId)
                ->willReturn($this->productFor($productCase));

            if ($expectedCode === 201) {
                $expectedMethod = trim((string)$data['payment_method']);
                $orders->expects(self::once())
                    ->method('createWithTransaction')
                    ->with(
                        self::callback(function (array $orderData) use ($expectedQuantity): bool {
                            self::assertSame($expectedQuantity, $orderData['quantity']);
                            self::assertSame('pending', $orderData['status']);
                            self::assertSame(10, $orderData['product_id']);
                            return true;
                        }),
                        self::callback(function (array $paymentData) use ($expectedMethod, $expectedPaymentStatus): bool {
                            self::assertSame($expectedMethod, $paymentData['payment_method']);
                            self::assertSame($expectedPaymentStatus, $paymentData['status']);
                            return true;
                        })
                    )
                    ->willReturn(601);
                $notifications->expects(self::once())->method('send')->willReturn(true);
            } else {
                $orders->expects(self::never())->method('createWithTransaction');
                $notifications->expects(self::never())->method('send');
            }
        }

        $result = $this->serviceWith($orders, $products, $users, $notifications)->checkout($data);

        self::assertSame($expectedCode, $result['code'], $classification . ' returned an unexpected status code.');
        self::assertSame($expectedCode === 201 ? 'success' : 'error', $result['status']);
        if ($expectedErrorField !== null) {
            self::assertArrayHasKey('errors', $result);
            self::assertArrayHasKey($expectedErrorField, $result['errors']);
        }
        if ($expectedCode === 201) {
            self::assertSame(601, $result['order_id']);
        }
    }

    private function validData(): array
    {
        return [
            'product_id' => 10,
            'shipping_address' => '123 Nguyen Trai, District 1',
            'payment_method' => 'COD',
            'quantity' => 1,
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ];
    }

    private function productFor(string $case): ?array
    {
        if ($case === 'missing') {
            return null;
        }
        $status = match ($case) {
            'available' => 'available',
            'inactive' => 'inactive',
            default => 'active',
        };
        return [
            'ID' => 10,
            'Name' => 'Test Product',
            'Price' => 100000,
            'Stock_quantity' => $case === 'zero-stock' ? 0 : 5,
            'Status' => $status,
            'Seller_ID' => $case === 'self-owned' ? 7 : 99,
        ];
    }

    private function user(): array
    {
        return ['ID' => 7, 'Fullname' => 'Buyer A', 'Phone' => '0900000000', 'Address' => 'Existing address'];
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
            session_id('phpunit-order-ep-' . getmypid());
            if (!session_start(['use_cookies' => false, 'cache_limiter' => ''])) {
                self::fail('Không thể khởi tạo session kiểm thử Order EP.');
            }
        }
    }
}
