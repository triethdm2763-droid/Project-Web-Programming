<?php
namespace App\Services;

use App\Repositories\CategoryRepository;
use InvalidArgumentException;
use Exception;

class CategoryService
{
    private $categoryRepository;

    // Hỗ trợ truyền Mock vào từ Unit Test
    public function __construct(CategoryRepository $categoryRepository = null)
    {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getCategory(int $id): array
    {
        $cat = $this->categoryRepository->findById($id);
        if ($cat === null) {
            throw new Exception("Danh mục không tồn tại");
        }
        return $cat;
    }

    public function createCategory(array $data): array
    {
        $data['name'] = $this->validateName($data['name'] ?? null);
        $id = $this->categoryRepository->create($data);
        return array_merge(['id' => $id], $data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        $this->getCategory($id); // Throw exception nếu không tồn tại
        if (array_key_exists('name', $data)) {
            $data['name'] = $this->validateName($data['name']);
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        $this->getCategory($id);
        return $this->categoryRepository->delete($id);
    }

    private function validateName($name): string
    {
        $name = trim((string)$name);
        if ($name === '') {
            throw new InvalidArgumentException("Tên danh mục không được để trống");
        }
        if (mb_strlen($name) > 100) {
            throw new InvalidArgumentException("Tên danh mục không được vượt quá 100 ký tự");
        }
        return $name;
    }
}
