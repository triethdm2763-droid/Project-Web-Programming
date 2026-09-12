<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CheckoutBoundaryValueAnalysisTest extends TestCase
{
    private OrderRepository $orders;
    private ProductRepository $products;
    private UserRepository $users;
    private NotificationService $notifications;
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_COOKIE = [];

        $this->orders = $this->createMock(OrderRepository::class);
        $this->products = $this->createMock(ProductRepository::class);
        $this->users = $this->createMock(UserRepository::class);
        $this->notifications = $this->createMock(NotificationService::class);

        $this->notifications
            ->method('send')
            ->willReturn(true);

        $this->service = new OrderService();

        $this->inject('orderRepository', $this->orders);
        $this->inject('productRepository', $this->products);
        $this->inject('userRepository', $this->users);
        $this->inject('notificationService', $this->notifications);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];

        parent::tearDown();
    }

    private function inject(string $property, object $value): void
    {
        $ref = new ReflectionClass($this->service);

        $propertyRef = $ref->getProperty($property);
        $propertyRef->setAccessible(true);
        $propertyRef->setValue($this->service, $value);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '1234567890',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ], $overrides);
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

    private function assertResponseCode(array $response, int $expected): void
    {
        $this->assertSame(
            $expected,
            $response['code'] ?? null
        );
    }

    /*
     * ==========================================================
     * SHIPPING_ADDRESS
     * min = 10
     * ==========================================================
     */

    // ORDER-BVA-ADDR-01
    // min - 1 = 9
    public function testBVAAddr01MinimumMinusOne(): void
    {
        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '123456789',
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    // ORDER-BVA-ADDR-02
    // min = 10
    public function testBVAAddr02Minimum(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(2001);

        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '1234567890',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-ADDR-03
    // min + 1 = 11
    public function testBVAAddr03MinimumPlusOne(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(2002);

        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '12345678901',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-ADDR-04
    // nominal = 20
    public function testBVAAddr04Nominal(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(2003);

        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '12345678901234567890',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ==========================================================
     * QUANTITY
     * min = 1
     * max động = stock = 5
     * ==========================================================
     */

    // ORDER-BVA-QTY-01
    // min - 1 = 0
    // Source hiện tại chuẩn hóa 0 -> 1
    public function testBVAQty01MinimumMinusOne(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 1
                ),
                $this->isType('array')
            )
            ->willReturn(2004);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 0,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-02
    // min = 1
    public function testBVAQty02Minimum(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 1
                ),
                $this->isType('array')
            )
            ->willReturn(2005);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 1,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-03
    // min + 1 = 2
    public function testBVAQty03MinimumPlusOne(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 2
                ),
                $this->isType('array')
            )
            ->willReturn(2006);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 2,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-04
    // nominal = 3
    public function testBVAQty04Nominal(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 3
                ),
                $this->isType('array')
            )
            ->willReturn(2007);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 3,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-05
    // max - 1 = 4
    public function testBVAQty05MaximumMinusOne(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 4
                ),
                $this->isType('array')
            )
            ->willReturn(2008);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 4,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-06
    // max = stock = 5
    public function testBVAQty06Maximum(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->callback(
                    fn(array $order): bool =>
                        ($order['quantity'] ?? null) === 5
                ),
                $this->isType('array')
            )
            ->willReturn(2009);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 5,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    // ORDER-BVA-QTY-07
    // max + 1 = 6
    public function testBVAQty07MaximumPlusOne(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(stock: 5));

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 6,
            ])
        );

        $this->assertResponseCode($response, 400);
    }
}