<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
class ProductServiceTest extends TestCase
{
    private function validateProductData($data)
    {
        if (empty($data['name']) || strlen($data['name']) > 255) {
            return 400;
        }
        if (!isset($data['price']) || $data['price'] < 1 || $data['price'] > 999999999) {
            return 400;
        }
        if (!isset($data['quantity']) || $data['quantity'] < 1 || $data['quantity'] > 1000) {
            return 400;
        }
        return 201;
    }

    #[DataProvider('bvaDataProvider')]
    public function testProductBVAValues($testId, $price, $quantity, $expectedStatus)
    {
        $input = [
            'name' => 'BVA Test Product',
            'price' => $price,
            'quantity' => $quantity
        ];

        $actualStatus = $this->validateProductData($input);
        $this->assertEquals($expectedStatus, $actualStatus, "Failed at Test Case: $testId");
    }

    public static function bvaDataProvider()
    {
        return [
            'BVA-01 (Nominal)' => ['BVA-01', 50000, 500, 201],
            'BVA-02 (Price Min)' => ['BVA-02', 1, 500, 201],
            'BVA-03 (Price Min+)' => ['BVA-03', 2, 500, 201],
            'BVA-04 (Price Max-)' => ['BVA-04', 999999998, 500, 201],
            'BVA-05 (Price Max)' => ['BVA-05', 999999999, 500, 201],
            'BVA-06 (Qty Min)' => ['BVA-06', 50000, 1, 201],
            'BVA-07 (Qty Min+)' => ['BVA-07', 50000, 2, 201],
            'BVA-08 (Qty Max-)' => ['BVA-08', 50000, 999, 201],
            'BVA-09 (Qty Max)' => ['BVA-09', 50000, 1000, 201],
            'BVA-10 (Price Min-1 Invalid)' => ['BVA-10', 0, 500, 400],
            'BVA-11 (Qty Min-1 Invalid)' => ['BVA-11', 50000, 0, 400],
        ];
    }
}