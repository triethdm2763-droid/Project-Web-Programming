<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\OrderService;

class OrderController extends BaseController {
    private $orderService;

    public function __construct() {
        $this->orderService = new OrderService();
    }

    /**
     * POST /api/orders
     * Checkout a product
     */
    public function create() {
        $data = $this->getRequestBody();
        $result = $this->orderService->checkout($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message'  => 'Đặt hàng thành công!',
                'order_id' => $result['order_id']
            ], 201);
        }

        return $this->json([
            'error'  => $result['message'] ?? 'Đặt hàng thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }

    /**
     * POST /api/orders/cancel
     * Cancel an order
     */
    public function cancel() {
        $data = $this->getRequestBody();
        $result = $this->orderService->cancelOrder($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => 'Hủy đơn hàng thành công!'
            ], 200);
        }

        return $this->json([
            'error' => $result['message'] ?? 'Hủy đơn hàng thất bại.'
        ], $result['code']);
    }

    /**
     * GET /api/orders/buyer
     * Get purchase history of the current user
     */
    public function buyerOrders() {
        $result = $this->orderService->getBuyerHistory();
        if ($result['status'] === 'success') {
            return $this->json($result['data'], 200);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }

    /**
     * GET /api/orders/seller
     * Get sales orders received by the current user
     */
    public function sellerOrders() {
        $result = $this->orderService->getSellerOrders();
        if ($result['status'] === 'success') {
            return $this->json($result['data'], 200);
        }
        return $this->json(['error' => $result['message']], $result['code']);
    }
}
