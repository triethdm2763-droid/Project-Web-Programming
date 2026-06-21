<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    private $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
    }

    public function listCategories(): array
    {
        $rows = $this->categoryRepository->findAll();
        return [
            'status' => 'success',
            'code' => 200,
            'data' => $rows
        ];
    }

    public function getCategory(int $id): array
    {
        $cat = $this->categoryRepository->findById($id);
        if ($cat === null) {
            return [
                'status' => 'error',
                'code' => 404,
                'message' => 'Category not found'
            ];
        }
        return [
            'status' => 'success',
            'code' => 200,
            'data' => $cat
        ];
    }
}
