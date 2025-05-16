<?php

ob_start();

// Bắt buộc: chính xác origin frontend
header("Access-Control-Allow-Origin: http://localhost"); 
header("Access-Control-Allow-Credentials: true"); // Cho phép gửi & nhận cookie

// Nếu là request preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $config = require __DIR__ . '/../includes/jwt_config.php';

    $access_token = $_COOKIE['access_token'] ?? '';
    $refresh_token = $_COOKIE['refresh_token'] ?? '';

    error_log("Access token: " . ($access_token ?: "empty"));
    error_log("Refresh token: " . ($refresh_token ?: "empty"));

    $conn = getDBConnection();

    if (empty($access_token)) {
        // Nếu không có access token, thử dùng refresh token để cấp lại
        if (empty($refresh_token)) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Vui lòng đăng nhập để đặt dịch vụ !",
                "error_code" => "NOT_LOGGED_IN"
            ]);
            exit;
        }

        // Kiểm tra refresh token còn hợp lệ không
        $stmt = $conn->prepare("SELECT id_user, expires_at FROM refresh_tokens WHERE token = :token AND expires_at > NOW()");
        $stmt->execute(['token' => $refresh_token]);
        $refresh_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$refresh_data) {
            // Xoá cookie nếu refresh token không hợp lệ hoặc hết hạn
            setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);

            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Refresh token không hợp lệ hoặc hết hạn",
                "error_code" => "INVALID_REFRESH_TOKEN"
            ]);
            exit;
        }

        // Lấy thông tin user
        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $refresh_data['id_user']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Người dùng không tồn tại",
                "error_code" => "USER_NOT_FOUND"
            ]);
            exit;
        }

        // Tạo access token mới
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

        // Tạo refresh token mới, gia hạn thời gian
        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600); // 30 ngày

        // Cập nhật refresh token trong database
        $stmt = $conn->prepare("UPDATE refresh_tokens SET token = :token, expires_at = :expires_at WHERE id_user = :user_id");
        $stmt->execute([
            'token' => $new_refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry),
            'user_id' => $user['id']
        ]);

        // Set lại cookie cho access token & refresh token
        setcookie('access_token', $new_access_token, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
            //'domain' => 'localhost', // nếu cần
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('refresh_token', $new_refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            //'domain' => 'localhost', // nếu cần
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Đã làm mới token",
            "data" => [
                "id" => $user['id'],
                "phone" => $user['sdt'],
                "name" => $user['name'],
                "role" => $user['role']
            ]
        ]);
        exit;
    }

    // Nếu có access token, kiểm tra hợp lệ
    try {
        $decoded = JWT::decode($access_token, new Key($config['secret_key'], 'HS256'));
        $user_id = $decoded->user_id;

        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Người dùng không tồn tại",
                "error_code" => "USER_NOT_FOUND"
            ]);
            exit;
        }

        echo json_encode([
            "success" => true,
            "message" => "Đã đăng nhập",
            "data" => [
                "id" => $user['id'],
                "phone" => $user['sdt'],
                "name" => $user['name'],
                "role" => $user['role']
            ]
        ]);
    } catch (\Firebase\JWT\ExpiredException $e) {
        error_log("Access token expired at: " . date('Y-m-d H:i:s', $e->getExpiredAt()));

        // Access token hết hạn, dùng refresh token làm mới
        if (empty($refresh_token)) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Thiếu refresh token",
                "error_code" => "MISSING_REFRESH_TOKEN"
            ]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id_user, expires_at FROM refresh_tokens WHERE token = :token AND expires_at > NOW()");
        $stmt->execute(['token' => $refresh_token]);
        $refresh_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$refresh_data) {
            setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Refresh token không hợp lệ hoặc hết hạn",
                "error_code" => "INVALID_REFRESH_TOKEN"
            ]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $refresh_data['id_user']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Người dùng không tồn tại",
                "error_code" => "USER_NOT_FOUND"
            ]);
            exit;
        }

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

        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600);

        $stmt = $conn->prepare("UPDATE refresh_tokens SET token = :token, expires_at = :expires_at WHERE id_user = :user_id");
        $stmt->execute([
            'token' => $new_refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry),
            'user_id' => $user['id']
        ]);

        setcookie('access_token', $new_access_token, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
            //'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('refresh_token', $new_refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            //'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Đã làm mới token",
            "data" => [
                "id" => $user['id'],
                "phone" => $user['sdt'],
                "name" => $user['name'],
                "role" => $user['role']
            ]
        ]);
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        error_log("Invalid access token signature");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Access token không hợp lệ",
            "error_code" => "INVALID_TOKEN"
        ]);
        exit;
    }
} catch (Exception $e) {
    error_log("Server error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server",
        "error_code" => "SERVER_ERROR"
    ]);
    exit;
}

$conn = null;
ob_end_flush();

?>
