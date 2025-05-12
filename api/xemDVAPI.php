<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Nhận token từ URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu token']);
    exit;
}

$secret_key = 'cabaymaublutopaz'; // Bạn nên lấy từ file config cho thống nhất

try {
    // Giải mã token và tự động kiểm tra thời gian hết hạn
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    // Kiểm tra vai trò
    if (!isset($decoded->role) || $decoded->role !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }

    // Truy vấn thông tin người dùng
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT sdt, name FROM user WHERE id = :user_id");
    $stmt->execute(['user_id' => $decoded->user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
    }

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Phiên bản đăng nhập đã hết hạn. Vui lòng đăng nhập lại !',
        'error_code' => 'TOKEN_EXPIRED'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    // Chỉ hiển thị lỗi chi tiết trong môi trường dev
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Token không hợp lệ hoặc lỗi máy chủ';
    error_log('JWT Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg]);
}
