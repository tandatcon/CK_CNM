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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (empty($phone) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Số điện thoại và mật khẩu không được để trống"]);
    exit;
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM user WHERE sdt = :sdt");
    $stmt->execute(['sdt' => $phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['pw'])) {
        $config = require __DIR__ . '/../includes/jwt_config.php';

        // Tạo access token
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
        $jwt = JWT::encode($payload, $config['secret_key'], 'HS256');

        // Tạo refresh token
        $refresh_token = bin2hex(random_bytes(32)); // Chuỗi ngẫu nhiên 64 ký tự
        $refresh_expiry = time() + (30 * 24 * 3600); // 30 ngày

        // Lưu refresh token vào DB
        $stmt = $conn->prepare("INSERT INTO refresh_tokens (id_user, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute([
            'user_id' => $user['id'],
            'token' => $refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry)
        ]);

        // Đặt cookie access_token
        setcookie('access_token', $jwt, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
              
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Đặt cookie refresh_token
        setcookie('refresh_token', $refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công",
            "data" => [
                "id" => $user['id'],
                "phone" => $user['sdt'],
                "full_name" => $user['name'],
                "role" => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Số điện thoại hoặc mật khẩu không đúng"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
}

$conn = null;
?>