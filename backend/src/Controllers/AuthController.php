<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
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
            $user = $result['user'];

            // Generate JWT
            $token = \App\Utils\JWT::encode([
                'user_id'  => $user['ID'],
                'username' => $user['Username'],
                'role'     => $user['Role']
            ]);

            // Set cookie for browser clients (expires in 24 hours, httpOnly = true)
            setcookie('token', $token, time() + 3600 * 24, '/', '', false, true);

            // Bind authentication parameters into active Session State
            Session::start();

            $_SESSION['user_id']  = $user['ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role']     = $user['Role'];

            return $this->json([
                'message' => 'Đăng nhập thành công!',
                'user'    => $user,
                'token'   => $token
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
        Session::start();

        // Clear all session variables
        $_SESSION = [];

        // Clear JWT cookie
        setcookie('token', '', time() - 3600, '/', '', false, true);

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

    /**
     * GET /api/auth/me
     * Retrieve current logged in user details.
     */
    public function me() {
        $result = $this->authService->getCurrentUser();
        if ($result['status'] === 'success') {
            return $this->json([
                'user' => $result['user']
            ], 200);
        }

        return $this->json([
            'error' => $result['message']
        ], $result['code']);
    }

    /**
     * POST /api/auth/forgot-password
     * Request a password reset OTP
     */
    public function requestReset() {
        $data = $this->getRequestBody();
        $result = $this->authService->requestPasswordReset($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => $result['message'],
                'otp'     => $result['otp'] ?? null // Simulation
            ], 200);
        }

        return $this->json([
            'error'  => $result['message'] ?? 'Yêu cầu thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }

    /**
     * POST /api/auth/reset-password
     * Verify OTP and change password
     */
    public function performReset() {
        $data = $this->getRequestBody();
        $result = $this->authService->resetPassword($data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => $result['message']
            ], 200);
        }

        return $this->json([
            'error'  => $result['message'] ?? 'Đặt lại mật khẩu thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }

    /**
     * POST /api/auth/profile/update
     * Update authenticated user profile details.
     */
    public function updateProfile() {
        Session::start();
        if (empty($_SESSION['user_id'])) {
            return $this->json(['error' => 'Bạn chưa đăng nhập.'], 401);
        }

        $userId = (int)$_SESSION['user_id'];
        
        // Since we might upload files (multipart/form-data), $_POST will contain the fields
        // and $_FILES will contain the avatar file.
        $data = $_POST;
        
        // Handle avatar upload if exists
        if (!empty($_FILES['avatar'])) {
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                return $this->json(['error' => 'Lỗi khi tải ảnh lên. Mã lỗi PHP: ' . $_FILES['avatar']['error']], 400);
            }
            
            if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                
                // Validate extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowedExtensions)) {
                    return $this->json(['error' => 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: ' . implode(', ', $allowedExtensions)], 400);
                }
                
                // Validate size (max 2MB for avatars)
                if ($file['size'] > 2 * 1024 * 1024) {
                    return $this->json(['error' => 'Dung lượng ảnh tối đa là 2MB.'], 400);
                }
                
                $targetDir = __DIR__ . '/../../uploads/avatars/';
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $newFilename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $targetFile = $targetDir . $newFilename;
                
                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    // Relative path to be stored in DB and returned
                    $data['avatar'] = '/backend/uploads/avatars/' . $newFilename;
                } else {
                    return $this->json(['error' => 'Không thể lưu file ảnh vào thư mục backend/uploads/avatars.'], 500);
                }
            }
        }

        $result = $this->authService->updateProfile($userId, $data);

        if ($result['status'] === 'success') {
            return $this->json([
                'message' => $result['message'],
                'avatar'  => $data['avatar'] ?? null
            ], 200);
        }

        return $this->json([
            'error'  => $result['message'] ?? 'Cập nhật thất bại.',
            'errors' => $result['errors'] ?? null
        ], $result['code']);
    }
}
