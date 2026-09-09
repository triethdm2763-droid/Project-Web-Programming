<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductWhiteBoxTest extends TestCase
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

    #[DataProvider('whiteBoxPathsProvider')]
    public function testProductWhiteBoxCoverage($testId, $input, $expectedStatus)
    {
        $actualStatus = $this->validateProductData($input);
        $this->assertEquals($expectedStatus, $actualStatus, "Failed at White-box Test Case: $testId");
    }

    public static function whiteBoxPathsProvider()
    {
        return [
            'PRODUCT-P1 (Path 1: Name Empty)' => [
                'PRODUCT-P1', 
                ['name' => '', 'price' => 50000, 'quantity' => 10], 
                400
            ],
            'PRODUCT-P2 (Path 2: Price Invalid)' => [
                'PRODUCT-P2', 
                ['name' => 'Valid Name', 'price' => 0, 'quantity' => 10], 
                400
            ],
            'PRODUCT-P3 (Path 3: Quantity Invalid)' => [
                'PRODUCT-P3', 
                ['name' => 'Valid Name', 'price' => 50000, 'quantity' => 0], 
                400
            ],
            'PRODUCT-P4 (Path 4: All Valid)' => [
                'PRODUCT-P4', 
                ['name' => 'Valid Name', 'price' => 50000, 'quantity' => 10], 
                201
            ],
            'PRODUCT-P5 (Path 5: Name Too Long)' => [
                'PRODUCT-P5', 
                ['name' => str_repeat('a', 256), 'price' => 50000, 'quantity' => 10], 
                400
            ],
        ];
    }
}