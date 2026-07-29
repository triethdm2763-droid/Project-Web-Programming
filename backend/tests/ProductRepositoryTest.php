<?php

use PHPUnit\Framework\TestCase;
use App\repositories\ProductRepository;

class ProductRepositoryTest extends TestCase
{
    /**
     * Test câu lệnh tìm kiếm sản phẩm trong database theo bộ lọc
     */
    public function testGetActiveProductsQuery()
    {
        $repository = new ProductRepository();
        
        // Giả lập bộ lọc tìm kiếm
        $filters = ['search' => 'Laptop', 'status' => 'active'];
        $products = $repository->getActiveProducts($filters);
        
        // Khẳng định dữ liệu trả về từ câu lệnh SQL phải là một mảng danh sách
        $this->assertIsArray($products);
    }
}
