<?php
// Tự động tải các class qua spl_autoload_register tương tự backend/public/index.php
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

echo "--- BẮT ĐẦU KIỂM THỬ GIAO DỊCH CHECKOUT CỦA ORDERSERVICE ---\n";

// 1. Giả lập phiên đăng nhập cho user có ID = 6 (buyer_minh)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 6;
$_SESSION['username'] = 'buyer_minh';

// 2. Lấy thông tin sản phẩm mẫu trước khi mua (ví dụ sản phẩm ID = 51)
$productId = 51;
$db = DB::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM `products` WHERE `ID` = :id");
$stmt->execute(['id' => $productId]);
$productBefore = $stmt->fetch();

if (!$productBefore) {
    echo "[-] Không tìm thấy sản phẩm ID = $productId để test. Hãy đảm bảo bạn đã import dữ liệu mẫu sạch.\n";
    exit(1);
}

echo "[+] Sản phẩm trước khi đặt: '" . $productBefore['Name'] . "' | Trạng thái: " . $productBefore['Status'] . " | Số lượng: " . $productBefore['Stock_quantity'] . "\n";

// 3. Gọi OrderService::checkout
$orderService = new OrderService();
$testPayload = [
    'product_id' => $productId,
    'shipping_address' => '999 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
    'payment_method' => 'COD',
    'fullname' => 'Nguyễn Quang Minh Mới',
    'phone' => '0901234567',
    'notes' => 'Giao hàng nhanh nhất có thể'
];

echo "[+] Đang gọi OrderService::checkout()...\n";
$result = $orderService->checkout($testPayload);

echo "[+] Kết quả API: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";

if ($result['status'] === 'success') {
    echo "[PASS] Đặt hàng thành công! Đơn hàng được tạo với ID = " . $result['order_id'] . "\n";
    
    // Kiểm tra trạng thái sản phẩm sau khi mua
    $stmt = $db->prepare("SELECT * FROM `products` WHERE `ID` = :id");
    $stmt->execute(['id' => $productId]);
    $productAfter = $stmt->fetch();
    echo "[+] Sản phẩm sau khi đặt: Trạng thái = " . $productAfter['Status'] . " | Số lượng = " . $productAfter['Stock_quantity'] . "\n";
    
    // Kiểm tra thông tin người dùng được cập nhật trong profile
    $stmt = $db->prepare("SELECT * FROM `users` WHERE `ID` = 6");
    $stmt->execute();
    $userAfter = $stmt->fetch();
    echo "[+] Hồ sơ người dùng sau cập nhật: Họ tên = '" . $userAfter['Fullname'] . "' | SĐT = '" . $userAfter['Phone'] . "' | Địa chỉ = '" . $userAfter['Address'] . "'\n";
    
    // Reset lại trạng thái sản phẩm để không làm bẩn dữ liệu mẫu của hệ thống
    $db->prepare("UPDATE `products` SET `Status` = 'available', `Stock_quantity` = 1 WHERE `ID` = :id")->execute(['id' => $productId]);
    $db->prepare("DELETE FROM `payments` WHERE `Order_ID` = :order_id")->execute(['order_id' => $result['order_id']]);
    $db->prepare("DELETE FROM `orders` WHERE `ID` = :order_id")->execute(['order_id' => $result['order_id']]);
    $db->prepare("DELETE FROM `notifications` WHERE `User_ID` IN (4, 6) AND `created_at` >= NOW() - INTERVAL 1 MINUTE")->execute();
    
    // Reset lại user profile
    $db->prepare("UPDATE `users` SET `Fullname` = 'Nguyễn Quang Minh', `Phone` = '0901000006', `Address` = '12 Đường Mua Sắm, Tân Bình, TP.HCM' WHERE `ID` = 6")->execute();
    echo "[+] Đã dọn dẹp dữ liệu kiểm thử thành công.\n";
} else {
    echo "[FAIL] Đặt hàng thất bại: " . $result['message'] . "\n";
}
echo "--- KẾT THÚC KIỂM THỬ ---\n";
