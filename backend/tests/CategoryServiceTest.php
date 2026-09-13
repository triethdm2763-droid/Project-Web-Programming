<?php

use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Services\CategoryService;
use App\Repositories\CategoryRepository;

class CategoryServiceTest extends TestCase
{
    private $repositoryMock;
    private $categoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock =
            $this->createMock(CategoryRepository::class);

        // Truyền trực tiếp Mock Repository qua Dependency Injection
        $this->categoryService =
            new CategoryService($this->repositoryMock);
    }

    public function test_get_all_categories_returns_data()
    {
        $mockData = [
            ['ID' => 1, 'Name' => 'Laptop']
        ];

        $this->repositoryMock
            ->method('findAll')
            ->willReturn($mockData);

        $result =
            $this->categoryService->getAllCategories();

        $this->assertCount(1, $result);
        $this->assertEquals(
            'Laptop',
            $result[0]['Name']
        );
    }

    public function test_get_category_throws_exception_if_not_found()
    {
        $this->repositoryMock
            ->method('findById')
            ->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Danh mục không tồn tại"
        );

        $this->categoryService->getCategory(999);
    }

    public function test_get_category_returns_existing_category()
    {
        $category = ['ID' => 1, 'Name' => 'Laptop'];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($category);

        $this->assertSame(
            $category,
            $this->categoryService->getCategory(1)
        );
    }

    public function test_create_category_throws_invalid_argument_if_name_empty()
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            "Tên danh mục không được để trống"
        );

        $this->categoryService
            ->createCategory(['name' => '']);
    }

    public function test_update_category_calls_repository_successfully()
    {
        // Mock category tồn tại
        $this->repositoryMock
            ->method('findById')
            ->willReturn([
                'ID' => 1,
                'Name' => 'Cũ'
            ]);

        $this->repositoryMock
            ->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result =
            $this->categoryService
                ->updateCategory(
                    1,
                    ['name' => 'Mới']
                );

        $this->assertTrue($result);
    }

    public function test_delete_category_successfully()
    {
        // Category tồn tại
        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn([
                'ID' => 1,
                'Name' => 'Laptop'
            ]);

        // Repository phải được gọi delete
        $this->repositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $result =
            $this->categoryService
                ->deleteCategory(1);

        $this->assertTrue($result);
    }

    public function test_delete_category_throws_exception_if_not_found()
    {
        // Category không tồn tại
        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Nếu không tồn tại thì không được gọi delete
        $this->repositoryMock
            ->expects($this->never())
            ->method('delete');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Danh mục không tồn tại"
        );

        $this->categoryService
            ->deleteCategory(999);
    }
}
