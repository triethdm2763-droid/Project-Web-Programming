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
}
