<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\CategoryController;
use App\Services\CategoryService;

class CategoryControllerTest extends TestCase
{
    private $serviceMock;
    private $categoryController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceMock = $this->createMock(CategoryService::class);
        $this->categoryController = new CategoryController($this->serviceMock);
    }

    public function test_index_returns_success()
    {
        $this->serviceMock->method('getAllCategories')->willReturn([
            ['ID' => 1, 'Name' => 'Xe']
        ]);

        ob_start();
        $this->categoryController->index();
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertCount(1, $json['data']);
    }

    public function test_FR25_guest_cannot_create_category_returns_403()
    {
        $_SESSION['user'] = null; // Guest

        ob_start();
        $this->categoryController->store(['name' => 'Hacker']);
        $output = ob_get_clean();

        $this->assertEquals(403, http_response_code());
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
        $this->assertEquals("Bạn không có quyền thực hiện thao tác này", $json['message']);
    }

    public function test_FR25_user_cannot_delete_category_returns_403()
    {
        $_SESSION['user'] = ['role' => 'user']; // User thường

        ob_start();
        $this->categoryController->destroy(1);
        $output = ob_get_clean();

        $this->assertEquals(403, http_response_code());
        $json = json_decode($output, true);
        $this->assertFalse($json['success']);
    }

    public function test_admin_can_create_category_successfully()
    {
        $_SESSION['user'] = ['role' => 'admin']; // Administrator

        $this->serviceMock->method('createCategory')
            ->willReturn(['id' => 10, 'name' => 'Đồng hồ']);

        ob_start();
        $this->categoryController->store(['name' => 'Đồng hồ']);
        $output = ob_get_clean();

        $this->assertEquals(201, http_response_code());
        $json = json_decode($output, true);
        $this->assertTrue($json['success']);
        $this->assertEquals('Đồng hồ', $json['data']['name']);
    }
}