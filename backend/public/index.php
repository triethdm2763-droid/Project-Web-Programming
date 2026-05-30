<?php
// Start session for login state tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
function sendJsonError($message, $code = 404) {
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
        '/api/auth/register' => ['App\Controllers\AuthController', 'register'],
        '/api/auth/login'    => ['App\Controllers\AuthController', 'login'],
        '/api/auth/logout'   => ['App\Controllers\AuthController', 'logout']
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
    $controllerInstance->$action();
    
} catch (Exception $e) {
    sendJsonError("Internal Server Error: " . $e->getMessage(), 500);
}
