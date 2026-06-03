<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\ProductService;

class ProductController extends BaseController {
    private $productService;

    public function __construct() {
        $this->productService = new ProductService();
    }

    /**
     * GET /api/products
     * Return list of active products with optional category / search filtering
     */
    public function list() {
        $filters = [];
        if (isset($_GET['category_id'])) {
            $filters['category_id'] = $_GET['category_id'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $result = $this->productService->getActiveProducts($filters);
        return $this->json($result['data'], $result['code']);
    }

    /**
     * GET /api/products/detail
     * Return single product details by id parameter
     */
    public function detail() {
        if (!isset($_GET['id'])) {
            return $this->json(['error' => 'Thiếu tham số ID sản phẩm.'], 400);
        }

        $id = intval($_GET['id']);
        $result = $this->productService->getProductDetail($id);

        if ($result['status'] === 'success') {
            return $this->json($result['data'], $result['code']);
        }

        return $this->json(['error' => $result['message']], $result['code']);
    }

    /**
     * POST /api/products
     * Register a new product listing (requires authenticated seller session)
     */
    public function create() {
        $data = $this->getRequestBody();
        $result = $this->productService->createProduct($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => 'Đăng bán sản phẩm thành công!',
                'product_id' => $result['product_id']
            ], 201);
        }

        return $this->json([
            'error' => $result['message'] ?? 'Đăng bán thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }
}
