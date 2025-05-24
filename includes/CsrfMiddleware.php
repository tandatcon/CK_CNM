<?php
    class CsrfMiddleware {
        public static function initSession() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    
        public static function generateToken() {
            self::initSession();
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
    
        public static function verifyToken() {
            if (session_status() === PHP_SESSION_NONE) session_start();
    
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
            if ($sessionToken === '' || $headerToken === '' || $sessionToken !== $headerToken) {
                http_response_code(403);
                echo json_encode(["error" => "Invalid CSRF token"]);
                exit;
            }
        }
    }
    

?>