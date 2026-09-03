<?php

declare(strict_types=1);

namespace Tests\Admin;

use App\Controllers\AdminController;
use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\CategoryService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdminCategoryEpBvaTest extends TestCase
{
    private function repository(): CategoryRepository
    {
        return new class extends CategoryRepository {
            public array $created = [];
            private int $nextId = 100;

            public function __construct() {}

            public function create(array $data): int
            {
                $this->created[] = $data;
                return $this->nextId++;
            }
        };
    }


    private function adminController(): array
    {
        $userRepo = new class extends UserRepository {
            public int $calls = 0;
            public string $lastStatus = '';
            public function __construct() {}
            public function updateStatus(int $id, string $status): bool
            {
                $this->calls++;
                $this->lastStatus = $status;
                return true;
            }
        };
        $orderRepo = new class extends OrderRepository { public function __construct() {} };
        $productRepo = new class extends ProductRepository { public function __construct() {} };
        return [new AdminController($userRepo, $orderRepo, $productRepo), $userRepo];
    }

    public function test_EP_admin_guest_is_invalid_partition(): void
    {
        $_SESSION = [];
        [$controller] = $this->adminController();
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'active']);
        self::assertSame(401, $response['status_code']);
    }

    public function test_EP_non_admin_role_is_invalid_partition(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'user'];
        [$controller] = $this->adminController();
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'active']);
        self::assertSame(403, $response['status_code']);
    }

    public function test_EP_missing_id_is_invalid_partition(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'admin'];
        [$controller, $repo] = $this->adminController();
        $response = $controller->updateUserStatus(['status' => 'active']);
        self::assertSame(400, $response['status_code']);
        self::assertSame(0, $repo->calls);
    }

    public function test_EP_missing_status_is_invalid_partition(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'admin'];
        [$controller, $repo] = $this->adminController();
        $response = $controller->updateUserStatus(['id' => 7]);
        self::assertSame(400, $response['status_code']);
        self::assertSame(0, $repo->calls);
    }

    public function test_EP_status_outside_enum_is_invalid_partition(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'admin'];
        [$controller, $repo] = $this->adminController();
        $response = $controller->updateUserStatus(['id' => 7, 'status' => 'suspended']);
        self::assertSame(400, $response['status_code']);
        self::assertSame(0, $repo->calls);
    }

    public function test_EP_active_and_banned_are_valid_status_partitions(): void
    {
        foreach (['active', 'banned'] as $status) {
            $_SESSION = ['user_id' => 2, 'role' => 'admin'];
            [$controller, $repo] = $this->adminController();
            $response = $controller->updateUserStatus(['id' => 7, 'status' => $status]);
            self::assertSame(200, $response['status_code']);
            self::assertSame($status, $repo->lastStatus);
        }
    }

    public static function validStandardBoundaryProvider(): array
    {
        return [
            'B1 min=1'       => [1],
            'B2 min+=2'      => [2],
            'B3 nominal=50'  => [50],
            'B4 max-=99'     => [99],
            'B5 max=100'     => [100],
        ];
    }

    #[DataProvider('validStandardBoundaryProvider')]
    public function test_BVA_standard_name_length_is_accepted_for_1_to_100(int $length): void
    {
        $repo = $this->repository();
        $service = new CategoryService($repo);
        $name = str_repeat('A', $length);

        $result = $service->createCategory(['name' => $name]);

        self::assertSame($name, $result['name']);
        self::assertSame($name, $repo->created[0]['name']);
    }

    // X1 = Robust min-1 = 0 characters. This is rejected by source code.
    public function test_EP_BVA_empty_name_is_rejected(): void
    {
        $service = new CategoryService($this->repository());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tên danh mục không được để trống');

        $service->createCategory(['name' => '']);
    }

    // X2 = Robust max+1 = 101 characters, based on categories.Name VARCHAR(100).
    // Expected to FAIL on the current source: CategoryService checks only empty(name).
    public function test_EP_BVA_name_longer_than_schema_max_should_be_rejected(): void
    {
        $service = new CategoryService($this->repository());

        $this->expectException(InvalidArgumentException::class);
        $service->createCategory(['name' => str_repeat('A', 101)]);
    }

    // EP invalid class: missing name key behaves the same as empty string.
    public function test_EP_missing_name_is_rejected(): void
    {
        $service = new CategoryService($this->repository());

        $this->expectException(InvalidArgumentException::class);
        $service->createCategory([]);
    }
}
