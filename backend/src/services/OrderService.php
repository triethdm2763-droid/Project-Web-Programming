<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Validators\Validator;
use Exception;

class OrderService
{
    private $orderRepository;
    private $productRepository;
    private $userRepository;
    private $notificationService;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->userRepository = new UserRepository();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create order checkout flow
     * 
     * @param array $data
     * @return array
     */
    public function checkout(array $data): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
        if ($quantity < 1) {
            $quantity = 1;
        }
        $buyerId = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if ($buyerId !== null) {
            $buyerExists = $this->userRepository->findById($buyerId);
            if (!$buyerExists) {
                $buyerId = null;
                $_SESSION = [];
                if (isset($_COOKIE['token'])) {
                    setcookie('token', '', time() - 3600, '/');
                }
            }
        }

        // Check if product exists
        $product = $this->productRepository->findById($productId);
        if ($product === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy sản phẩm này.'
            ];
        }

        // Validate availability and stock count
        $stockQty = intval($product['Stock_quantity'] ?? 0);
        if (!in_array(strtolower($product['Status'] ?? ''), ['active', 'available']) || $stockQty < 1) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Sản phẩm này đã được bán hoặc không còn khả dụng để đặt mua.'
            ];
        }

        if ($stockQty < $quantity) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => "Số lượng mua vượt quá số lượng tồn kho (chỉ còn $stockQty sản phẩm)."
            ];
        }

        // Prevent buying own product
        if ($buyerId !== null && intval($product['Seller_ID']) === $buyerId) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Bạn không thể tự mua sản phẩm của chính mình.'
            ];
        }

        $fullname = isset($data['fullname']) ? trim($data['fullname']) : '';
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        $address = isset($data['shipping_address']) ? trim($data['shipping_address']) : '';

        // If guest checkout, prepend their contact info into the shipping address field
        if ($buyerId === null) {
            $shippingAddressText = "Khách vãng lai: {$fullname} (SĐT: {$phone}) - Địa chỉ: {$address}";
        } else {
            $shippingAddressText = $address;
        }

        // Generate a unique order code
        $orderCode = 'DH' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));

        // Map order and payment structure
        $orderData = [
            'order_code'       => $orderCode,
            'buyer_id'         => $buyerId,
            'seller_id'        => (int)$product['Seller_ID'],
            'product_id'       => $productId,
            'quantity'         => $quantity,
            'total_price'      => floatval($product['Price']) * $quantity,
            'shipping_address' => $shippingAddressText,
            'status'           => 'pending'
        ];

        // Tự động lưu/cập nhật thông tin nhận hàng vào hồ sơ cá nhân nếu chưa có hoặc có thay đổi
        if ($buyerId !== null && (!empty($fullname) || !empty($phone) || !empty($address))) {
            $user = $this->userRepository->findById($buyerId);
            if ($user) {
                $profileUpdateData = [
                    'fullname' => !empty($fullname) ? $fullname : $user['Fullname'],
                    'phone'    => !empty($phone) ? $phone : $user['Phone'],
                    'address'  => !empty($address) ? $address : $user['Address']
                ];
                $this->userRepository->updateProfile($buyerId, $profileUpdateData);
            }
        }

        $paymentData = [
            'amount'         => floatval($product['Price']) * $quantity,
            'payment_method' => trim($data['payment_method']),
            'status'         => (trim($data['payment_method']) === 'COD') ? 'pending' : 'success' //COD is pending, Bank transfer is instantly simulated success
        ];

        try {
            // Run transaction check
            $orderId = $this->orderRepository->createWithTransaction($orderData, $paymentData);

            // Send notification to seller
            $buyerName = !empty($_SESSION['username']) ? $_SESSION['username'] : 'Khách vãng lai';
            if ($buyerId === null && !empty($fullname)) {
                $buyerName = "Khách vãng lai ({$fullname})";
            }
            $this->notificationService->send(
                $orderData['seller_id'],
                "Bạn có đơn hàng mới!",
                "Sản phẩm '" . $product['Name'] . "' của bạn đã được '" . $buyerName . "' đặt mua thành công. Vui lòng giao hàng."
            );

            // Send notification to buyer
            if ($buyerId !== null) {
                $this->notificationService->send(
                    $buyerId,
                    "Đặt mua thành công!",
                    "Đơn hàng mua '" . $product['Name'] . "' (SL: " . $quantity . ") đã được tạo thành công với mã #" . $orderId . ". Tổng số tiền: " . number_format($orderData['total_price'], 0, ',', '.') . " đ."
                );
            }

            return [
                'status'     => 'success',
                'code'       => 201,
                'order_id'   => $orderId,
                'order_code' => $orderCode
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
     * Cancel an order flow
     * 
     * @param array $data
     * @return array
     */
    public function cancelOrder(array $data): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập để hủy đơn hàng.'
            ];
        }

        if (empty($data['order_id'])) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Mã đơn hàng không hợp lệ.'
            ];
        }

        $orderId = intval($data['order_id']);
        $buyerId = intval($_SESSION['user_id']);

        // Find order detail
        $order = $this->orderRepository->findById($orderId);
        if ($order === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy đơn hàng này.'
            ];
        }

        // Verify the logged in user is the buyer
        if (intval($order['Buyer_ID']) !== $buyerId) {
            return [
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Bạn không có quyền hủy đơn hàng của người khác.'
            ];
        }

        // Verify order status is pending
        if ($order['Status'] !== 'pending') {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Chỉ có thể hủy đơn hàng đang ở trạng thái Chờ xử lý.'
            ];
        }

        try {
            $quantityToRestore = isset($order['Quantity']) ? intval($order['Quantity']) : 1;
            // Cancel with database transaction
            $this->orderRepository->cancelWithTransaction($orderId, intval($order['Product_ID']), $quantityToRestore);

            // Send notification to seller
            $this->notificationService->send(
                intval($order['Seller_ID']),
                "Đơn hàng bị hủy!",
                "Đơn hàng cho sản phẩm '" . $order['ProductName'] . "' đã bị người mua '" . $_SESSION['username'] . "' hủy bỏ."
            );

            // Send notification to buyer
            $this->notificationService->send(
                $buyerId,
                "Hủy đơn hàng thành công!",
                "Bạn đã hủy đơn hàng cho sản phẩm '" . $order['ProductName'] . "' (Mã đơn #${orderId}) thành công."
            );

            return [
                'status' => 'success',
                'code'   => 200
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve current user's purchase orders
     * 
     * @return array
     */
    public function getBuyerHistory(): array
    {
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
    public function getSellerOrders(): array
    {
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

    /**
     * Update order status by seller
     * 
     * @param array $data
     * @return array
     */
    public function updateStatus(array $data): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Bạn phải đăng nhập để cập nhật đơn hàng.'
            ];
        }

        if (empty($data['order_id']) || empty($data['status'])) {
            return [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Mã đơn hàng và trạng thái là bắt buộc.'
            ];
        }

        $orderId = intval($data['order_id']);
        $newStatus = trim($data['status']);
        $sellerId = intval($_SESSION['user_id']);

        $order = $this->orderRepository->findById($orderId);
        if ($order === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy đơn hàng.'
            ];
        }

        // Verify logged in user is the seller
        if (intval($order['Seller_ID']) !== $sellerId) {
            return [
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Bạn không có quyền cập nhật đơn hàng của người khác.'
            ];
        }

        // Update status in repository
        $success = $this->orderRepository->updateStatus($orderId, $newStatus);

        if ($success) {
            // Send notification to buyer about the status change
            $statusLabel = $newStatus;
            if (strtolower($newStatus) === 'confirmed') $statusLabel = 'Đã xác nhận';
            elseif (strtolower($newStatus) === 'completed') $statusLabel = 'Hoàn thành';
            elseif (strtolower($newStatus) === 'cancelled') $statusLabel = 'Đã hủy';

            if (!empty($order['Buyer_ID'])) {
                $this->notificationService->send(
                    intval($order['Buyer_ID']),
                    "Đơn hàng của bạn " . $statusLabel,
                    "Đơn hàng #" . $orderId . " cho sản phẩm '" . $order['ProductName'] . "' đã được cập nhật trạng thái: " . $statusLabel
                );
            }

            return [
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Cập nhật trạng thái đơn hàng thành công.'
            ];
        }

        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => 'Không thể cập nhật trạng thái đơn hàng.'
        ];
    }

    /**
     * Track an order details by ID or Order Code (public)
     * 
     * @param string $codeOrId
     * @return array
     */
    public function trackOrder(string $codeOrId): array
    {
        $order = $this->orderRepository->findByCode(trim($codeOrId));

        if ($order === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy đơn hàng này.'
            ];
        }

        return [
            'status' => 'success',
            'code'   => 200,
            'data'   => [
                'id' => $order['ID'],
                'order_code' => $order['Order_Code'],
                'product_name' => $order['ProductName'],
                'product_image' => $order['ProductImage'],
                'total_price' => $order['Total_price'],
                'shipping_address' => $order['Shipping_address'],
                'status' => $order['Status'],
                'payment_method' => $order['Payment_method'],
                'payment_status' => $order['PaymentStatus'],
                'created_at' => $order['created_at']
            ]
        ];
    }
}
