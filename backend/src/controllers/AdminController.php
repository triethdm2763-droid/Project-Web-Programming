<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class AdminController
{
    public function users()
    {
        $repo = new UserRepository();
        echo json_encode($repo->findAll());
    }

    public function orders()
    {
        $repo = new OrderRepository();
        echo json_encode($repo->findAll());
    }

    public function wallets()
    {
        $repo = new UserRepository();
        echo json_encode($repo->getWallets());
    }

    public function reports()
    {
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
        $repo = new ProductRepository();
        echo json_encode($repo->findAllForAdmin());
    }

    public function updateProductStatus()
    {
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
