<?php
namespace App\Services;

use App\Repositories\ProductRepository;
use App\Validators\Validator;

class ProductService {
    private $productRepository;

    public function __construct() {
        $this->productRepository = new ProductRepository();
    }

    /**
     * Get list of active products with optional filters
     * 
     * @param array $filters
     * @return array
     */
    public function getActiveProducts(array $filters = []): array {
        $products = $this->productRepository->findAllActive($filters);
        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $products
        ];
    }

    /**
     * Get detail of a specific product
     * 
     * @param int $id
     * @return array
     */
    public function getProductDetail(int $id): array {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy sản phẩm hoặc sản phẩm đã bị xóa.'
            ];
        }
        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $product
        ];
    }

    /**
     * Register / create a new product listing
     * 
     * @param array $data
     * @return array
     */
    public function createProduct(array $data): array {
        // Enforce user session context
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập mới có thể đăng bán sản phẩm.'
            ];
        }

        // Validate Input Fields
        $rules = [
            'name'        => 'required|min:3|max:255',
            'price'       => 'required',
            'category_id' => 'required'
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => $errors
            ];
        }

        // Additional validations
        $price = floatval($data['price']);
        if ($price <= 0) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => ['price' => ['Giá bán phải lớn hơn 0.']]
            ];
        }

        // Prepare product insert data
        $insertData = [
            'name'           => trim($data['name']),
            'description'    => $data['description'] ?? '',
            'image'          => $data['image'] ?? '',
            'category_id'    => intval($data['category_id']),
            'seller_id'      => intval($_SESSION['user_id']),
            'price'          => $price,
            'stock_quantity' => 1, // Marketplace default is unique C2C product
            'status'         => 'active' // By default active, or pending admin approval if required
        ];

        $productId = $this->productRepository->create($insertData);

        return [
            'status'     => 'success',
            'code'       => 201,
            'product_id' => $productId
        ];
    }

    /**
     * Update product details
     * 
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateProduct(int $id, array $data): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập để thực hiện tác vụ này.'
            ];
        }

        $product = $this->productRepository->findById($id);
        if ($product === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy sản phẩm hoặc sản phẩm đã bị xóa.'
            ];
        }

        // Check ownership
        if (intval($product['Seller_ID']) !== intval($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Bạn không có quyền chỉnh sửa sản phẩm này.'
            ];
        }

        // Validate Input Fields
        $rules = [
            'name'        => 'required|min:3|max:255',
            'price'       => 'required',
            'category_id' => 'required'
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => $errors
            ];
        }

        $price = floatval($data['price']);
        if ($price <= 0) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => ['price' => ['Giá bán phải lớn hơn 0.']]
            ];
        }

        $updateData = [
            'name'        => trim($data['name']),
            'description' => $data['description'] ?? '',
            'image'       => $data['image'] ?? $product['Image'], // keep old image if not provided
            'category_id' => intval($data['category_id']),
            'price'       => $price
        ];

        $success = $this->productRepository->update($id, $updateData);

        if ($success) {
            return [
                'status' => 'success',
                'code'   => 200,
                'message' => 'Cập nhật sản phẩm thành công.'
            ];
        }

        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => 'Không thể cập nhật sản phẩm.'
        ];
    }

    /**
     * Delete product (soft-delete)
     * 
     * @param int $id
     * @return array
     */
    public function deleteProduct(int $id): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập để thực hiện tác vụ này.'
            ];
        }

        $product = $this->productRepository->findById($id);
        if ($product === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy sản phẩm.'
            ];
        }

        // Check ownership
        if (intval($product['Seller_ID']) !== intval($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Bạn không có quyền xóa sản phẩm này.'
            ];
        }

        $success = $this->productRepository->delete($id);

        if ($success) {
            return [
                'status' => 'success',
                'code'   => 200,
                'message' => 'Xóa sản phẩm thành công.'
            ];
        }

        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => 'Không thể xóa sản phẩm.'
        ];
    }

    /**
     * Get products listed by the currently logged in seller
     * 
     * @param string|null $status
     * @return array
     */
    public function getSellerProducts(string $status = null): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Chưa đăng nhập.'
            ];
        }

        $sellerId = (int)$_SESSION['user_id'];
        $products = $this->productRepository->findBySeller($sellerId, $status);

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $products
        ];
    }

    /**
     * Get statistics for the currently logged in seller
     * 
     * @return array
     */
    public function getSellerStats(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Chưa đăng nhập.'
            ];
        }

        $sellerId = (int)$_SESSION['user_id'];

        // Get orders stats
        $orderRepo = new \App\Repositories\OrderRepository();
        $orders = $orderRepo->findBySeller($sellerId);

        $totalRevenue = 0;
        $deliveredOrders = 0;
        foreach ($orders as $o) {
            if (strtolower($o['Status']) === 'completed') {
                $totalRevenue += floatval($o['Total_price']);
                $deliveredOrders++;
            }
        }

        // Get count of products by status
        $allProducts = $this->productRepository->findBySeller($sellerId);
        $availableCount = 0;
        $pendingCount = 0;
        $soldCount = 0;

        foreach ($allProducts as $p) {
            $status = strtolower($p['Status']);
            if ($status === 'active' || $status === 'available') {
                $availableCount++;
            } elseif ($status === 'pending') {
                $pendingCount++;
            } elseif ($status === 'sold') {
                $soldCount++;
            }
        }

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => [
                'total_revenue'    => $totalRevenue,
                'delivered_orders' => $deliveredOrders,
                'available_count'  => $availableCount,
                'pending_count'    => $pendingCount,
                'sold_count'       => $soldCount
            ]
        ];
    }
}

