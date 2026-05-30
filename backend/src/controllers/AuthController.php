<?php
declare(strict_types=1);

namespace controllers;

use services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(): void
    {
        $result = $this->authService->register($this->getJsonInput());
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
    }

    public function login(): void
    {
        $result = $this->authService->login($this->getJsonInput());
        http_response_code($result['success'] ? 200 : 401);
        echo json_encode($result);
    }

    public function logout(): void
    {
        echo json_encode($this->authService->logout());
    }

    public function me(): void
    {
        $result = $this->authService->getCurrentUser();
        http_response_code($result['success'] ? 200 : 401);
        echo json_encode($result);
    }

    private function getJsonInput(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : [];
    }
}
