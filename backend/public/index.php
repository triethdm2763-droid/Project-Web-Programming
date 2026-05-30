<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once BASE_PATH . '/src/config/Database.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'POST /api/auth/register' => ['controllers\AuthController', 'register'],
    'POST /api/auth/login'    => ['controllers\AuthController', 'login'],
    'POST /api/auth/logout'   => ['controllers\AuthController', 'logout'],
    'GET /api/auth/me'        => ['controllers\AuthController', 'me'],
];

$key = "$method $uri";

if (array_key_exists($key, $routes)) {
    [$controllerClass, $action] = $routes[$key];
    $controller = new ("\\$controllerClass")();
    $controller->$action();
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Route not found']);
}
