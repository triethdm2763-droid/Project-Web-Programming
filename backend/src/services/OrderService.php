<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\NotificationService;
use App\Validators\Validator;
use Exception;

class OrderService {
    private $orderRepository;
    private $productRepository;
    private $notificationService;

    public function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create order checkout flow
     * 
     * @param array $data
     * @return array
     */
    public function checkout(array $data): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập để mua sản phẩm.'
            ];
        }

        $rules = [
            'product_id'       => 'required',
            'shipping_address' => 'required|min:10',
            'payment_method'   => 'required'
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => $errors
            ];
        }

        $productId = intval($data['product_id']);
        $buyerId = intval($_SESSION['user_id']);

        // Check if product exists
        $product = $this->productRepository->findById($productId);
        if ($product === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy sản phẩm này.'
            ];
        }

        // Prevent buying own product
        if (intval($product['Seller_ID']) === $buyerId) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Bạn không thể tự mua sản phẩm của chính mình.'
            ];
        }

        // Map order and payment structure
        $orderData = [
            'buyer_id'         => $buyerId,
            'seller_id'        => (int)$product['Seller_ID'],
            'product_id'       => $productId,
            'total_price'      => floatval($product['Price']),
            'shipping_address' => trim($data['shipping_address']),
            'status'           => 'pending'
        ];

        $paymentData = [
            'amount'         => floatval($product['Price']),
            'payment_method' => trim($data['payment_method']),
            'status'         => (trim($data['payment_method']) === 'COD') ? 'pending' : 'success' //COD is pending, Bank transfer is instantly simulated success
        ];

        try {
            // Run transaction check
            $orderId = $this->orderRepository->createWithTransaction($orderData, $paymentData);

            // Send notification to seller
            $this->notificationService->send(
                $orderData['seller_id'],
                "Bạn có đơn hàng mới!",
                "Sản phẩm '" . $product['Name'] . "' của bạn đã được '" . $_SESSION['username'] . "' đặt mua thành công. Vui lòng giao hàng."
            );

            // Send notification to buyer
            $this->notificationService->send(
                $buyerId,
                "Đặt mua thành công!",
                "Đơn hàng mua '" . $product['Name'] . "' đã được tạo thành công với mã #" . $orderId . ". Tổng số tiền: " . number_format($product['Price'], 0, ',', '.') . " đ."
            );

            return [
                'status'   => 'success',
                'code'     => 201,
                'order_id' => $orderId
            ];

        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Có lỗi xảy ra trong quá trình thanh toán: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve current user's purchase orders
     * 
     * @return array
     */
    public function getBuyerHistory(): array {
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

        $orders = $this->orderRepository->findByBuyer((int)$_SESSION['user_id']);
        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $orders
        ];
    }

    /**
     * Retrieve current user's sales orders
     * 
     * @return array
     */
    public function getSellerOrders(): array {
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

        $orders = $this->orderRepository->findBySeller((int)$_SESSION['user_id']);
        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => $orders
        ];
    }
}
