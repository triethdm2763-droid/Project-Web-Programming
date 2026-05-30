<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AuthService;

class AuthController extends BaseController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/register
     * Process new user account creation.
     */
    public function register() {
        $data = $this->getRequestBody();
        $result = $this->authService->register($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => 'Đăng ký tài khoản thành công!',
                'user_id' => $result['user_id']
            ], 201);
        }

        // Return error messages with appropriate HTTP status code
        return $this->json([
            'errors' => $result['errors'] ?? 'Đăng ký thất bại.'
        ], $result['code']);
    }

    /**
     * POST /api/auth/login
     * Process user login and establish active session state.
     */
    public function login() {
        $data = $this->getRequestBody();
        $result = $this->authService->login($data);

        if ($result['status'] === 'success') {
            // Bind authentication parameters into active Session State
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $user = $result['user'];
            $_SESSION['user_id']  = $user['ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role']     = $user['Role'];

            return $this->json([
                'message' => 'Đăng nhập thành công!',
                'user'    => $user
            ], 200);
        }

        // Return login error payload
        return $this->json([
            'error'  => $result['message'] ?? 'Đăng nhập thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }

    /**
     * POST /api/auth/logout
     * Log the user out by destroying active session variables and cookies.
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session variables
        $_SESSION = [];

        // Clear browser session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Destroy the server-side session
        session_destroy();

        return $this->json([
            'message' => 'Đăng xuất thành công!'
        ], 200);
    }
}
