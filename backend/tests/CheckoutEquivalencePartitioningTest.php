<?php

namespace Tests;

use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class CheckoutEquivalencePartitioningTest extends TestCase
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
            'shipping_address' => '123456789012345',
            'payment_method'   => 'COD',
            'quantity'         => 1,
            'fullname'         => 'Nguyen Van A',
            'phone'            => '0901234567',
        ];
    }

    // =========================================================
    // EP-01: product_id - Product tồn tại
    // Expected: 201
    // =========================================================
    public function testEP01_Product_Exists(): void
    {
        $data = $this->validData();

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-02: product_id - Không truyền
    // Expected: 400
    // =========================================================
    public function testEP02_Product_Missing(): void
    {
        $data = $this->validData();

        unset($data['product_id']);

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-03: product_id - Không tồn tại
    // Expected: 404
    // =========================================================
    public function testEP03_Product_NotFound(): void
    {
        $data = $this->validData();

        $data['product_id'] = 999999999;

        $result = $this->service->checkout($data);

        $this->assertSame(404, $result['code']);
    }

    // =========================================================
    // EP-04: shipping_address - >= 10 ký tự
    // Expected: 201
    // =========================================================
    public function testEP04_Address_Valid(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '123456789012345';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-05: shipping_address - 1 đến 9 ký tự
    // Expected: 400
    // =========================================================
    public function testEP05_Address_TooShort(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '123456789';

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-06: shipping_address - Rỗng
    // Expected: 400
    // =========================================================
    public function testEP06_Address_Empty(): void
    {
        $data = $this->validData();

        $data['shipping_address'] = '';

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-07: payment_method - Có giá trị
    // Expected: 201
    // =========================================================
    public function testEP07_PaymentMethod_Valid(): void
    {
        $data = $this->validData();

        $data['payment_method'] = 'COD';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-08: payment_method - Rỗng
    // Expected: 400
    // =========================================================
    public function testEP08_PaymentMethod_Empty(): void
    {
        $data = $this->validData();

        $data['payment_method'] = '';

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-09: quantity - Không truyền
    // Expected: 201
    // =========================================================
    public function testEP09_Quantity_Missing(): void
    {
        $data = $this->validData();

        unset($data['quantity']);

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-10: quantity - >= 1
    // Expected: 201
    // =========================================================
    public function testEP10_Quantity_Valid(): void
    {
        $data = $this->validData();

        $data['quantity'] = 1;

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-11: quantity - < 1
    // Source tự chuyển về quantity = 1
    // Expected: 201
    // =========================================================
    public function testEP11_Quantity_LessThanOne(): void
    {
        $data = $this->validData();

        $data['quantity'] = 0;

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-12: fullname - Có giá trị
    // Expected: 201
    // =========================================================
    public function testEP12_Fullname_Valid(): void
    {
        $data = $this->validData();

        $data['fullname'] = 'Nguyen Van A';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-13: fullname - Rỗng
    // Expected: 400
    // =========================================================
    public function testEP13_Fullname_Empty(): void
    {
        $data = $this->validData();

        $data['fullname'] = '';

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-14: phone - Có giá trị hợp lệ
    // Expected: 201
    // =========================================================
    public function testEP14_Phone_Valid(): void
    {
        $data = $this->validData();

        $data['phone'] = '0901234567';

        $result = $this->service->checkout($data);

        $this->assertSame(201, $result['code']);
    }

    // =========================================================
    // EP-15: phone - Rỗng
    // Expected: 400
    // =========================================================
    public function testEP15_Phone_Empty(): void
    {
        $data = $this->validData();

        $data['phone'] = '';

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }

    // =========================================================
    // EP-16: phone - Không truyền
    // Expected: 400
    // =========================================================
    public function testEP16_Phone_Missing(): void
    {
        $data = $this->validData();

        unset($data['phone']);

        $result = $this->service->checkout($data);

        $this->assertSame(400, $result['code']);
    }
}