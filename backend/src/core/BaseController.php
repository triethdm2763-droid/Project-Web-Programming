<?php
namespace App\Core;

class BaseController {
    
    /**
     * Send a standard JSON response to the client
     * 
     * @param array|object $data The response data payload
     * @param int $statusCode The HTTP status code (default 200)
     */
    protected function json($data, int $statusCode = 200) {
        // Clear any previous output buffer to prevent corrupt JSON
        if (ob_get_length()) {
            ob_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Read and decode the raw JSON request body sent by the client
     * 
     * @return array decoded JSON array or empty array if parsing fails
     */
    protected function getRequestBody(): array {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
