<?php
// Start session for login state tracking
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $sessionPath = __DIR__ . '/../storage/sessions';
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    session_start();
}

function getAuthorizationHeader(): string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $candidates = [
        $_SERVER['HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['Authorization'] ?? '',
        $headers['Authorization'] ?? '',
        $headers['authorization'] ?? ''
    ];

    foreach ($candidates as $candidate) {
        if (!empty($candidate)) {
            return $candidate;
        }
    }

    return '';
}

// Hydrate session from JWT Authorization header or cookie
$token = null;
$authorizationHeader = getAuthorizationHeader();
if (!empty($authorizationHeader) && preg_match('/Bearer\s+(\S+)/i', $authorizationHeader, $matches)) {
    $token = $matches[1];
}

if (empty($token)) {
    $token = $_COOKIE['token'] ?? null;
}

if (!empty($token)) {
    require_once __DIR__ . '/../src/utils/JWT.php';
    $payload = \App\Utils\JWT::decode($token);
    if ($payload) {
        $_SESSION['user_id']  = $payload['user_id'];
        $_SESSION['username'] = $payload['username'];
        $_SESSION['role']     = $payload['role'];
    } else {
        $_SESSION = [];
    }
}

// 1. SMART PSR-4 AUTOLOADER
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    
    // Split namespace parts
    $parts = explode('\\', $relative_class);
    $className = array_pop($parts); // E.g., AuthController
    
    // Convert directory names to lowercase (e.g., Controllers -> controllers)
    $dirs = array_map('strtolower', $parts);
    
    $path = implode('/', $dirs);
    $file = $base_dir . ($path ? $path . '/' : '') . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Helper response if route not found
function sendJsonError($message, $code = 404) 
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. EXTRACT CLEAN ROUTE PATH
$requestUri = $_SERVER['REQUEST_URI'];
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}

$path = '/';
if (preg_match('/(\/api\/.*)$/', $requestUri, $matches)) {
    $path = rtrim($matches[1], '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle CORS Preflight request
if ($method === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(204);
    exit;
}

// 3. DEFINE STATIC API ROUTES
$routes = [
    'POST' => [
        '/api/auth/register'        => ['App\Controllers\AuthController', 'register'],
        '/api/auth/login'           => ['App\Controllers\AuthController', 'login'],
        '/api/auth/logout'          => ['App\Controllers\AuthController', 'logout'],
        '/api/auth/forgot-password' => ['App\Controllers\AuthController', 'requestReset'],
        '/api/auth/reset-password'  => ['App\Controllers\AuthController', 'performReset'],
        '/api/auth/profile/update'  => ['App\Controllers\AuthController', 'updateProfile'],
        '/api/products'             => ['App\Controllers\ProductController', 'create'],
        '/api/products/upload'      => ['App\Controllers\ProductController', 'uploadImage'],
        '/api/products/update'      => ['App\Controllers\ProductController', 'update'],
        '/api/products/delete'      => ['App\Controllers\ProductController', 'delete'],
        '/api/orders'               => ['App\Controllers\OrderController', 'create'],
        '/api/orders/cancel'        => ['App\Controllers\OrderController', 'cancel'],
        '/api/orders/status'        => ['App\Controllers\OrderController', 'updateOrderStatus'],
        '/api/notifications/read'   => ['App\Controllers\NotificationController', 'markRead'],
        '/api/admin/products/update-status' => ['App\Controllers\AdminController', 'updateProductStatus'],
        '/api/admin/users/update-status'    => ['App\Controllers\AdminController', 'updateUserStatus']
    ],
    'GET' => [
        '/api/auth/me'            => ['App\Controllers\AuthController', 'me'],
        '/api/products'           => ['App\Controllers\ProductController', 'list'],
        '/api/products/detail'    => ['App\Controllers\ProductController', 'detail'],
        '/api/products/mine'      => ['App\Controllers\ProductController', 'mine'],
        // Categories endpoints
        '/api/categories'         => ['App\Controllers\CategoryController', 'list'],
        '/api/categories/detail'  => ['App\Controllers\CategoryController', 'detail'],
        '/api/orders/buyer'       => ['App\Controllers\OrderController', 'buyerOrders'],
        '/api/orders/seller'      => ['App\Controllers\OrderController', 'sellerOrders'],
        '/api/orders/track'       => ['App\Controllers\OrderController', 'track'],
        '/api/seller/stats'       => ['App\Controllers\ProductController', 'sellerStats'],
        '/api/notifications'      => ['App\Controllers\NotificationController', 'list'],
        // Admin dashboard endpoints
        '/api/admin/users'        => ['App\Controllers\AdminController', 'users'],
        '/api/admin/wallets'      => ['App\Controllers\AdminController', 'wallets'],
        '/api/admin/reports'      => ['App\Controllers\AdminController', 'reports'],
        '/api/admin/orders'       => ['App\Controllers\AdminController', 'orders'],
        '/api/admin/products'     => ['App\Controllers\AdminController', 'products']
    ]
];

// 4. ROUTE DISPATCHING
if (!isset($routes[$method]) || !isset($routes[$method][$path])) {
    sendJsonError("API endpoint '{$method} {$path}' not found.", 404);
}

list($controllerClass, $action) = $routes[$method][$path];

try {
    if (!class_exists($controllerClass)) {
        sendJsonError("Controller class '{$controllerClass}' not found.", 500);
    }
    
    $controllerInstance = new $controllerClass();
    
    if (!method_exists($controllerInstance, $action)) {
        sendJsonError("Action '{$action}' not found in controller '{$controllerClass}'.", 500);
    }
    
    // Call the action
    $response = $controllerInstance->$action();

    if (is_array($response)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (array_key_exists('status_code', $response) && array_key_exists('body', $response)) {
            http_response_code((int)$response['status_code']);
            echo json_encode($response['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
    
} catch (Exception $e) {
    sendJsonError("Internal Server Error: " . $e->getMessage(), 500);
}
