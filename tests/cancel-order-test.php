<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../backend/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    $className = array_pop($parts);
    $dirs = array_map('strtolower', $parts);
    $path = implode('/', $dirs);
    $file = $base_dir . ($path ? $path . '/' : '') . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Services\OrderService;
use App\Config\Database as DB;

echo "--- BẮT ĐẦU KIỂM THỬ GIAO DỊCH HỦY ĐƠN HÀNG ---\n";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 6;
$_SESSION['username'] = 'buyer_minh';

$productId = 51; // Sách tiếng Anh (đang có sẵn)
$db = DB::getInstance()->getConnection();

// 1. Tạo đơn hàng giả lập
$orderService = new OrderService();
$testPayload = [
    'product_id' => $productId,
    'shipping_address' => '999 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
    'payment_method' => 'COD',
    'fullname' => 'Nguyễn Quang Minh',
    'phone' => '0901234567',
    'notes' => 'Giao hàng nhanh nhất có thể'
];

echo "[+] Đang đặt hàng để tạo đơn test...\n";
$checkoutRes = $orderService->checkout($testPayload);

if ($checkoutRes['status'] !== 'success') {
    echo "[-] Tạo đơn hàng lỗi: " . $checkoutRes['message'] . "\n";
    exit(1);
}

$orderId = $checkoutRes['order_id'];
echo "[+] Đã tạo đơn hàng test ID = $orderId\n";

// Kiểm tra trạng thái sản phẩm sau khi đặt
$stmt = $db->prepare("SELECT * FROM `products` WHERE `ID` = :id");
$stmt->execute(['id' => $productId]);
$prodPlaced = $stmt->fetch();
echo "[+] Trạng thái sản phẩm sau khi đặt: Status = '" . $prodPlaced['Status'] . "' | Stock = " . $prodPlaced['Stock_quantity'] . "\n";

// 2. Thực hiện hủy đơn hàng vừa tạo
echo "[+] Đang tiến hành hủy đơn hàng ID = $orderId...\n";
$cancelRes = $orderService->cancelOrder(['order_id' => $orderId]);
echo "[+] Kết quả hủy đơn hàng: " . json_encode($cancelRes, JSON_UNESCAPED_UNICODE) . "\n";

if ($cancelRes['status'] === 'success') {
    echo "[PASS] Giao dịch hủy đơn thành công!\n";
    
    // Kiểm tra trạng thái đơn hàng sau hủy
    $stmt = $db->prepare("SELECT * FROM `orders` WHERE `ID` = :id");
    $stmt->execute(['id' => $orderId]);
    $orderAfter = $stmt->fetch();
    echo "[+] Trạng thái đơn hàng sau hủy: Status = '" . $orderAfter['Status'] . "'\n";
    
    // Kiểm tra trạng thái sản phẩm sau hủy
    $stmt = $db->prepare("SELECT * FROM `products` WHERE `ID` = :id");
    $stmt->execute(['id' => $productId]);
    $prodAfter = $stmt->fetch();
    echo "[+] Trạng thái sản phẩm sau hủy: Status = '" . $prodAfter['Status'] . "' | Stock = " . $prodAfter['Stock_quantity'] . "\n";
    
    // 3. Dọn dẹp dữ liệu test
    $db->prepare("DELETE FROM `payments` WHERE `Order_ID` = :order_id")->execute(['order_id' => $orderId]);
    $db->prepare("DELETE FROM `orders` WHERE `ID` = :order_id")->execute(['order_id' => $orderId]);
    $db->prepare("DELETE FROM `notifications` WHERE `User_ID` IN (4, 6) AND `created_at` >= NOW() - INTERVAL 1 MINUTE")->execute();
    echo "[+] Đã dọn dẹp dữ liệu kiểm thử thành công.\n";
} else {
    echo "[FAIL] Hủy đơn hàng thất bại: " . $cancelRes['message'] . "\n";
}

echo "--- KẾT THÚC KIỂM THỬ HỦY ĐƠN HÀNG ---\n";
