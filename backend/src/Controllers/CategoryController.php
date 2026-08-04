<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CategoryService;

class CategoryController extends BaseController {
    private $service;

    public function __construct(CategoryService $service = null) {
        $this->service = $service ?? new CategoryService();
    }

    // GET /api/categories
    public function index() {
        $result = $this->service->getAllCategories();
        return $this->json(['success' => true, 'data' => $result]);
    }

    // Hàm kiểm tra quyền Admin (Trả về boolean, KHÔNG dùng exit)
    public function list() {
        return $this->json($this->service->getAllCategories());
    }

    public function detail() {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            return $this->json(['error' => 'Thiếu ID danh mục.'], 400);
        }

        try {
            return $this->json($this->service->getCategory($id));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    private function isAdmin(): bool {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
    }

    // POST /api/categories
    public function store($data = []) {
        if (!$this->isAdmin()) {
            http_response_code(403);
            return $this->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này'], 403);
        }

        $payload = empty($data) ? $_POST : $data;
        $result = $this->service->createCategory($payload);
        http_response_code(201);
        return $this->json(['success' => true, 'data' => $result], 201);
    }

    // PUT /api/categories/{id}
    public function update($id, $data = []) {
        if (!$this->isAdmin()) {
            http_response_code(403);
            return $this->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này'], 403);
        }

        $result = $this->service->updateCategory($id, $data);
        return $this->json(['success' => $result]);
    }

    // DELETE /api/categories/{id}
    public function destroy($id) {
        if (!$this->isAdmin()) {
            http_response_code(403);
            return $this->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này'], 403);
        }

        $result = $this->service->deleteCategory($id);
        return $this->json(['success' => $result]);
    }
}
