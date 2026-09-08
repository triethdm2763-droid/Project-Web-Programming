<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Services\OrderService;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Control-flow scope: OrderService::checkout(), cancelOrder(), updateStatus().
 * Each data set is a distinct executable path. Assertions verify both the
 * response and the repository/notification action reached on that path.
 */
final class WhiteBoxOrderServiceTest extends TestCase
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

    public static function checkoutPaths(): array
    {
        return [
            'ORDER-WB-CHK-01 validation error' => [[
                'changes' => ['product_id' => '__UNSET__'], 'code' => 400, 'error' => 'product_id',
            ]],
            'ORDER-WB-CHK-02 quantity default' => [[
                'changes' => ['quantity' => '__UNSET__'], 'code' => 201, 'quantity' => 1, 'notifications' => 1,
            ]],
            'ORDER-WB-CHK-03 quantity below one normalized' => [[
                'changes' => ['quantity' => 0], 'code' => 201, 'quantity' => 1, 'notifications' => 1,
            ]],
            'ORDER-WB-CHK-04 invalid buyer session becomes guest' => [[
                'loggedIn' => true, 'userResults' => [null], 'code' => 201, 'quantity' => 1,
                'notifications' => 1, 'sessionCleared' => true, 'cookieToken' => true,
            ]],
            'ORDER-WB-CHK-05 product not found' => [[
                'product' => null, 'code' => 404,
            ]],
            'ORDER-WB-CHK-06 inactive product' => [[
                'product' => ['Status' => 'inactive'], 'code' => 400,
            ]],
            'ORDER-WB-CHK-07 active product with zero stock' => [[
                'product' => ['Stock_quantity' => 0], 'code' => 400,
            ]],
            'ORDER-WB-CHK-08 quantity exceeds stock' => [[
                'changes' => ['quantity' => 6], 'code' => 400,
            ]],
            'ORDER-WB-CHK-09 self purchase rejected' => [[
                'loggedIn' => true, 'userResults' => [['ID' => 7]],
                'product' => ['Seller_ID' => 7], 'code' => 400,
            ]],
            'ORDER-WB-CHK-10 guest without fullname' => [[
                'changes' => ['fullname' => ''], 'code' => 201, 'quantity' => 1,
                'notifications' => 1, 'buyerName' => 'Khách vãng lai',
            ]],
            'ORDER-WB-CHK-11 guest with fullname' => [[
                'code' => 201, 'quantity' => 1, 'notifications' => 1,
                'buyerName' => 'Khách vãng lai (Nguyen Van A)',
            ]],
            'ORDER-WB-CHK-12 logged buyer updates profile' => [[
                'loggedIn' => true, 'userResults' => [['ID' => 7], ['ID' => 7]],
                'code' => 201, 'quantity' => 1, 'profileUpdates' => 1, 'notifications' => 2,
            ]],
            'ORDER-WB-CHK-13 second user lookup is null' => [[
                'loggedIn' => true, 'userResults' => [['ID' => 7], null],
                'code' => 201, 'quantity' => 1, 'profileUpdates' => 0, 'notifications' => 2,
            ]],
            'ORDER-WB-CHK-14 non-COD payment is marked success' => [[
                'changes' => ['payment_method' => 'Bank Transfer'], 'code' => 201,
                'quantity' => 1, 'paymentStatus' => 'success', 'notifications' => 1,
            ]],
            'ORDER-WB-CHK-15 transaction exception' => [[
                'transactionException' => true, 'code' => 500, 'notifications' => 0,
            ]],
            'ORDER-WB-CHK-16 profile update selected by phone' => [[
                'loggedIn' => true,
                'userResults' => [
                    ['ID' => 7],
                    ['ID' => 7, 'Fullname' => 'Existing Buyer', 'Phone' => '0800000000', 'Address' => 'Old address'],
                ],
                'changes' => ['fullname' => ''], 'code' => 201, 'quantity' => 1,
                'profileUpdates' => 1, 'notifications' => 2,
                'profileData' => ['fullname' => 'Existing Buyer', 'phone' => '0901234567', 'address' => '123 Nguyen Trai, District 1'],
            ]],
            'ORDER-WB-CHK-17 profile update selected by address' => [[
                'loggedIn' => true,
                'userResults' => [
                    ['ID' => 7],
                    ['ID' => 7, 'Fullname' => 'Existing Buyer', 'Phone' => '0800000000', 'Address' => 'Old address'],
                ],
                'changes' => ['fullname' => '', 'phone' => ''], 'code' => 201, 'quantity' => 1,
                'profileUpdates' => 1, 'notifications' => 2,
                'profileData' => ['fullname' => 'Existing Buyer', 'phone' => '0800000000', 'address' => '123 Nguyen Trai, District 1'],
            ]],
        ];
    }

    #[DataProvider('checkoutPaths')]
    public function testCheckoutControlFlowPath(array $case): void
    {
        $data = $this->checkoutData();
        foreach ($case['changes'] ?? [] as $field => $value) {
            if ($value === '__UNSET__') {
                unset($data[$field]);
            } else {
                $data[$field] = $value;
            }
        }

        if ($case['loggedIn'] ?? false) {
            $_SESSION = ['user_id' => 7, 'username' => 'Buyer A'];
        }
        if ($case['cookieToken'] ?? false) {
            $_COOKIE['token'] = 'expired-test-token';
        }

        [$service, $orders, $products, $users, $notifications] = $this->checkoutRig($case);
        $result = $service->checkout($data);

        self::assertSame($case['code'], $result['code']);
        self::assertSame($case['code'] === 201 ? 'success' : 'error', $result['status']);
        if (isset($case['error'])) {
            self::assertArrayHasKey($case['error'], $result['errors']);
        }

        $expectedCreateCalls = ($case['code'] === 201 || ($case['transactionException'] ?? false)) ? 1 : 0;
        self::assertSame($expectedCreateCalls, $orders->createCalls);
        self::assertSame($case['notifications'] ?? 0, count($notifications->sent));
        self::assertSame($case['profileUpdates'] ?? 0, count($users->profileUpdates));

        if ($case['code'] === 201) {
            self::assertSame(801, $result['order_id']);
            self::assertSame($case['quantity'], $orders->createdOrder['quantity']);
            self::assertSame('pending', $orders->createdOrder['status']);
            self::assertSame(
                $case['paymentStatus'] ?? 'pending',
                $orders->createdPayment['status']
            );
        }
        if (isset($case['buyerName'])) {
            self::assertStringContainsString($case['buyerName'], $notifications->sent[0]['content']);
        }
        if ($case['sessionCleared'] ?? false) {
            self::assertArrayNotHasKey('user_id', $_SESSION);
        }
        if (isset($case['profileData'])) {
            self::assertSame($case['profileData'], $users->profileUpdates[0]['data']);
        }
        self::assertGreaterThanOrEqual(0, $products->findCalls);
    }

    public function testCheckoutStartsSessionWhenNoneIsActive(): void
    {
        $this->closeSessionForSourceBranch();
        [$service, $orders] = $this->checkoutRig([]);

        $result = $service->checkout($this->checkoutData());

        self::assertSame(201, $result['code']);
        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame(1, $orders->createCalls);
    }

    public static function cancelPaths(): array
    {
        return [
            'ORDER-WB-CAN-01 unauthenticated' => [['authenticated' => false, 'code' => 401]],
            'ORDER-WB-CAN-02 missing order id' => [['data' => [], 'code' => 400]],
            'ORDER-WB-CAN-03 order not found' => [['order' => null, 'code' => 404]],
            'ORDER-WB-CAN-04 wrong buyer' => [['order' => ['Buyer_ID' => 2], 'code' => 403]],
            'ORDER-WB-CAN-05 non-pending rejected' => [['order' => ['Status' => 'confirmed'], 'code' => 400]],
            'ORDER-WB-CAN-06 pending cancellation' => [['order' => ['Quantity' => 2], 'code' => 200, 'restore' => 2, 'notifications' => 2]],
            'ORDER-WB-CAN-07 missing quantity defaults to one' => [['order' => ['Quantity' => '__UNSET__'], 'code' => 200, 'restore' => 1, 'notifications' => 2]],
            'ORDER-WB-CAN-08 transaction exception' => [['cancelException' => true, 'code' => 500]],
        ];
    }

    #[DataProvider('cancelPaths')]
    public function testCancelOrderControlFlowPath(array $case): void
    {
        if ($case['authenticated'] ?? true) {
            $_SESSION = ['user_id' => 1, 'username' => 'Buyer A'];
        }
        [$service, $orders, $notifications] = $this->orderMutationRig($case);
        $result = $service->cancelOrder($case['data'] ?? ['order_id' => 20]);

        self::assertSame($case['code'], $result['code']);
        self::assertSame($case['code'] === 200 ? 'success' : 'error', $result['status']);
        $expectedCancelCalls = in_array($case['code'], [200, 500], true) ? 1 : 0;
        self::assertSame($expectedCancelCalls, $orders->cancelCalls);
        self::assertSame($case['notifications'] ?? 0, count($notifications->sent));
        if (isset($case['restore'])) {
            self::assertSame([20, 10, $case['restore']], $orders->lastCancelArgs);
        }
    }

    public function testCancelOrderStartsSessionWhenNoneIsActive(): void
    {
        $this->closeSessionForSourceBranch();
        [$service, $orders] = $this->orderMutationRig([]);

        $result = $service->cancelOrder(['order_id' => 20]);

        self::assertSame(401, $result['code']);
        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame(0, $orders->cancelCalls);
    }

    public static function updatePaths(): array
    {
        return [
            'ORDER-WB-UPD-01 unauthenticated' => [['authenticated' => false, 'code' => 401]],
            'ORDER-WB-UPD-02 missing order id' => [['data' => ['status' => 'confirmed'], 'code' => 400]],
            'ORDER-WB-UPD-03 missing status' => [['data' => ['order_id' => 20], 'code' => 400]],
            'ORDER-WB-UPD-04 order not found' => [['order' => null, 'code' => 404]],
            'ORDER-WB-UPD-05 wrong seller' => [['order' => ['Seller_ID' => 88], 'code' => 403]],
            'ORDER-WB-UPD-06 repository failure' => [['updateResult' => false, 'code' => 500]],
            'ORDER-WB-UPD-07 confirmed label' => [['status' => 'confirmed', 'code' => 200, 'label' => 'Đã xác nhận', 'notifications' => 1]],
            'ORDER-WB-UPD-08 completed label' => [['status' => 'completed', 'code' => 200, 'label' => 'Hoàn thành', 'notifications' => 1]],
            'ORDER-WB-UPD-09 cancelled label' => [['status' => 'cancelled', 'code' => 200, 'label' => 'Đã hủy', 'notifications' => 1]],
            'ORDER-WB-UPD-10 arbitrary status uses raw label' => [['status' => 'archived', 'code' => 200, 'label' => 'archived', 'notifications' => 1]],
            'ORDER-WB-UPD-11 guest order skips buyer notification' => [['order' => ['Buyer_ID' => null], 'code' => 200, 'notifications' => 0]],
        ];
    }

    #[DataProvider('updatePaths')]
    public function testUpdateStatusControlFlowPath(array $case): void
    {
        if ($case['authenticated'] ?? true) {
            $_SESSION = ['user_id' => 99, 'username' => 'Seller A'];
        }
        [$service, $orders, $notifications] = $this->orderMutationRig($case);
        $status = $case['status'] ?? 'confirmed';
        $result = $service->updateStatus($case['data'] ?? ['order_id' => 20, 'status' => $status]);

        self::assertSame($case['code'], $result['code']);
        self::assertSame($case['code'] === 200 ? 'success' : 'error', $result['status']);
        $expectedUpdateCalls = in_array($case['code'], [200, 500], true) ? 1 : 0;
        self::assertSame($expectedUpdateCalls, $orders->updateCalls);
        self::assertSame($case['notifications'] ?? 0, count($notifications->sent));
        if ($case['code'] === 200) {
            self::assertSame([20, $status], $orders->lastUpdateArgs);
        }
        if (isset($case['label'])) {
            self::assertStringContainsString($case['label'], $notifications->sent[0]['title']);
            self::assertStringContainsString($case['label'], $notifications->sent[0]['content']);
        }
    }

    public function testUpdateStatusStartsSessionWhenNoneIsActive(): void
    {
        $this->closeSessionForSourceBranch();
        [$service, $orders] = $this->orderMutationRig([]);

        $result = $service->updateStatus(['order_id' => 20, 'status' => 'confirmed']);

        self::assertSame(401, $result['code']);
        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame(0, $orders->updateCalls);
    }

    private function checkoutRig(array $case): array
    {
        $product = array_key_exists('product', $case) ? $case['product'] : [];
        if ($product !== null) {
            $product = array_merge($this->product(), $product);
        }

        $orders = new class((bool)($case['transactionException'] ?? false)) extends OrderRepository {
            public int $createCalls = 0;
            public ?array $createdOrder = null;
            public ?array $createdPayment = null;
            public bool $throwOnCreate;
            public function __construct(bool $throwOnCreate) { $this->throwOnCreate = $throwOnCreate; }
            public function createWithTransaction(array $orderData, array $paymentData): int
            {
                $this->createCalls++;
                $this->createdOrder = $orderData;
                $this->createdPayment = $paymentData;
                if ($this->throwOnCreate) throw new Exception('Simulated transaction failure');
                return 801;
            }
        };
        $products = new class($product) extends ProductRepository {
            public ?array $product;
            public int $findCalls = 0;
            public function __construct(?array $product) { $this->product = $product; }
            public function findById(int $id) { $this->findCalls++; return $this->product; }
        };
        $users = new class($case['userResults'] ?? []) extends UserRepository {
            public array $results;
            public array $profileUpdates = [];
            public function __construct(array $results) { $this->results = $results; }
            public function findById(int $id) { return array_shift($this->results); }
            public function updateProfile(int $id, array $data): bool
            {
                $this->profileUpdates[] = ['id' => $id, 'data' => $data];
                return true;
            }
        };
        $notifications = $this->notificationDouble();
        return [$this->serviceWith($orders, $products, $users, $notifications), $orders, $products, $users, $notifications];
    }

    private function orderMutationRig(array $case): array
    {
        $order = array_key_exists('order', $case) ? $case['order'] : [];
        if ($order !== null) {
            $order = array_merge($this->order(), $order);
            if (($case['order']['Quantity'] ?? null) === '__UNSET__') {
                unset($order['Quantity']);
            }
        }

        $orders = new class($order, (bool)($case['cancelException'] ?? false), (bool)($case['updateResult'] ?? true)) extends OrderRepository {
            public ?array $foundOrder;
            public bool $throwOnCancel;
            public bool $updateResult;
            public int $cancelCalls = 0;
            public int $updateCalls = 0;
            public ?array $lastCancelArgs = null;
            public ?array $lastUpdateArgs = null;
            public function __construct(?array $order, bool $throwOnCancel, bool $updateResult)
            {
                $this->foundOrder = $order;
                $this->throwOnCancel = $throwOnCancel;
                $this->updateResult = $updateResult;
            }
            public function findById(int $id) { return $this->foundOrder; }
            public function cancelWithTransaction(int $orderId, int $productId, int $quantity = 1)
            {
                $this->cancelCalls++;
                $this->lastCancelArgs = [$orderId, $productId, $quantity];
                if ($this->throwOnCancel) throw new Exception('Simulated cancellation failure');
            }
            public function updateStatus(int $id, string $status): bool
            {
                $this->updateCalls++;
                $this->lastUpdateArgs = [$id, $status];
                return $this->updateResult;
            }
        };
        $notifications = $this->notificationDouble();
        $service = $this->serviceWith(
            $orders,
            $this->createMock(ProductRepository::class),
            $this->createMock(UserRepository::class),
            $notifications
        );
        return [$service, $orders, $notifications];
    }

    private function notificationDouble(): NotificationService
    {
        return new class extends NotificationService {
            public array $sent = [];
            public function __construct() {}
            public function send(int $userId, string $title, string $content, $dbTransaction = null): bool
            {
                $this->sent[] = compact('userId', 'title', 'content');
                return true;
            }
        };
    }

    private function checkoutData(): array
    {
        return [
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '123 Nguyen Trai, District 1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
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

    private function order(): array
    {
        return [
            'ID' => 20,
            'Buyer_ID' => 1,
            'Seller_ID' => 99,
            'Product_ID' => 10,
            'ProductName' => 'Test Product',
            'Quantity' => 1,
            'Status' => 'pending',
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
            session_id('phpunit-order-whitebox-' . getmypid());
            if (!session_start(['use_cookies' => false, 'cache_limiter' => ''])) {
                self::fail('Không thể khởi tạo session kiểm thử Order White-box.');
            }
        }
    }

    private function closeSessionForSourceBranch(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $_SESSION = [];
    }
}
