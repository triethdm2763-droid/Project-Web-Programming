<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\CategoryRepository;

class CategoryRepositoryTest extends TestCase
{
    private $pdoMock;
    private $statementMock;
    private $categoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdoMock = $this->createMock(PDO::class);
        $this->statementMock = $this->createMock(PDOStatement::class);
        
        $this->categoryRepository = new CategoryRepository();
        
        // Dùng Reflection để ghi đè thuộc tính $db từ BaseRepository bằng PDOMock
        $reflection = new ReflectionClass($this->categoryRepository);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->categoryRepository, $this->pdoMock);
    }

    public function test_find_all_returns_array()
    {
        $expectedData = [
            ['ID' => 1, 'Name' => 'Thời trang'],
            ['ID' => 2, 'Name' => 'Điện tử']
        ];

        $this->statementMock->method('execute')->willReturn(true);
        $this->statementMock->method('fetchAll')->willReturn($expectedData);
        $this->pdoMock->method('prepare')->willReturn($this->statementMock);

        $result = $this->categoryRepository->findAll();

        $this->assertCount(2, $result);
        $this->assertEquals('Thời trang', $result[0]['Name']);
    }

    public function test_create_category_returns_last_insert_id()
    {
        $this->statementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->statementMock);
        $this->pdoMock->method('lastInsertId')->willReturn('5');

        $insertedId = $this->categoryRepository->create(['name' => 'Sách', 'icon' => 'book.png']);

        $this->assertEquals(5, $insertedId);
    }

    public function test_update_returns_true()
    {
        $this->statementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->statementMock);

        $result = $this->categoryRepository->update(1, ['name' => 'Mới']);

        $this->assertTrue($result);
    }
}