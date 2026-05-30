<?php
declare(strict_types=1);

namespace services;

use repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function register(array $input): array
    {
        $name     = trim($input['name']  ?? '');
        $email    = trim($input['email'] ?? '');
        $password = $input['password']   ?? '';

        if (empty($name) || empty($email) || empty($password))
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        if (strlen($password) < 6)
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        if ($this->userRepo->findByEmail($email))
            return ['success' => false, 'message' => 'Email đã được sử dụng'];

        $id = $this->userRepo->create($name, $email, password_hash($password, PASSWORD_BCRYPT));

        return [
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data'    => ['id' => $id, 'name' => $name, 'email' => $email],
        ];
    }

    public function login(array $input): array
    {
        $email    = trim($input['email'] ?? '');
        $password = $input['password']   ?? '';

        if (empty($email) || empty($password))
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];

        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password']))
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng'];

        $this->startSession();
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in']  = true;

        return [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data'    => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']],
        ];
    }

    public function logout(): array
    {
        $this->startSession();
        $_SESSION = [];
        session_destroy();
        return ['success' => true, 'message' => 'Đăng xuất thành công'];
    }

    public function getCurrentUser(): array
    {
        $this->startSession();
        if (empty($_SESSION['logged_in']))
            return ['success' => false, 'message' => 'Chưa đăng nhập'];

        $user = $this->userRepo->findById((int) $_SESSION['user_id']);
        return $user
            ? ['success' => true, 'data' => $user]
            : ['success' => false, 'message' => 'Không tìm thấy người dùng'];
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
}
