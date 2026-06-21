<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Validators\Validator;

class ProductService
{
    private $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function getActiveProducts(array $filters = []): array
    {
        return ['status' => 'success', 'code' => 200, 'data' => $this->productRepository->findAllActive($filters)];
    }

    public function getProductDetail(int $id): array
    {
        $product = $this->productRepository->findById($id);
        return $product ? ['status' => 'success', 'code' => 200, 'data' => $product] 
                        : ['status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.'];
    }

    public function createProduct(array $data): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];

        $rules = ['name' => 'required|min:3|max:255', 'price' => 'required', 'category_id' => 'required'];
        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) return ['status' => 'error', 'code' => 400, 'errors' => $errors];

        $price = floatval($data['price']);
        if ($price <= 0) return ['status' => 'error', 'code' => 400, 'errors' => ['price' => ['Giá bán phải > 0.']]];

        $insertData = [
            'name'             => trim($data['name']),
            'description'      => $data['description'] ?? '',
            'image'            => $data['image'] ?? '',
            'category_id'      => intval($data['category_id']),
            'seller_id'        => intval($_SESSION['user_id']),
            'price'            => $price,
            'status'           => 'active',
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? '')
        ];

        return ['status' => 'success', 'code' => 201, 'product_id' => $this->productRepository->create($insertData)];
    }

    public function updateProduct(int $id, array $data): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];

        $product = $this->productRepository->findById($id);
        if (!$product) return ['status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.'];

        $isOwner = (int)$product['Seller_ID'] === (int)$_SESSION['user_id'];
        $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
        if (!$isOwner && !$isAdmin) return ['status' => 'error', 'code' => 403, 'message' => 'Bạn không có quyền chỉnh sửa.'];
        if (($product['Status'] ?? '') === 'sold') return ['status' => 'error', 'code' => 400, 'message' => 'Sản phẩm đã bán.'];

        $rules = ['name' => 'required|min:3|max:255', 'price' => 'required', 'category_id' => 'required'];
        if (!empty(Validator::validate($data, $rules))) return ['status' => 'error', 'code' => 400, 'message' => 'Dữ liệu không hợp lệ.'];

        $updateData = [
            'name'             => trim($data['name']),
            'description'      => $data['description'] ?? '',
            'category_id'      => intval($data['category_id']),
            'price'            => floatval($data['price']),
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? '')
        ];
        if (!empty($data['image'])) $updateData['image'] = $data['image'];

        $this->productRepository->update($id, $updateData);
        return ['status' => 'success', 'code' => 200];
    }

    public function deleteProduct(int $id): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];

        $product = $this->productRepository->findById($id);
        if (!$product) return ['status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.'];

        $isOwner = (int)$product['Seller_ID'] === (int)$_SESSION['user_id'];
        $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
        if (!$isOwner && !$isAdmin) return ['status' => 'error', 'code' => 403, 'message' => 'Bạn không có quyền xóa.'];
        if (($product['Status'] ?? '') === 'sold') return ['status' => 'error', 'code' => 400, 'message' => 'Sản phẩm đã bán, không thể xóa.'];

        $this->productRepository->softDelete($id);
        return ['status' => 'success', 'code' => 200];
    }
}