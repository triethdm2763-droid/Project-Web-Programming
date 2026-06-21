<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class AdminController
{
    /**
     * Guard every Admin endpoint: must be logged in AND have Role = 'admin'.
     * Stops execution with a 401/403 JSON response otherwise.
     */
    private function requireAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để truy cập trang quản trị.']);
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập chức năng quản trị này.']);
            exit;
        }
    }

    public function users()
    {
        $this->requireAdmin();
        $repo = new UserRepository();
        echo json_encode($repo->findAll());
    }

    public function updateUserStatus()
    {
        $this->requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$id || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số id hoặc status.']);
            return;
        }

        if (!in_array($status, ['active', 'banned'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.']);
            return;
        }

        // Prevent an admin from accidentally locking their own account
        if ((int)$id === (int)$_SESSION['user_id'] && $status === 'banned') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
            return;
        }

        $repo = new UserRepository();
        $success = $repo->updateStatus((int)$id, $status);

        echo json_encode(['success' => $success]);
    }

    public function orders()
    {
        $this->requireAdmin();
        $repo = new OrderRepository();
        echo json_encode($repo->findAll());
    }

    public function wallets()
    {
        $this->requireAdmin();
        $repo = new UserRepository();
        echo json_encode($repo->getWallets());
    }

    public function reports()
    {
        $this->requireAdmin();
        $productRepo = new ProductRepository();
        $userRepo = new UserRepository();
        $orderRepo = new OrderRepository();

        echo json_encode([
            'products' => count($productRepo->findAllActive()),
            'users' => count($userRepo->findAll()),
            'orders' => count($orderRepo->findAll())
        ]);
    }

    public function products()
    {
        $this->requireAdmin();

        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $repo = new ProductRepository();
        echo json_encode($repo->findAllForAdmin($filters));
    }

    public function updateProductStatus()
    {
        $this->requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);

        $id = $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$id || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số id hoặc status.']);
            return;
        }

        $repo = new ProductRepository();
        $success = $repo->updateStatus((int)$id, $status);

        echo json_encode(['success' => $success]);
    }
}
