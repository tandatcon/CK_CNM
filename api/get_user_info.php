<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu token']);
    exit;
}

try {
    $secret_key = 'cabaymaublutopaz'; // Thay bằng khóa bí mật từ api/login.php
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->role !== 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Không phải khách hàng']);
        exit;
    }

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
} catch (Exception $e) {
    http_response_code(500);
    error_log('JWT Error: ' . $e->getMessage()); // Ghi log vào php_error_log
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc lỗi server']);
}

$conn = null;
?>