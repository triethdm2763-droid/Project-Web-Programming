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

final class CheckoutEquivalencePartitioningTest extends TestCase
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

        // NotificationService::send() returns bool.
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
            'shipping_address' => '123 Nguyen Trai, Q1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ], $overrides);
    }

    private function product(
        int $sellerId = 99,
        int $stock = 5,
        string $status = 'active'
    ): array {
        return [
            'ID' => 10,
            'Name' => 'Test Product',
            'Price' => 100000,
            'Stock_quantity' => $stock,
            'Status' => $status,
            'Seller_ID' => $sellerId,
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
     * ORDER-EP-01
     * product_id: Thiếu trường
     * Expected: HTTP 400
     * Source: OrderService.php:39-52
     * ==========================================================
     */
    public function testEP01ProductIdMissing(): void
    {
        $data = $this->checkoutData();

        unset($data['product_id']);

        $response = $this->service->checkout($data);

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-02
     * product_id: Rỗng / khoảng trắng
     */
    public function testEP02ProductIdWhitespace(): void
    {
        $response = $this->service->checkout(
            $this->checkoutData([
                'product_id' => '   ',
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-03
     * product_id: Không phải số
     * "abc" -> intval = 0
     * Expected: HTTP 404
     */
    public function testEP03ProductIdNonNumeric(): void
    {
        $this->products
            ->expects($this->once())
            ->method('findById')
            ->with(0)
            ->willReturn(null);

        $response = $this->service->checkout(
            $this->checkoutData([
                'product_id' => 'abc',
            ])
        );

        $this->assertResponseCode($response, 404);
    }

    /*
     * ORDER-EP-04
     * product_id: ID không tồn tại
     */
    public function testEP04ProductIdNotFound(): void
    {
        $this->products
            ->method('findById')
            ->willReturn(null);

        $response = $this->service->checkout(
            $this->checkoutData([
                'product_id' => 999999,
            ])
        );

        $this->assertResponseCode($response, 404);
    }

    /*
     * ORDER-EP-05
     * product status = active
     */
    public function testEP05ProductStatusActive(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                status: 'active'
            ));

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(1001);

        $response = $this->service->checkout(
            $this->checkoutData()
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-06
     * product status = available
     */
    public function testEP06ProductStatusAvailable(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                status: 'available'
            ));

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(1002);

        $response = $this->service->checkout(
            $this->checkoutData()
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-07
     * product status: Không khả dụng
     */
    public function testEP07ProductInactive(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                status: 'inactive'
            ));

        $response = $this->service->checkout(
            $this->checkoutData()
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-08
     * stock = 0
     */
    public function testEP08ProductOutOfStock(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                stock: 0
            ));

        $response = $this->service->checkout(
            $this->checkoutData()
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-09
     * shipping_address: Thiếu trường
     */
    public function testEP09ShippingAddressMissing(): void
    {
        $data = $this->checkoutData();

        unset($data['shipping_address']);

        $response = $this->service->checkout($data);

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-10
     * shipping_address: Rỗng / khoảng trắng
     */
    public function testEP10ShippingAddressWhitespace(): void
    {
        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '   ',
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-11
     * shipping_address: dưới 10 ký tự
     */
    public function testEP11ShippingAddressBelowMinimum(): void
    {
        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '123456789',
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-12
     * shipping_address: đúng 10 ký tự
     */
    public function testEP12ShippingAddressMinimum(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->method('createWithTransaction')
            ->willReturn(1003);

        $response = $this->service->checkout(
            $this->checkoutData([
                'shipping_address' => '1234567890',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-13
     * payment_method: Thiếu trường
     */
    public function testEP13PaymentMethodMissing(): void
    {
        $data = $this->checkoutData();

        unset($data['payment_method']);

        $response = $this->service->checkout($data);

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-14
     * payment_method: Rỗng / khoảng trắng
     */
    public function testEP14PaymentMethodWhitespace(): void
    {
        $response = $this->service->checkout(
            $this->checkoutData([
                'payment_method' => '   ',
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-15
     * payment_method = COD
     * Expected: HTTP 201 + payment pending
     */
    public function testEP15PaymentMethodCOD(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->isType('array'),
                $this->callback(
                    function (array $payment): bool {
                        return
                            $payment['payment_method'] === 'COD'
                            && $payment['status'] === 'pending';
                    }
                )
            )
            ->willReturn(1004);

        $response = $this->service->checkout(
            $this->checkoutData([
                'payment_method' => 'COD',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-16
     * payment_method = Bank Transfer
     * Expected: HTTP 201 + payment success
     */
    public function testEP16PaymentMethodBankTransfer(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product());

        $this->orders
            ->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->isType('array'),
                $this->callback(
                    function (array $payment): bool {
                        return
                            $payment['payment_method'] === 'Bank Transfer'
                            && $payment['status'] === 'success';
                    }
                )
            )
            ->willReturn(1005);

        $response = $this->service->checkout(
            $this->checkoutData([
                'payment_method' => 'Bank Transfer',
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-17
     * quantity: Thiếu trường
     * Source: mặc định quantity = 1
     */
    public function testEP17QuantityMissingDefaultsToOne(): void
    {
        $data = $this->checkoutData();

        unset($data['quantity']);

        $this->products
            ->method('findById')
            ->willReturn($this->product());

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
            ->willReturn(1006);

        $response = $this->service->checkout($data);

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-18
     * quantity trong tồn kho
     * quantity = stock = 5
     */
    public function testEP18QuantityWithinStock(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                stock: 5
            ));

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
            ->willReturn(1007);

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 5,
            ])
        );

        $this->assertResponseCode($response, 201);
    }

    /*
     * ORDER-EP-19
     * quantity vượt tồn kho
     * quantity = 6, stock = 5
     */
    public function testEP19QuantityExceedsStock(): void
    {
        $this->products
            ->method('findById')
            ->willReturn($this->product(
                stock: 5
            ));

        $response = $this->service->checkout(
            $this->checkoutData([
                'quantity' => 6,
            ])
        );

        $this->assertResponseCode($response, 400);
    }

    /*
     * ORDER-EP-20
     * buyer_id = seller_id
     * Expected: không cho tự mua sản phẩm
     */
    public function testEP20BuyerCannotBuyOwnProduct(): void
    {
        $_SESSION = [
            'user_id' => 99,
            'username' => 'seller',
        ];

        $this->users
            ->method('findById')
            ->willReturn([
                'ID' => 99,
            ]);

        $this->products
            ->method('findById')
            ->willReturn(
                $this->product(
                    sellerId: 99
                )
            );

        $response = $this->service->checkout(
            $this->checkoutData()
        );

        $this->assertResponseCode($response, 400);
    }
}