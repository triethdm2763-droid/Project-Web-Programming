<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Validators\Validator;

class ProductService
{
    private const MIN_PRICE = 0.01;
    private const MAX_PRICE = 9999999999999.99;

    private ProductRepository $productRepository;

    public function __construct(?ProductRepository $productRepository = null)
    {
        $this->productRepository = $productRepository ?? new ProductRepository();
    }

    public function getActiveProducts(array $filters = []): array
    {
        $data = $this->productRepository->findAllActive($filters);
        $result = ['status' => 'success', 'code' => 200, 'data' => $data];
        if (isset($filters['limit'])) {
            $result['total'] = $this->productRepository->countAllActive($filters);
        }
        return $result;
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
        if (empty($_SESSION['user_id'])) {
            return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập để đăng tin.'];
        }
        $sellerId = intval($_SESSION['user_id']);

        $errors = $this->validateProductData($data);
        if (!empty($errors)) return ['status' => 'error', 'code' => 400, 'errors' => $errors];

        $price = floatval($data['price']);
        $categoryId = intval($data['category_id']);

        if (!$this->productRepository->categoryExists($categoryId)) {
            return ['status' => 'error', 'code' => 400, 'errors' => ['category_id' => ['Danh mục không tồn tại.']]];
        }

        $insertData = [
            'name'             => trim($data['name']),
            'description'      => $data['description'] ?? '',
            'image'            => $data['image'] ?? '',
            'category_id'      => $categoryId,
            'seller_id'        => $sellerId,
            'price'            => $price,
            'status'           => 'pending',
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? ''),
            'stock_quantity'   => isset($data['stock_quantity']) ? intval($data['stock_quantity']) : 1
        ];

        $productId = $this->productRepository->create($insertData);
        if ($productId <= 0) {
            return ['status' => 'error', 'code' => 500, 'message' => 'Không thể tạo tin đăng.'];
        }

        return ['status' => 'success', 'code' => 201, 'product_id' => $productId];
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

        $errors = $this->validateProductData($data);
        if (!empty($errors)) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors];
        }

        $categoryId = intval($data['category_id']);
        if (!$this->productRepository->categoryExists($categoryId)) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => ['category_id' => ['Danh mục không tồn tại.']],
            ];
        }

        $status = $product['Status'];
        if ($isOwner && !$isAdmin) {
            $status = 'pending';
        }

        $updateData = [
            'name'             => trim($data['name']),
            'description'      => $data['description'] ?? '',
            'category_id'      => $categoryId,
            'price'            => floatval($data['price']),
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? ''),
            'stock_quantity'   => isset($data['stock_quantity']) ? intval($data['stock_quantity']) : 1,
            'status'           => $status
        ];
        if (!empty($data['image'])) $updateData['image'] = $data['image'];

        if (!$this->productRepository->update($id, $updateData)) {
            return ['status' => 'error', 'code' => 500, 'message' => 'Không thể cập nhật tin đăng.'];
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Cập nhật tin đăng thành công!'];
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

        if (!$this->productRepository->softDelete($id)) {
            return ['status' => 'error', 'code' => 500, 'message' => 'Không thể xóa tin đăng.'];
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Xóa tin đăng thành công!'];
    }

    public function getSellerProducts(?string $status = null): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];
        $sellerId = (int)$_SESSION['user_id'];
        $products = $this->productRepository->findSellerProducts($sellerId, $status);
        return ['status' => 'success', 'code' => 200, 'data' => $products];
    }

    public function getMyProducts(?string $status = null): array
    {
        return $this->getSellerProducts($status);
    }

    public function getSellerStats(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];
        
        $sellerId = (int)$_SESSION['user_id'];
        $products = $this->productRepository->findSellerProducts($sellerId);
        $totalProducts = count($products);
        
        $soldProducts = count(array_filter($products, function($p) {
            return ($p['Status'] ?? $p['status'] ?? '') === 'sold';
        }));
        
        $stats = $this->productRepository->getSellerStats($sellerId);
        $stats['total_products'] = $totalProducts;
        $stats['sold_products'] = $soldProducts;
        
        return ['status' => 'success', 'code' => 200, 'data' => $stats];
    }

    private function validateProductData(array $data): array
    {
        $rules = ['name' => 'required|min:3|max:255', 'price' => 'required', 'category_id' => 'required'];
        $errors = Validator::validate($data, $rules);

        if (isset($data['price']) && trim((string)$data['price']) !== '') {
            if (!is_numeric($data['price'])) {
                $errors['price'][] = 'Giá bán phải là số.';
            } else {
                $price = (float)$data['price'];
                if ($price < self::MIN_PRICE || $price > self::MAX_PRICE) {
                    $errors['price'][] = 'Giá bán phải từ 0.01 đến 9999999999999.99.';
                }
            }
        }

        if (isset($data['category_id']) && trim((string)$data['category_id']) !== '') {
            $categoryId = filter_var($data['category_id'], FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId < 1) {
                $errors['category_id'][] = 'Danh mục phải là số nguyên dương.';
            }
        }

        if (array_key_exists('stock_quantity', $data)) {
            $stock = filter_var($data['stock_quantity'], FILTER_VALIDATE_INT);
            if ($stock === false || $stock < 1) {
                $errors['stock_quantity'][] = 'Số lượng phải là số nguyên dương.';
            }
        }

        return $errors;
    }
}
