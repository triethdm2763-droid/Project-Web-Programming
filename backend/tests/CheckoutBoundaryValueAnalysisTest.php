<?php

namespace Tests;

use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class CheckoutBoundaryValueAnalysisTest extends TestCase
{
    private OrderService $service;

    private int $validProductId = 3;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        $this->service = new OrderService();
    }

    private function validData(): array
    {
        return [
            'product_id'       => $this->validProductId,
            'shipping_address' => '12345678901234567890',
            'payment_method'   => 'COD',
            'quantity'         => 1,
            'fullname'         => 'Nguyen Van A',
            'phone'            => '0901234567',
        ];
    }

    // =========================================================
    // BVA-01: shipping_address - Min = 10
    // Expected: 201
    // =========================================================
    public function testBVA01_Address_Min(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '1234567890';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // BVA-02: shipping_address - Min + 1 = 11
    // Expected: 201
    // =========================================================
    public function testBVA02_Address_MinPlusOne(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '12345678901';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // BVA-03: shipping_address - Nominal = 20
    // Expected: 201
    // =========================================================
    public function testBVA03_Address_Nominal(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '12345678901234567890';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }
}