<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Validators\Validator;

class AuthService {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    /**
     * Process user registration.
     * 
     * @param array $data Input data payload
     * @return array Status array indicating success or failure with error messages
     */
    public function register(array $data): array {
        // 1. Validate Input Data
        $rules = [
            'username' => 'required|min:3|max:50',
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'phone'    => 'phone'
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => $errors
            ];
        }

        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];
        $phone = isset($data['phone']) ? trim($data['phone']) : null;

        // 2. Check if Username Already Exists
        if ($this->userRepository->findByUsername($username) !== null) {
            return [
                'status' => 'error',
                'code'   => 409, // HTTP Conflict
                'errors' => ['username' => ['Tên đăng nhập này đã tồn tại trên hệ thống.']]
            ];
        }

        // 3. Check if Email Already Exists
        if ($this->userRepository->findByEmail($email) !== null) {
            return [
                'status' => 'error',
                'code'   => 409, // HTTP Conflict
                'errors' => ['email' => ['Địa chỉ email này đã được đăng ký sử dụng.']]
            ];
        }

        // 4. Secure Password Hashing
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // 5. Save to Database
        $userId = $this->userRepository->create($username, $email, $passwordHash, $phone);

        return [
            'status'  => 'success',
            'code'    => 201, // HTTP Created
            'user_id' => $userId
        ];
    }

    /**
     * Process user login.
     * 
     * @param array $data Input credentials payload
     * @return array Status array indicating success or failure with error messages
     */
    public function login(array $data): array {
        // 1. Validate Input Fields
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code'   => 400,
                'errors' => $errors
            ];
        }

        $inputUsername = trim($data['username']);
        $password = $data['password'];

        // 2. Retrieve User by Username or Email (Flexible Login)
        $user = $this->userRepository->findByUsername($inputUsername);
        if ($user === null) {
            // Try checking by email just in case the user typed their email
            $user = $this->userRepository->findByEmail($inputUsername);
        }

        if ($user === null) {
            return [
                'status'  => 'error',
                'code'    => 401, // Unauthorized
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'
            ];
        }

        // 3. Verify Hashed Password
        if (!password_verify($password, $user['Password'])) {
            return [
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'
            ];
        }

        // 4. Check User Account Status
        if ($user['Status'] !== 'active') {
            return [
                'status'  => 'error',
                'code'    => 403, // Forbidden
                'message' => 'Tài khoản của bạn hiện đang bị khóa hoặc ngưng hoạt động.'
            ];
        }

        // 5. Securely Strip Sensitive Password Hash
        unset($user['Password']);

        return [
            'status' => 'success',
            'code'   => 200,
            'user'   => $user
        ];
    }

    /**
     * Get details of the currently authenticated user from local session state.
     * 
     * @return array Status payload with user profile or error details
     */
    public function getCurrentUser(): array {
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

        $user = $this->userRepository->findById((int)$_SESSION['user_id']);
        if ($user === null) {
            return [
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Không tìm thấy người dùng.'
            ];
        }

        return [
            'status' => 'success',
            'code'   => 200,
            'user'   => $user
        ];
    }
}
