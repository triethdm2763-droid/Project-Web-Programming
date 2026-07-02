<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    }
}

// Restore session from JWT if session is empty and cookie is present
if (empty($_SESSION['user_id']) && !empty($_COOKIE['token'])) {
    $jwtPath = __DIR__ . '/../../backend/src/utils/JWT.php';
    if (file_exists($jwtPath)) {
        require_once $jwtPath;
        $payload = \App\Utils\JWT::decode($_COOKIE['token']);
        if ($payload) {
            $_SESSION['user_id']  = $payload['user_id'];
            $_SESSION['username'] = $payload['username'];
            $_SESSION['role']     = $payload['role'];
        }
    }
}
