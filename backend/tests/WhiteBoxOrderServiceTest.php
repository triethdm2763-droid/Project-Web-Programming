<?php

namespace Tests;

use App\Services\OrderService;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * White-box test suite for the complete OrderService.php.
 *
 * Functions covered:
 *   - __construct()
 *   - checkout()
 *   - cancelOrder()
 *   - getBuyerHistory()
 *   - getSellerOrders()
 *   - updateStatus()
 *   - trackOrder()
 *
 * The test class replaces the private dependencies with PHPUnit mocks,
 * so the tests focus on the control flow of OrderService.
 */
class WhiteBoxOrderServiceTest extends TestCase
{
    private OrderService $service;
    private $orderRepo;
    private $productRepo;
    private $userRepo;
    private $notification;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
        } else {
            @session_start();
            $_SESSION = [];
        }

        $_COOKIE = [];

        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->productRepo = $this->createMock(ProductRepository::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->notification = $this->createMock(NotificationService::class);

        // NotificationService::send() returns bool in the project.
        $this->notification->method('send')->willReturn(true);

        $this->service = new OrderService();

        $this->inject('orderRepository', $this->orderRepo);
        $this->inject('productRepository', $this->productRepo);
        $this->inject('userRepository', $this->userRepo);
        $this->inject('notificationService', $this->notification);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
        parent::tearDown();
    }

    private function inject(string $property, $value): void
    {
        $ref = new ReflectionClass(OrderService::class);
        $p = $ref->getProperty($property);
        $p->setAccessible(true);
        $p->setValue($this->service, $value);
    }

    private function validCheckout(array $override = []): array
    {
        return array_merge([
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '123 Nguyen Trai, Quan 1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ], $override);
    }

    private function product(array $override = []): array
    {
        return array_merge([
            'ID' => 10,
            'Seller_ID' => 99,
            'Name' => 'Ao khoac',
            'Price' => 100000,
            'Stock_quantity' => 5,
            'Status' => 'active',
        ], $override);
    }

    private function order(array $override = []): array
    {
        return array_merge([
            'ID' => 20,
            'Buyer_ID' => 7,
            'Seller_ID' => 99,
            'Product_ID' => 10,
            'ProductName' => 'Ao khoac',
            'ProductImage' => 'ao.jpg',
            'Quantity' => 1,
            'Total_price' => 100000,
            'Shipping_address' => '123 Nguyen Trai',
            'Status' => 'pending',
            'Payment_method' => 'COD',
            'PaymentStatus' => 'pending',
            'Order_Code' => 'DH260912ABC123',
            'created_at' => '2026-09-12 10:00:00',
        ], $override);
    }

    private function assertCode(array $result, int $expected): void
    {
        $this->assertSame($expected, $result['code'] ?? null);
    }

    // ============================================================
    // F00 - CONSTRUCTOR
    // ============================================================

    public function testConstructorCreatesDependencies(): void
    {
        $service = new OrderService();
        $ref = new ReflectionClass($service);

        foreach ([
            'orderRepository',
            'productRepository',
            'userRepository',
            'notificationService',
        ] as $property) {
            $p = $ref->getProperty($property);
            $p->setAccessible(true);
            $this->assertNotNull($p->getValue($service));
        }
    }

    // ============================================================
    // F01 - checkout()
    // ============================================================

    public function testCheckoutValidationError(): void
    {
        $this->assertCode($this->service->checkout([]), 400);
    }

    public function testCheckoutDefaultQuantity(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(101);

        $result = $this->service->checkout(
            $this->validCheckout(['quantity' => null])
        );

        // null is isset=false, therefore quantity defaults to 1.
        $this->assertCode($result, 201);
    }

    public function testCheckoutQuantityLessThanOneIsNormalized(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(102);

        $result = $this->service->checkout(
            $this->validCheckout(['quantity' => 0])
        );

        $this->assertCode($result, 201);
    }

    public function testCheckoutAuthenticatedBuyerExists(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $this->userRepo->method('findById')
            ->willReturnOnConsecutiveCalls(
                ['ID' => 7, 'Fullname' => 'Old', 'Phone' => '0900', 'Address' => 'Old address'],
                ['ID' => 7, 'Fullname' => 'Old', 'Phone' => '0900', 'Address' => 'Old address']
            );

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(103);

        $result = $this->service->checkout($this->validCheckout());

        $this->assertCode($result, 201);
    }

    public function testCheckoutInvalidBuyerFallsBackToGuestAndClearsSession(): void
    {
        $_SESSION = ['user_id' => 777];
        $_COOKIE = ['token' => 'abc'];

        $this->userRepo->method('findById')->willReturn(null);
        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(104);

        $result = $this->service->checkout($this->validCheckout());

        $this->assertCode($result, 201);
        $this->assertSame([], $_SESSION);
    }

    public function testCheckoutProductNotFound(): void
    {
        $this->productRepo->method('findById')->willReturn(null);

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            404
        );
    }

    public function testCheckoutInactiveProduct(): void
    {
        $this->productRepo->method('findById')
            ->willReturn($this->product(['Status' => 'inactive']));

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            400
        );
    }

    public function testCheckoutZeroStock(): void
    {
        $this->productRepo->method('findById')
            ->willReturn($this->product(['Stock_quantity' => 0]));

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            400
        );
    }

    public function testCheckoutQuantityExceedsStock(): void
    {
        $this->productRepo->method('findById')
            ->willReturn($this->product(['Stock_quantity' => 1]));

        $this->assertCode(
            $this->service->checkout($this->validCheckout(['quantity' => 2])),
            400
        );
    }

    public function testCheckoutBuyerCannotBuyOwnProduct(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->userRepo->method('findById')->willReturn(['ID' => 99]);
        $this->productRepo->method('findById')->willReturn(
            $this->product(['Seller_ID' => 99])
        );

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            400
        );
    }

    public function testCheckoutGuestBuildsGuestShippingAndSendsSellerNotification(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(105);

        $this->notification
            ->expects($this->once())
            ->method('send')
            ->with(
                99,
                'Bạn có đơn hàng mới!',
                $this->stringContains('Nguyen Van A')
            )
            ->willReturn(true);

        $result = $this->service->checkout($this->validCheckout());

        $this->assertCode($result, 201);
    }

    public function testCheckoutAuthenticatedProfileUpdate(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $user = [
            'ID' => 7,
            'Fullname' => 'Old',
            'Phone' => '0900',
            'Address' => 'Old address',
        ];

        $this->userRepo->method('findById')->willReturn($user);
        $this->userRepo->expects($this->atLeastOnce())
            ->method('updateProfile');

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(106);

        $this->assertCode(
            $this->service->checkout(
                $this->validCheckout([
                    'fullname' => 'New Name',
                    'phone' => '0911111111',
                    'shipping_address' => 'New address, Quan 1',
                ])
            ),
            201
        );
    }

    public function testCheckoutProfileLookupReturnsNullAndSkipsProfileUpdate(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $this->userRepo->method('findById')->willReturnOnConsecutiveCalls(
            ['ID' => 7],
            null
        );
        $this->userRepo->expects($this->never())->method('updateProfile');

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(107);

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            201
        );
    }

    public function testCheckoutCod(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->arrayHasKey('order_code'),
                $this->callback(fn(array $payment) =>
                    $payment['payment_method'] === 'COD'
                    && $payment['status'] === 'pending'
                )
            )
            ->willReturn(108);

        $this->assertCode(
            $this->service->checkout($this->validCheckout(['payment_method' => 'COD'])),
            201
        );
    }

    public function testCheckoutNonCod(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->expects($this->once())
            ->method('createWithTransaction')
            ->with(
                $this->arrayHasKey('order_code'),
                $this->callback(fn(array $payment) =>
                    $payment['payment_method'] === 'Bank'
                    && $payment['status'] === 'success'
                )
            )
            ->willReturn(109);

        $this->assertCode(
            $this->service->checkout($this->validCheckout(['payment_method' => 'Bank'])),
            201
        );
    }

    public function testCheckoutTransactionException(): void
    {
        $_SESSION = [];

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')
            ->willThrowException(new \Exception('DB error'));

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            500
        );
    }

    public function testCheckoutBuyerNotification(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $this->userRepo->method('findById')->willReturn([
            'ID' => 7,
            'Fullname' => 'Buyer',
            'Phone' => '0900',
            'Address' => 'Address',
        ]);

        $this->productRepo->method('findById')->willReturn($this->product());
        $this->orderRepo->method('createWithTransaction')->willReturn(110);

        $this->notification
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturn(true);

        $this->assertCode(
            $this->service->checkout($this->validCheckout()),
            201
        );
    }

    // ============================================================
    // F02 - cancelOrder()
    // ============================================================

    public function testCancelOrderNotLoggedIn(): void
    {
        $_SESSION = [];

        $this->assertCode(
            $this->service->cancelOrder([]),
            401
        );
    }

    public function testCancelOrderMissingOrderId(): void
    {
        $_SESSION = ['user_id' => 7];

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => '']),
            400
        );
    }

    public function testCancelOrderNotFound(): void
    {
        $_SESSION = ['user_id' => 7];

        $this->orderRepo->method('findById')->willReturn(null);

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            404
        );
    }

    public function testCancelOrderWrongBuyer(): void
    {
        $_SESSION = ['user_id' => 7];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Buyer_ID' => 8])
        );

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            403
        );
    }

    public function testCancelOrderNonPending(): void
    {
        $_SESSION = ['user_id' => 7];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Status' => 'confirmed'])
        );

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            400
        );
    }

    public function testCancelOrderPendingQuantityTwo(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Quantity' => 2])
        );
        $this->orderRepo->expects($this->once())
            ->method('cancelWithTransaction')
            ->with(20, 10, 2);

        $this->notification->expects($this->exactly(2))
            ->method('send')
            ->willReturn(true);

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            200
        );
    }

    public function testCancelOrderMissingQuantityDefaultsToOne(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $order = $this->order();
        unset($order['Quantity']);

        $this->orderRepo->method('findById')->willReturn($order);
        $this->orderRepo->expects($this->once())
            ->method('cancelWithTransaction')
            ->with(20, 10, 1);

        $this->notification->expects($this->exactly(2))
            ->method('send')
            ->willReturn(true);

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            200
        );
    }

    public function testCancelOrderTransactionException(): void
    {
        $_SESSION = ['user_id' => 7, 'username' => 'buyer'];

        $this->orderRepo->method('findById')->willReturn($this->order());
        $this->orderRepo->method('cancelWithTransaction')
            ->willThrowException(new \Exception('DB error'));

        $this->assertCode(
            $this->service->cancelOrder(['order_id' => 20]),
            500
        );
    }

    // ============================================================
    // F03 - getBuyerHistory()
    // ============================================================

    public function testGetBuyerHistoryNotLoggedIn(): void
    {
        $_SESSION = [];

        $this->assertCode(
            $this->service->getBuyerHistory(),
            401
        );
    }

    public function testGetBuyerHistoryAuthenticated(): void
    {
        $_SESSION = ['user_id' => 7];

        $orders = [$this->order()];
        $this->orderRepo->expects($this->once())
            ->method('findByBuyer')
            ->with(7)
            ->willReturn($orders);

        $result = $this->service->getBuyerHistory();

        $this->assertCode($result, 200);
        $this->assertSame($orders, $result['data']);
    }

    // ============================================================
    // F04 - getSellerOrders()
    // ============================================================

    public function testGetSellerOrdersNotLoggedIn(): void
    {
        $_SESSION = [];

        $this->assertCode(
            $this->service->getSellerOrders(),
            401
        );
    }

    public function testGetSellerOrdersAuthenticated(): void
    {
        $_SESSION = ['user_id' => 99];

        $orders = [$this->order(['Seller_ID' => 99])];

        $this->orderRepo->expects($this->once())
            ->method('findBySeller')
            ->with(99)
            ->willReturn($orders);

        $result = $this->service->getSellerOrders();

        $this->assertCode($result, 200);
        $this->assertSame($orders, $result['data']);
    }

    // ============================================================
    // F05 - updateStatus()
    // ============================================================

    public function testUpdateStatusNotLoggedIn(): void
    {
        $_SESSION = [];

        $this->assertCode(
            $this->service->updateStatus([]),
            401
        );
    }

    public function testUpdateStatusMissingOrderId(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => '',
                'status' => 'confirmed',
            ]),
            400
        );
    }

    public function testUpdateStatusMissingStatus(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => '',
            ]),
            400
        );
    }

    public function testUpdateStatusOrderNotFound(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(null);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'confirmed',
            ]),
            404
        );
    }

    public function testUpdateStatusWrongSeller(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 100])
        );

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'confirmed',
            ]),
            403
        );
    }

    public function testUpdateStatusRepositoryFailure(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99])
        );
        $this->orderRepo->method('updateStatus')->willReturn(false);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'confirmed',
            ]),
            500
        );
    }

    public function testUpdateStatusConfirmed(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99, 'Buyer_ID' => 7])
        );
        $this->orderRepo->method('updateStatus')->willReturn(true);

        $this->notification->expects($this->once())
            ->method('send')
            ->with(
                7,
                'Đơn hàng của bạn Đã xác nhận',
                $this->stringContains('Đã xác nhận')
            )
            ->willReturn(true);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'confirmed',
            ]),
            200
        );
    }

    public function testUpdateStatusCompleted(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99, 'Buyer_ID' => 7])
        );
        $this->orderRepo->method('updateStatus')->willReturn(true);

        $this->notification->expects($this->once())
            ->method('send')
            ->with(
                7,
                'Đơn hàng của bạn Hoàn thành',
                $this->stringContains('Hoàn thành')
            )
            ->willReturn(true);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'completed',
            ]),
            200
        );
    }

    public function testUpdateStatusCancelled(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99, 'Buyer_ID' => 7])
        );
        $this->orderRepo->method('updateStatus')->willReturn(true);

        $this->notification->expects($this->once())
            ->method('send')
            ->with(
                7,
                'Đơn hàng của bạn Đã hủy',
                $this->stringContains('Đã hủy')
            )
            ->willReturn(true);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'cancelled',
            ]),
            200
        );
    }

    public function testUpdateStatusOtherStatusKeepsOriginalLabel(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99, 'Buyer_ID' => 7])
        );
        $this->orderRepo->method('updateStatus')->willReturn(true);

        $this->notification->expects($this->once())
            ->method('send')
            ->with(
                7,
                'Đơn hàng của bạn archived',
                $this->stringContains('archived')
            )
            ->willReturn(true);

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'archived',
            ]),
            200
        );
    }

    public function testUpdateStatusGuestOrderDoesNotNotifyBuyer(): void
    {
        $_SESSION = ['user_id' => 99];

        $this->orderRepo->method('findById')->willReturn(
            $this->order(['Seller_ID' => 99, 'Buyer_ID' => null])
        );
        $this->orderRepo->method('updateStatus')->willReturn(true);

        $this->notification->expects($this->never())->method('send');

        $this->assertCode(
            $this->service->updateStatus([
                'order_id' => 20,
                'status' => 'confirmed',
            ]),
            200
        );
    }

    // ============================================================
    // F06 - trackOrder()
    // ============================================================

    public function testTrackOrderNotFound(): void
    {
        $this->orderRepo->method('findByCode')->willReturn(null);

        $this->assertCode(
            $this->service->trackOrder('DH-NOT-FOUND'),
            404
        );
    }

    public function testTrackOrderFoundMapsAllFields(): void
    {
        $order = $this->order();

        $this->orderRepo->expects($this->once())
            ->method('findByCode')
            ->with('DH260912ABC123')
            ->willReturn($order);

        $result = $this->service->trackOrder('  DH260912ABC123  ');

        $this->assertCode($result, 200);
        $this->assertSame(20, $result['data']['id']);
        $this->assertSame('DH260912ABC123', $result['data']['order_code']);
        $this->assertSame('Ao khoac', $result['data']['product_name']);
        $this->assertSame('ao.jpg', $result['data']['product_image']);
        $this->assertSame(100000, $result['data']['total_price']);
        $this->assertSame('123 Nguyen Trai', $result['data']['shipping_address']);
        $this->assertSame('pending', $result['data']['status']);
        $this->assertSame('COD', $result['data']['payment_method']);
        $this->assertSame('pending', $result['data']['payment_status']);
        $this->assertSame('2026-09-12 10:00:00', $result['data']['created_at']);
    }
}
