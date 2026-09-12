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

final class OrderStateTransitionTest extends TestCase
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

    public function testCheckoutCreatesOrderInPendingState(): void
    {
        $orders = $this->createMock(OrderRepository::class);
        $products = $this->createMock(ProductRepository::class);
        $users = $this->createMock(UserRepository::class);
        $notifications = $this->createMock(NotificationService::class);

        $products->method('findById')->willReturn($this->product());
        $orders->expects(self::once())
            ->method('createWithTransaction')
            ->with(
                self::callback(function (array $orderData): bool {
                    self::assertSame('pending', $orderData['status']);
                    return true;
                }),
                self::isType('array')
            )
            ->willReturn(701);
        $notifications->expects(self::once())->method('send')->willReturn(true);

        $result = $this->serviceWith($orders, $products, $users, $notifications)->checkout($this->checkoutData());

        self::assertSame(201, $result['code']);
        self::assertSame(701, $result['order_id']);
    }

    public static function transitionCases(): array
    {
        return [
            'ORDER-ST-02 pending buyer cancel' => ['cancel', 'pending', 'cancelled', 200, 'cancelled', false],
            'ORDER-ST-03 confirmed buyer cancel rejected' => ['cancel', 'confirmed', 'cancelled', 400, 'confirmed', false],
            'ORDER-ST-04 completed buyer cancel rejected' => ['cancel', 'completed', 'cancelled', 400, 'completed', false],
            'ORDER-ST-05 cancelled buyer cancel rejected' => ['cancel', 'cancelled', 'cancelled', 400, 'cancelled', false],
            'ORDER-ST-06 pending seller confirms' => ['update', 'pending', 'confirmed', 200, 'confirmed', false],
            'ORDER-ST-07 confirmed seller completes' => ['update', 'confirmed', 'completed', 200, 'completed', false],
            // Characterization tests: current source has no transition matrix or status allowlist.
            'ORDER-ST-GAP-01 completed can return to confirmed' => ['update', 'completed', 'confirmed', 200, 'confirmed', true],
            'ORDER-ST-GAP-02 arbitrary status is accepted' => ['update', 'pending', 'archived', 200, 'archived', true],
        ];
    }

    #[DataProvider('transitionCases')]
    public function testOrderTransitionsAgainstCurrentSource(
        string $operation,
        string $startState,
        string $requestedState,
        int $expectedCode,
        string $expectedEndState,
        bool $isSourceGap
    ): void {
        $orders = new class($startState) extends OrderRepository {
            public string $state;
            public int $updateCalls = 0;
            public int $cancelCalls = 0;

            public function __construct(string $state)
            {
                $this->state = $state;
            }

            public function findById(int $id)
            {
                return [
                    'ID' => $id,
                    'Buyer_ID' => 1,
                    'Seller_ID' => 99,
                    'Product_ID' => 10,
                    'ProductName' => 'Test Product',
                    'Quantity' => 1,
                    'Status' => $this->state,
                ];
            }

            public function updateStatus(int $id, string $status): bool
            {
                $this->updateCalls++;
                $this->state = $status;
                return true;
            }

            public function cancelWithTransaction(int $orderId, int $productId, int $quantity = 1)
            {
                $this->cancelCalls++;
                $this->state = 'cancelled';
            }
        };
        $products = $this->createMock(ProductRepository::class);
        $users = $this->createMock(UserRepository::class);
        $notifications = $this->createMock(NotificationService::class);

        if ($operation === 'cancel') {
            $_SESSION = ['user_id' => 1, 'username' => 'Buyer A'];
            $notifications->expects($expectedCode === 200 ? self::exactly(2) : self::never())
                ->method('send')
                ->willReturn(true);
        } else {
            $_SESSION = ['user_id' => 99, 'username' => 'Seller A'];
            $notifications->expects(self::once())->method('send')->willReturn(true);
        }

        $service = $this->serviceWith($orders, $products, $users, $notifications);
        $result = $operation === 'cancel'
            ? $service->cancelOrder(['order_id' => 20])
            : $service->updateStatus(['order_id' => 20, 'status' => $requestedState]);

        self::assertSame($expectedCode, $result['code']);
        self::assertSame($expectedCode === 200 ? 'success' : 'error', $result['status']);
        self::assertSame($expectedEndState, $orders->state);

        if ($operation === 'cancel') {
            self::assertSame($expectedCode === 200 ? 1 : 0, $orders->cancelCalls);
            self::assertSame(0, $orders->updateCalls);
        } else {
            self::assertSame(1, $orders->updateCalls);
            self::assertSame(0, $orders->cancelCalls);
        }
        if ($isSourceGap) {
            self::assertSame(200, $result['code'], 'Current source accepts a transition outside the intended business model.');
        }
    }

    private function checkoutData(): array
    {
        return [
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '123 Nguyen Trai, District 1',
            'payment_method' => 'COD',
            'fullname' => 'Guest Buyer',
            'phone' => '0901234567',
        ];
    }

    private function product(): array
    {
        return [
            'ID' => 10,
            'Name' => 'Test Product',
            'Price' => 100000,
            'Stock_quantity' => 5,
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
            session_id('phpunit-order-state-' . getmypid());
            if (!session_start(['use_cookies' => false, 'cache_limiter' => ''])) {
                self::fail('Không thể khởi tạo session kiểm thử Order State Transition.');
            }
        }
    }
}
