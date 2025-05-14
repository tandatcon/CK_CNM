<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$refresh_token = isset($_COOKIE['refresh_token']) ? $_COOKIE['refresh_token'] : '';

if (empty($refresh_token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Thiếu refresh token']);
    exit;
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token = :token");
    $stmt->execute(['token' => $refresh_token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($token_data && strtotime($token_data['expires_at']) > time()) {
        // Lấy thông tin người dùng
        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :user_id");
        $stmt->execute(['user_id' => $token_data['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $config = require __DIR__ . '/../includes/jwt_config.php';

            // Tạo access token mới
            $payload = [
                "iss" => $config['issuer'],
                "aud" => $config['audience'],
                "iat" => time(),
                "exp" => time() + $config['expires_in'],
                "user_id" => $user['id'],
                "phone" => $user['sdt'],
                "full_name" => $user['name'],
                "role" => $user['role']
            ];
            $new_jwt = JWT::encode($payload, $config['secret_key'], 'HS256');

            // Đặt cookie access_token mới
            setcookie('access_token', $new_jwt, [
                'expires' => time() + $config['expires_in'],
                'path' => '/',
                'domain' => 'localhost',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            echo json_encode(['success' => true, 'message' => 'Access token mới đã được cấp']);
        } else {
            throw new Exception('Người dùng không tồn tại');
        }
    } else {
        // Xóa refresh token nếu không hợp lệ
        $stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE token = :token");
        $stmt->execute(['token' => $refresh_token]);
        setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost']);
        setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost']);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Refresh token không hợp lệ hoặc hết hạn']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}

$conn = null;
?>