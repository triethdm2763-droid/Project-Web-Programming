<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Core\Session;

class AdminController
{
    private UserRepository $userRepo;
    private OrderRepository $orderRepo;
    private ProductRepository $productRepo;

    /**
     * 1. Dependency Injection: Cho phép truyền Repository giả (Mock) khi Test,
     * nếu không truyền (khi chạy Web thật) sẽ tự tạo Repository mặc định.
     */
    public function __construct(
        ?UserRepository $userRepo = null,
        ?OrderRepository $orderRepo = null,
        ?ProductRepository $productRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->orderRepo = $orderRepo ?? new OrderRepository();
        $this->productRepo = $productRepo ?? new ProductRepository();
    }

    /**
     * 2. Phân quyền Admin: Trả về Response Array thay vì dùng exit;
     */
    public function checkAdminAuth(): ?array
    {
        Session::start();

        if (empty($_SESSION['user_id'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Bạn cần đăng nhập để truy cập trang quản trị.'], 401);
        }

        if (($_SESSION['role'] ?? '') !== 'admin') {
            return $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập chức năng quản trị này.'], 403);
        }

        return null; // Phân quyền hợp lệ
    }

    /**
     * Helper hỗ trợ trả về Response vừa gõ echo (cho Web) vừa return array (cho Unit Test)
     */
    private function jsonResponse(array $data, int $statusCode = 200): array
    {
        http_response_code($statusCode);
        return [
            'status_code' => $statusCode,
            'body' => $data
        ];
    }

    public function users(): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        return $this->jsonResponse($this->userRepo->findAll());
    }

    public function updateUserStatus(?array $inputData = null): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        // Cho phép truyền $inputData trực tiếp từ bài Test, hoặc lấy từ php://input nếu chạy Web
        $data = $inputData ?? json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$id || !$status) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu tham số id hoặc status.'], 400);
        }

        if (!in_array($status, ['active', 'banned'], true)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 400);
        }

        // Prevent an admin from accidentally locking their own account
        if ((int)$id === (int)($_SESSION['user_id'] ?? 0) && $status === 'banned') {
            return $this->jsonResponse(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.'], 400);
        }

        $success = $this->userRepo->updateStatus((int)$id, $status);

        return $this->jsonResponse(['success' => (bool)$success]);
    }

    public function orders(): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        return $this->jsonResponse($this->orderRepo->findAll());
    }

    public function wallets(): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        return $this->jsonResponse($this->userRepo->getWallets());
    }

    public function reports(): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        $data = [
            'products' => count($this->productRepo->findAllActive() ?? []),
            'users' => count($this->userRepo->findAll() ?? []),
            'orders' => count($this->orderRepo->findAll() ?? [])
        ];

        return $this->jsonResponse($data);
    }

    public function products(?array $queryParams = null): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        $params = $queryParams ?? $_GET;
        $filters = [];
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        if (!empty($params['status'])) {
            $filters['status'] = $params['status'];
        }

        return $this->jsonResponse($this->productRepo->findAllForAdmin($filters));
    }

    public function updateProductStatus(?array $inputData = null): array
    {
        if ($authError = $this->checkAdminAuth()) {
            return $authError;
        }

        $data = $inputData ?? json_decode(file_get_contents('php://input'), true) ?? [];

        $id = $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$id || !$status) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu tham số id hoặc status.'], 400);
        }

        $success = $this->productRepo->updateStatus((int)$id, $status);

        return $this->jsonResponse(['success' => (bool)$success]);
    }
}
