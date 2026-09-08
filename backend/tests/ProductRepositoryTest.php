<?php

declare(strict_types=1);

use App\Core\BaseRepository;
use App\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

final class ProductRepositoryTest extends TestCase
{
    public function testFindAllActiveBuildsSearchQueryAndReturnsRows(): void
    {
        $expected = [['ID' => 10, 'Name' => 'Laptop A']];
        $statement = $this->createMock(\PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with([
            'search_name' => '%Laptop%',
            'search_desc' => '%Laptop%',
            'search_seller' => '%Laptop%',
        ])->willReturn(true);
        $statement->expects(self::once())->method('fetchAll')->willReturn($expected);

        $pdo = $this->createMock(\PDO::class);
        $pdo->expects(self::once())->method('prepare')->with(self::callback(function (string $sql): bool {
            self::assertStringContainsString("p.Status IN ('active', 'available')", $sql);
            self::assertStringContainsString('p.Name LIKE :search_name', $sql);
            self::assertStringContainsString('ORDER BY p.created_at DESC', $sql);
            return true;
        }))->willReturn($statement);

        $reflection = new \ReflectionClass(ProductRepository::class);
        /** @var ProductRepository $repository */
        $repository = $reflection->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(BaseRepository::class, 'db');
        $property->setValue($repository, $pdo);

        self::assertSame($expected, $repository->findAllActive(['search' => 'Laptop']));
    }
}
