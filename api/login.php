<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công",
            "data" => [
                "id" => $user['id'],
                "phone" => $user['sdt'],
                "full_name" => $user['name'],
                "role" => $user['role']
            ],
            "token" => $jwt
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