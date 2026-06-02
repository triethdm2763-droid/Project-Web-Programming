<?php
namespace App\Middlewares;

class AuthMiddleware {
    
    /**
     * Handle the incoming request. If unauthorized, returns 401 JSON and halts execution.
     * 
     * @return array|null Returns array of user details if logged in, or halts request if unauthorized.
     */
    public static function handle() {
        // Double check session start status
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user session key is active
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Bạn cần đăng nhập để thực hiện chức năng này.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Return user session details if logged in
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'role'     => $_SESSION['role'] ?? 'user'
        ];
    }
}
