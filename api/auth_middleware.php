<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

function authenticateUser() {
    $conn = getDBConnection();
    $config = require __DIR__ . '/../includes/jwt_config.php';

    // Kiểm tra cookie access_token
    if (!isset($_COOKIE['access_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu token', 'error_code' => 'MISSING_TOKEN']);
        exit;
    }

    $access_token = $_COOKIE['access_token'];
    try {
        // Thử giải mã access_token
        $decoded = JWT::decode($access_token, new Key($config['secret_key'], 'HS256'));
    } catch (ExpiredException $e) {
        // Access token hết hạn, thử làm mới
        error_log("Access token expired at: " . date('Y-m-d H:i:s', $e->getPayload()->exp));
        $refresh_token = $_COOKIE['refresh_token'] ?? '';
        error_log("Refresh token: " . ($refresh_token ?: "empty"));

        if (empty($refresh_token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Thiếu refresh token', 'error_code' => 'MISSING_REFRESH_TOKEN']);
            exit;
        }

        // Kiểm tra refresh_token trong CSDL
        $stmt = $conn->prepare("SELECT id_user, expires_at FROM refresh_tokens WHERE token = :token AND expires_at > NOW()");
        $stmt->execute(['token' => $refresh_token]);
        $refresh_data = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Refresh data: " . json_encode($refresh_data) . ", NOW: " . date('Y-m-d H:i:s'));

        if (!$refresh_data) {
            error_log("Refresh token not found or expired");
            $stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE token = :token");
            $stmt->execute(['token' => $refresh_token]);
            setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Refresh token không hợp lệ hoặc hết hạn", "error_code" => "INVALID_REFRESH_TOKEN"]);
            exit;
        }

        // Lấy thông tin người dùng từ refresh_data
        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $refresh_data['id_user']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại", "error_code" => "USER_NOT_FOUND"]);
            exit;
        }

        // Tạo access_token mới
        $payload = [
            "iss" => $config['issuer'],
            "aud" => $config['audience'],
            "iat" => time(),
            "exp" => time() + $config['expires_in'],
            "user_id" => $user['id'],
            "phone" => $user['sdt'],
            "name" => $user['name'],
            "role" => $user['role']
        ];
        $new_access_token = JWT::encode($payload, $config['secret_key'], 'HS256');

        // Tạo refresh_token mới
        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600);

        // Cập nhật refresh_token trong CSDL
        $stmt = $conn->prepare("UPDATE refresh_tokens SET token = :token, expires_at = :expires_at WHERE id_user = :user_id");
        $stmt->execute([
            'token' => $new_refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry),
            'user_id' => $user['id']
        ]);

        // Đặt lại cookie
        if (!setcookie('access_token', $new_access_token, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ])) {
            error_log("Failed to set new access_token cookie");
        }
        if (!setcookie('refresh_token', $new_refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ])) {
            error_log("Failed to set new refresh_token cookie");
        }

        // Gán lại decoded với thông tin mới
        $decoded = (object)$payload;
    }

    return $decoded;
}
?>