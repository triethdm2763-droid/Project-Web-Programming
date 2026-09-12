<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\ProductService;

class ProductController extends BaseController
{
    private $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    /**
     * GET /api/products
     * Return list of active products with optional category / search / price / sort filtering
     */
    public function list()
    {
        // If status filter is passed, it is the seller dashboard listing
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
            $result = $this->productService->getSellerProducts($status);
            if ($result['status'] === 'success') {
                return $this->json($result['data'], 200);
            }
            return $this->json(['error' => $result['message']], $result['code']);
        }

        $filters = [];
        if (isset($_GET['category_id'])) {
            $filters['category_id'] = $_GET['category_id'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (isset($_GET['min_price'])) {
            $filters['min_price'] = $_GET['min_price'];
        }
        if (isset($_GET['max_price'])) {
            $filters['max_price'] = $_GET['max_price'];
        }
        if (isset($_GET['sort'])) {
            $filters['sort'] = $_GET['sort'];
        }
        if (isset($_GET['location'])) {
            $filters['location'] = $_GET['location'];
        }
        if (isset($_GET['condition_status'])) {
            $filters['condition_status'] = $_GET['condition_status'];
        }
        if (isset($_GET['limit'])) {
            $filters['limit'] = intval($_GET['limit']);
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            if ($page < 1) $page = 1;
            $filters['offset'] = ($page - 1) * $filters['limit'];
        }

        $result = $this->productService->getActiveProducts($filters);
        if (isset($filters['limit'])) {
            return $this->json([
                'total' => $result['total'] ?? count($result['data']),
                'data' => $result['data']
            ], $result['code']);
        }
        return $this->json($result['data'], $result['code']);
    }

    /**
     * GET /api/products/mine
     * Return every listing belonging to the logged-in seller, regardless of
     * status, optionally narrowed to a single status (used by the "Kênh
     * người bán" page tabs: Đang bán / Chờ duyệt / Đã bán).
     */
    public function mine() {
        $status = $_GET['status'] ?? null;
        $result = $this->productService->getMyProducts($status);

        if ($result['status'] === 'success') {
            return $this->json($result['data'], $result['code']);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }


    /**
     * GET /api/products/detail
     * Return single product details by id parameter
     */
    public function detail()
    {
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
    public function create()
    {
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

    /**
     * POST /api/products/upload
     * Upload an image for a product
     */
    public function uploadImage()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            return $this->json(['error' => 'Bạn phải đăng nhập để thực hiện chức năng này.'], 401);
        }

        if (empty($_FILES['image'])) {
            return $this->json(['error' => 'Không tìm thấy file ảnh gửi lên.'], 400);
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Lỗi khi upload file: ' . $file['error']], 400);
        }

        // Validate extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return $this->json(['error' => 'Chỉ chấp nhận các định dạng ảnh: ' . implode(', ', $allowedExtensions)], 400);
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return $this->json(['error' => 'Dung lượng ảnh tối đa là 5MB.'], 400);
        }

        // Create target directory if it doesn't exist
        $targetDir = __DIR__ . '/../../uploads/products/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Generate unique filename
        $newFilename = uniqid('prod_', true) . '.' . $ext;
        $targetFile = $targetDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $this->json([
                'status' => 'success',
                'filename' => $newFilename
            ], 200);
        } else {
            return $this->json(['error' => 'Không thể lưu file ảnh lên máy chủ.'], 500);
        }
    }

    /**
     * POST /api/products/update
     * Update an existing product (requires authenticated seller session & ownership)
     */
    public function update()
    {
        $data = $this->getRequestBody();
        if (empty($data['id'])) {
            return $this->json(['error' => 'Thiếu tham số ID sản phẩm.'], 400);
        }

        $id = intval($data['id']);
        $result = $this->productService->updateProduct($id, $data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => $result['message']
            ], 200);
        }

        return $this->json([
            'error' => $result['message'] ?? 'Cập nhật thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }

    /**
     * POST /api/products/delete
     * Delete an existing product (requires authenticated seller session & ownership)
     */
    public function delete()
    {
        $data = $this->getRequestBody();
        if (empty($data['id'])) {
            return $this->json(['error' => 'Thiếu tham số ID sản phẩm.'], 400);
        }

        $id = intval($data['id']);
        $result = $this->productService->deleteProduct($id);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => $result['message']
            ], 200);
        }

        return $this->json([
            'error' => $result['message'] ?? 'Xóa thất bại.'
        ], $result['code']);
    }

    /**
     * GET /api/seller/stats
     * Retrieve statistics for currently logged in seller
     */
    public function sellerStats()
    {
        $result = $this->productService->getSellerStats();
        if ($result['status'] === 'success') {
            return $this->json($result['data'], 200);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }
}
