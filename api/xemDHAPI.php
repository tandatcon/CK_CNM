<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start(); // Bắt đầu output buffering để tránh lỗi header

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();

    // Kiểm tra cookie access_token
    if (!isset($_COOKIE['access_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu token', 'error_code' => 'MISSING_TOKEN']);
        exit;
    }

    $config = require __DIR__ . '/../includes/jwt_config.php';
    $access_token = $_COOKIE['access_token'];
    $decoded = null;

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

    // Kiểm tra vai trò
    if (!isset($decoded->role) || $decoded->role !== 1) { // Sửa !== thành != vì role là string trong JWT
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
        exit;
    }

    // Truy vấn thông tin người dùng
    $stmt = $conn->prepare("SELECT a.id, a.id_nguoikham, a.namsinh, a.gt, a.id_benhvien, b.ten_benhvien, 
        a.diemhen, a.ngayhen, a.giohen, a.tinhtrang_nguoikham, a.tongchiphi, a.trangthai, a.loai, a.quanhe_ho, a.ten_ho, a.sdt_ho
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        WHERE a.id_nhanvien = :user_id AND a.trangthai IN (1,2)");
    $stmt->execute(['user_id' => $decoded->user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($orders && count($orders) > 0) {
        echo json_encode(['success' => true, 'data' => $orders]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không có đơn đặt dịch vụ nào']);
    }

} catch (Exception $e) {
    http_response_code(500);
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Token không hợp lệ hoặc lỗi máy chủ';
    error_log('JWT Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg, 'error_code' => 'SERVER_ERROR']);
}

ob_end_flush(); // Kết thúc output buffering
$conn = null;
?>