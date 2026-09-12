<?php

namespace Tests;

use App\Services\OrderService;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OrderStateTransitionTest extends TestCase
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

        // Tạo mocks
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->productRepo = $this->createMock(ProductRepository::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->notification = $this->createMock(NotificationService::class);

        // Notification mặc định không gây lỗi test
        $this->notification
            ->method('send')
            ->willReturn(true);

        // Tạo service
        $this->service = new OrderService();

        // Inject dependencies
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

    /**
     * Inject mock vào private property của OrderService
     */
    private function inject(string $property, $value): void
    {
        $ref = new ReflectionClass(OrderService::class);

        $propertyRef = $ref->getProperty($property);
        $propertyRef->setAccessible(true);
        $propertyRef->setValue($this->service, $value);
    }

    /**
     * Order fixture
     */
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

    /**
     * Product fixture
     */
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

    /**
     * Assert HTTP response code
     */
    private function assertCode(array $result, int $expected): void
    {
        $this->assertSame(
            $expected,
            $result['code'] ?? null
        );
    }


    // ============================================================
    // ORDER-ST-01
    // Không có order -> Checkout hợp lệ -> pending
    // ============================================================

    public function testST01CheckoutCreatesPendingOrder(): void
    {
        $_SESSION = [];

        $this->productRepo
            ->method('findById')
            ->willReturn($this->product());

        $this->orderRepo
            ->method('createWithTransaction')
            ->willReturn(101);

        $result = $this->service->checkout([
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '123 Nguyen Trai, Quan 1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ]);

        $this->assertCode($result, 201);
    }


    // ============================================================
    // ORDER-ST-02
    // pending -> Buyer hủy -> cancelled
    // ============================================================

    public function testST02PendingBuyerCancelToCancelled(): void
    {
        $_SESSION = [
            'user_id' => 7,
            'username' => 'buyer'
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'pending'
                ])
            );

        $this->orderRepo
            ->expects($this->once())
            ->method('cancelWithTransaction')
            ->with(20, 10, 1);

        $this->notification
            ->method('send')
            ->willReturn(true);

        $result = $this->service->cancelOrder([
            'order_id' => 20
        ]);

        $this->assertCode($result, 200);
    }


    // ============================================================
    // ORDER-ST-03
    // confirmed -> Buyer hủy -> giữ confirmed
    // ============================================================

    public function testST03ConfirmedBuyerCancelRejected(): void
    {
        $_SESSION = [
            'user_id' => 7
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'confirmed'
                ])
            );

        // Không được gọi cancelWithTransaction
        $this->orderRepo
            ->expects($this->never())
            ->method('cancelWithTransaction');

        $result = $this->service->cancelOrder([
            'order_id' => 20
        ]);

        $this->assertCode($result, 400);
    }


    // ============================================================
    // ORDER-ST-04
    // completed -> Buyer hủy -> giữ completed
    // ============================================================

    public function testST04CompletedBuyerCancelRejected(): void
    {
        $_SESSION = [
            'user_id' => 7
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'completed'
                ])
            );

        $this->orderRepo
            ->expects($this->never())
            ->method('cancelWithTransaction');

        $result = $this->service->cancelOrder([
            'order_id' => 20
        ]);

        $this->assertCode($result, 400);
    }


    // ============================================================
    // ORDER-ST-05
    // cancelled -> Buyer hủy -> giữ cancelled
    // ============================================================

    public function testST05CancelledBuyerCancelRejected(): void
    {
        $_SESSION = [
            'user_id' => 7
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'cancelled'
                ])
            );

        $this->orderRepo
            ->expects($this->never())
            ->method('cancelWithTransaction');

        $result = $this->service->cancelOrder([
            'order_id' => 20
        ]);

        $this->assertCode($result, 400);
    }


    // ============================================================
    // ORDER-ST-06
    // pending -> Seller cập nhật confirmed
    // ============================================================

    public function testST06PendingSellerConfirmToConfirmed(): void
    {
        $_SESSION = [
            'user_id' => 99
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'pending',
                    'Seller_ID' => 99,
                    'Buyer_ID' => 7
                ])
            );

        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(20, 'confirmed')
            ->willReturn(true);

        $this->notification
            ->method('send')
            ->willReturn(true);

        $result = $this->service->updateStatus([
            'order_id' => 20,
            'status' => 'confirmed'
        ]);

        $this->assertCode($result, 200);
    }


    // ============================================================
    // ORDER-ST-07
    // confirmed -> Seller cập nhật completed
    // ============================================================

    public function testST07ConfirmedSellerCompleteToCompleted(): void
    {
        $_SESSION = [
            'user_id' => 99
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'confirmed',
                    'Seller_ID' => 99,
                    'Buyer_ID' => 7
                ])
            );

        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(20, 'completed')
            ->willReturn(true);

        $this->notification
            ->method('send')
            ->willReturn(true);

        $result = $this->service->updateStatus([
            'order_id' => 20,
            'status' => 'completed'
        ]);

        $this->assertCode($result, 200);
    }


    // ============================================================
    // ORDER-ST-GAP-01
    // completed -> Seller cập nhật confirmed
    //
    // EXPECTED:
    // Giữ completed / từ chối
    //
    // CURRENT SOURCE:
    // Cho phép update -> confirmed
    // => GAP
    // ============================================================

    public function testSTGap01CompletedSellerChangesToConfirmed(): void
    {
        $_SESSION = [
            'user_id' => 99
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'completed',
                    'Seller_ID' => 99,
                    'Buyer_ID' => 7
                ])
            );

        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(20, 'confirmed')
            ->willReturn(true);

        $this->notification
            ->method('send')
            ->willReturn(true);

        $result = $this->service->updateStatus([
            'order_id' => 20,
            'status' => 'confirmed'
        ]);

        /*
         * Source hiện tại cho phép chuyển completed -> confirmed.
         * Vì vậy HTTP 200 là hành vi hiện tại.
         */
        $this->assertCode($result, 200);
    }


    // ============================================================
    // ORDER-ST-GAP-02
    // pending -> Seller cập nhật archived
    //
    // EXPECTED:
    // Giữ pending / từ chối
    //
    // CURRENT SOURCE:
    // Cho phép archived
    // => GAP
    // ============================================================

    public function testSTGap02PendingSellerChangesToArchived(): void
    {
        $_SESSION = [
            'user_id' => 99
        ];

        $this->orderRepo
            ->method('findById')
            ->willReturn(
                $this->order([
                    'Status' => 'pending',
                    'Seller_ID' => 99,
                    'Buyer_ID' => 7
                ])
            );

        $this->orderRepo
            ->expects($this->once())
            ->method('updateStatus')
            ->with(20, 'archived')
            ->willReturn(true);

        $this->notification
            ->method('send')
            ->willReturn(true);

        $result = $this->service->updateStatus([
            'order_id' => 20,
            'status' => 'archived'
        ]);

        /*
         * Source hiện tại không có status allowlist
         * nên archived vẫn được cập nhật.
         */
        $this->assertCode($result, 200);
    }
}