<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\OrderService;

class OrderStateTransitionTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        $this->orderService = new OrderService();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];

        parent::tearDown();
    }

    /**
     * ST-ORDER-01
     * pending -> confirmed
     */
    public function testPendingToConfirmed(): void
    {
        $_SESSION['user_id'] = 2;

        $orderId = 3;

        // Start State
        $this->setOrderStatus($orderId, 'pending');

        // Event
        $response = $this->orderService->updateStatus([
            'order_id' => $orderId,
            'status'   => 'confirmed'
        ]);

        // Expected response
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('success', $response['status']);

        // Actual End State
        $actualState = $this->getOrderStatus($orderId);

        $this->assertEquals('confirmed', $actualState);
    }

    /**
     * ST-ORDER-02
     * pending -> cancelled
     */
    public function testPendingToCancelled(): void
    {
        $_SESSION['user_id'] = 8;

        $orderId = 3;

        // Đảm bảo Start State = pending
        $this->setOrderStatus($orderId, 'pending');

        $response = $this->orderService->cancelOrder([
            'order_id' => $orderId
        ]);

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('success', $response['status']);

        $actualState = $this->getOrderStatus($orderId);

        $this->assertEquals('cancelled', $actualState);
    }

    /**
     * ST-ORDER-03
     * confirmed -> completed
     */
    public function testConfirmedToCompleted(): void
    {
        $_SESSION['user_id'] = 2;

        $orderId = 4;

        // Đảm bảo Start State = confirmed
        $this->setOrderStatus($orderId, 'confirmed');

        $response = $this->orderService->updateStatus([
            'order_id' => $orderId,
            'status'   => 'completed'
        ]);

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('success', $response['status']);

        $actualState = $this->getOrderStatus($orderId);

        $this->assertEquals('completed', $actualState);
    }

    /**
     * ST-ORDER-04
     * confirmed -> cancelled
     * Invalid transition
     */
    public function testConfirmedCannotBeCancelled(): void
    {
        $_SESSION['user_id'] = 9;

        $orderId = 4;

        // Đảm bảo Start State = confirmed
        $this->setOrderStatus($orderId, 'confirmed');

        $response = $this->orderService->cancelOrder([
            'order_id' => $orderId
        ]);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('error', $response['status']);

        $actualState = $this->getOrderStatus($orderId);

        // Trạng thái phải giữ nguyên
        $this->assertEquals('confirmed', $actualState);
    }

    /**
     * ST-ORDER-05
     * completed -> cancelled
     * Invalid transition
     */
    public function testCompletedCannotBeCancelled(): void
    {
        $_SESSION['user_id'] = 6;

        $orderId = 1;

        // Đảm bảo Start State = completed
        $this->setOrderStatus($orderId, 'completed');

        $response = $this->orderService->cancelOrder([
            'order_id' => $orderId
        ]);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('error', $response['status']);

        $actualState = $this->getOrderStatus($orderId);

        // Trạng thái phải giữ nguyên
        $this->assertEquals('completed', $actualState);
    }

    /**
     * Lấy trạng thái hiện tại của Order
     */
    private function getOrderStatus(int $orderId): string
    {
        $reflection = new \ReflectionClass($this->orderService);

        $property = $reflection->getProperty('orderRepository');
        $property->setAccessible(true);

        $repository = $property->getValue($this->orderService);

        $order = $repository->findById($orderId);

        $this->assertNotNull($order);

        return $order['Status'];
    }

    /**
     * Set trạng thái trước khi chạy test
     */
    private function setOrderStatus(int $orderId, string $status): void
    {
        $reflection = new \ReflectionClass($this->orderService);

        $property = $reflection->getProperty('orderRepository');
        $property->setAccessible(true);

        $repository = $property->getValue($this->orderService);

        $result = $repository->updateStatus($orderId, $status);

        $this->assertTrue($result);
    }
}