<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CategoryService;

class CategoryController extends BaseController {
    private $service;

    public function __construct() {
        $this->service = new CategoryService();
    }

    // GET /api/categories
    public function list() {
        $result = $this->service->listCategories();
        return $this->json($result['data'], $result['code']);
    }

    // GET /api/categories/detail?id=1
    public function detail() {
        if (!isset($_GET['id'])) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }
        $id = intval($_GET['id']);
        $result = $this->service->getCategory($id);
        if ($result['status'] === 'success') {
            return $this->json($result['data'], $result['code']);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }
}
