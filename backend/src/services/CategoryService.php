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
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Tên danh mục không được để trống");
        }
        $id = $this->categoryRepository->create($data);
        return array_merge(['id' => $id], $data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        $this->getCategory($id); // Throw exception nếu không tồn tại
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        $this->getCategory($id);
        return $this->categoryRepository->delete($id);
    }
}