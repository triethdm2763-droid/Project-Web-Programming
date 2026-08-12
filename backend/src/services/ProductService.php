<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Core\Session;
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
        Session::start();
        if (empty($_SESSION['user_id'])) {
            return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập để đăng tin.'];
        }
        $sellerId = intval($_SESSION['user_id']);

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
            'seller_id'        => $sellerId,
            'price'            => $price,
            'status'           => 'pending',
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? ''),
            'stock_quantity'   => isset($data['stock_quantity']) ? intval($data['stock_quantity']) : 1
        ];

        return ['status' => 'success', 'code' => 201, 'product_id' => $this->productRepository->create($insertData)];
    }

    public function updateProduct(int $id, array $data): array
    {
        Session::start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];

        $product = $this->productRepository->findById($id);
        if (!$product) return ['status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.'];

        $isOwner = (int)$product['Seller_ID'] === (int)$_SESSION['user_id'];
        $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
        if (!$isOwner && !$isAdmin) return ['status' => 'error', 'code' => 403, 'message' => 'Bạn không có quyền chỉnh sửa.'];
        if (($product['Status'] ?? '') === 'sold') return ['status' => 'error', 'code' => 400, 'message' => 'Sản phẩm đã bán.'];

        $rules = ['name' => 'required|min:3|max:255', 'price' => 'required', 'category_id' => 'required'];
        if (!empty(Validator::validate($data, $rules))) return ['status' => 'error', 'code' => 400, 'message' => 'Dữ liệu không hợp lệ.'];

        $status = $product['Status'];
        if ($isOwner && !$isAdmin) {
            $status = 'pending';
        }

        $updateData = [
            'name'             => trim($data['name']),
            'description'      => $data['description'] ?? '',
            'category_id'      => intval($data['category_id']),
            'price'            => floatval($data['price']),
            'condition_status' => trim($data['condition_status'] ?? ''),
            'accessories'      => trim($data['accessories'] ?? ''),
            'warranty'         => trim($data['warranty'] ?? 'Không bảo hành'),
            'used_duration'    => trim($data['used_duration'] ?? ''),
            'stock_quantity'   => isset($data['stock_quantity']) ? intval($data['stock_quantity']) : 1,
            'status'           => $status
        ];
        if (!empty($data['image'])) $updateData['image'] = $data['image'];

        $this->productRepository->update($id, $updateData);
        return ['status' => 'success', 'code' => 200, 'message' => 'Cập nhật tin đăng thành công!'];
    }

    public function deleteProduct(int $id): array
    {
        Session::start();
        if (empty($_SESSION['user_id'])) return ['status' => 'error', 'code' => 401, 'message' => 'Bạn phải đăng nhập.'];

        $product = $this->productRepository->findById($id);
        if (!$product) return ['status' => 'error', 'code' => 404, 'message' => 'Không tìm thấy sản phẩm.'];

        $isOwner = (int)$product['Seller_ID'] === (int)$_SESSION['user_id'];
        $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
        if (!$isOwner && !$isAdmin) return ['status' => 'error', 'code' => 403, 'message' => 'Bạn không có quyền xóa.'];
        if (($product['Status'] ?? '') === 'sold') return ['status' => 'error', 'code' => 400, 'message' => 'Sản phẩm đã bán, không thể xóa.'];

        $this->productRepository->softDelete($id);
        return ['status' => 'success', 'code' => 200, 'message' => 'Xóa tin đăng thành công!'];
    }

    public function getSellerProducts(?string $status = null): array
    {
        Session::start();
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
        Session::start();
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

    public function validateImage(array $file): bool
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024;

        $size = (int)($file['size'] ?? 0);
        $type = strtolower((string)($file['type'] ?? ''));
        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));

        if ($size <= 0 || $size > $maxSize) {
            return false;
        }

        if ($type !== '' && !in_array($type, $allowedTypes, true)) {
            return false;
        }

        return $extension === '' || in_array($extension, $allowedExtensions, true);
    }
}
