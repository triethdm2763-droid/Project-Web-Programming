<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
<<<<<<< HEAD
    $sessionPath = __DIR__ . '/../../backend/storage/sessions';
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

function app_base_url()
{
    static $baseUrl = null;

    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $markers = ['/frontend/', '/backend/', '/index.php'];

    foreach ($markers as $marker) {
        $position = strpos($scriptName, $marker);
        if ($position !== false) {
            $baseUrl = rtrim(substr($scriptName, 0, $position), '/');
            return $baseUrl;
        }
    }

    $directory = str_replace('\\', '/', dirname($scriptName));
    $baseUrl = ($directory === '/' || $directory === '.') ? '' : rtrim($directory, '/');
    return $baseUrl;
}

function app_url($path = '')
{
    $path = '/' . ltrim($path, '/');
    return app_base_url() . $path;
}

if (!defined('APP_INTERNAL_URL_REWRITE_STARTED') && PHP_SAPI !== 'cli') {
    define('APP_INTERNAL_URL_REWRITE_STARTED', true);
    ob_start(function ($html) {
        $baseUrl = app_base_url();
        if ($baseUrl === '') {
            return $html;
        }

        return str_replace(
            ['"/frontend/', "'/frontend/", '"/backend/', "'/backend/"],
            ['"' . $baseUrl . '/frontend/', "'" . $baseUrl . '/frontend/', '"' . $baseUrl . '/backend/', "'" . $baseUrl . '/backend/'],
            $html
        );
    });
}

=======
    session_start();
}

>>>>>>> 798812e7ff3d82d10aedc22d1123ffffaa1407f2
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
