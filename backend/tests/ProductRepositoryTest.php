<?php

namespace Tests\Product;

use PHPUnit\Framework\TestCase;
use App\Repositories\ProductRepository;

class ProductRepositoryTest extends TestCase
{
    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ProductRepository();
    }

    /**
     * TC-REPOSITORY-01
     * Lấy danh sách sản phẩm active với bộ lọc search + status
     */
    public function testGetActiveProductsWithFilters(): void
    {
        $filters = [
            'search' => 'Laptop',
            'status' => 'active'
        ];

        $result = $this->repository->getActiveProducts($filters);

        $this->assertIsArray($result);
    }

    /**
     * TC-REPOSITORY-02
     * Lấy danh sách sản phẩm active không có bộ lọc
     */
    public function testGetActiveProductsWithoutFilters(): void
    {
        $result = $this->repository->getActiveProducts([]);

        $this->assertIsArray($result);
    }

    /**
     * TC-REPOSITORY-03
     * Đếm số sản phẩm active
     */
    public function testCountAllActive(): void
    {
        $result = $this->repository->countAllActive([]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * TC-REPOSITORY-04
     * Tìm sản phẩm theo ID không tồn tại
     */
    public function testFindByIdNotFound(): void
    {
        $result = $this->repository->findById(999999999);

        $this->assertNull($result);
    }

    /**
     * TC-REPOSITORY-05
     * Tìm sản phẩm của seller không tồn tại
     */
    public function testFindSellerProductsNotFound(): void
    {
        $result = $this->repository->findSellerProducts(999999999);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * TC-REPOSITORY-06
     * Lấy thống kê seller không tồn tại
     */
    public function testGetSellerStats(): void
    {
        $result = $this->repository->getSellerStats(999999999);

        $this->assertIsArray($result);
    }
}