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

    public function test_create_category_trims_name_and_returns_created_data()
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'name' => 'Điện thoại',
                'icon' => 'phone.svg'
            ])
            ->willReturn(15);

        $result = $this->categoryService->createCategory([
            'name' => '  Điện thoại  ',
            'icon' => 'phone.svg'
        ]);

        $this->assertSame([
            'id' => 15,
            'name' => 'Điện thoại',
            'icon' => 'phone.svg'
        ], $result);
    }

    public function test_create_category_accepts_name_at_100_character_boundary()
    {
        $name = str_repeat('a', 100);

        $this->repositoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['name' => $name])
            ->willReturn(16);

        $this->assertSame(
            ['id' => 16, 'name' => $name],
            $this->categoryService->createCategory(['name' => $name])
        );
    }

    public function test_create_category_rejects_name_above_100_characters()
    {
        $this->repositoryMock
            ->expects($this->never())
            ->method('create');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Tên danh mục không được vượt quá 100 ký tự'
        );

        $this->categoryService->createCategory([
            'name' => str_repeat('a', 101)
        ]);
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
            ->with(1, ['name' => 'Mới'])
            ->willReturn(true);

        $result =
            $this->categoryService
                ->updateCategory(
                    1,
                    ['name' => 'Mới']
                );

        $this->assertTrue($result);
    }

    public function test_update_category_without_name_skips_name_validation()
    {
        $data = ['icon' => 'laptop.svg'];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(['ID' => 1, 'Name' => 'Laptop']);

        $this->repositoryMock
            ->expects($this->once())
            ->method('update')
            ->with(1, $data)
            ->willReturn(false);

        $this->assertFalse(
            $this->categoryService->updateCategory(1, $data)
        );
    }

    public function test_update_category_rejects_invalid_name_before_update()
    {
        $this->repositoryMock
            ->method('findById')
            ->willReturn(['ID' => 1, 'Name' => 'Laptop']);

        $this->repositoryMock
            ->expects($this->never())
            ->method('update');

        $this->expectException(InvalidArgumentException::class);

        $this->categoryService->updateCategory(1, ['name' => '   ']);
    }

    public function test_constructor_can_create_default_repository()
    {
        $databaseReflection = new ReflectionClass(Database::class);
        $database = $databaseReflection->newInstanceWithoutConstructor();
        $connection = $this->createMock(PDO::class);

        $connectionProperty = $databaseReflection->getProperty('conn');
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue($database, $connection);

        $instanceProperty = $databaseReflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $previousInstance = $instanceProperty->getValue();
        $instanceProperty->setValue(null, $database);

        try {
            $service = new CategoryService();
            $serviceReflection = new ReflectionClass($service);
            $repositoryProperty = $serviceReflection->getProperty(
                'categoryRepository'
            );
            $repositoryProperty->setAccessible(true);

            $this->assertInstanceOf(
                CategoryRepository::class,
                $repositoryProperty->getValue($service)
            );
        } finally {
            $instanceProperty->setValue(null, $previousInstance);
        }
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
