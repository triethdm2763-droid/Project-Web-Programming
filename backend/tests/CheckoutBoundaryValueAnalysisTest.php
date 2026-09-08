<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\OrderService;

class CheckoutBoundaryValueAnalysisTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService();
    }

    /**
     * BVA-01: shipping_address = Min - 1
     * 9 ký tự -> phải bị reject
     */
    public function testBVA01ShippingAddressMinMinusOne(): void
    {
        $data = [
            'product_id'       => 3,
            'shipping_address' => '123456789',
            'payment_method'   => 'COD',
            'fullname'         => 'Nguyen Van A',
            'phone'            => '0901234567',
        ];

        $result = $this->orderService->checkout($data);

        $this->assertSame(400, $result['code']);
        $this->assertSame('error', $result['status']);
    }

    /**
     * BVA-02: shipping_address = Min
     * 10 ký tự -> hợp lệ
     */
    public function testBVA02ShippingAddressMin(): void
    {
        $data = [
            'product_id'       => 3,
            'shipping_address' => '1234567890',
            'payment_method'   => 'COD',
            'fullname'         => 'Nguyen Van A',
            'phone'            => '0901234567',
        ];

        $result = $this->orderService->checkout($data);

        $this->assertSame(201, $result['code']);
        $this->assertSame('success', $result['status']);
    }

    /**
     * BVA-03: shipping_address = Min + 1
     * 11 ký tự -> hợp lệ
     */
    public function testBVA03ShippingAddressMinPlusOne(): void
    {
        $data = [
            'product_id'       => 3,
            'shipping_address' => '12345678901',
            'payment_method'   => 'COD',
            'fullname'         => 'Nguyen Van A',
            'phone'            => '0901234567',
        ];

        $result = $this->orderService->checkout($data);

        $this->assertSame(201, $result['code']);
        $this->assertSame('success', $result['status']);
    }
}