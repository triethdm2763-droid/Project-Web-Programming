<?php

use PHPUnit\Framework\TestCase;
use App\services\ProductService;

class ProductServiceTest extends TestCase
{
    /**
     * Test logic kiểm tra định dạng và dung lượng ảnh gửi lên
     */
    public function testValidateProductImage()
    {
        $service = new ProductService();
        
        // Kịch bản file lỗi (vượt dung lượng hoặc sai đuôi)
        $invalidImage = ['size' => 25000000, 'type' => 'text/php'];
        $result = $service->validateImage($invalidImage);
        
        // Khẳng định logic dịch vụ phải trả về false hoặc báo lỗi
        $this->assertFalse($result);
    }
}
