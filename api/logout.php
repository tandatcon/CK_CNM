<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

// Kết nối CSDL
$conn = getDBConnection();

// Lấy access_token từ cookie
$access_token = $_COOKIE['access_token'] ?? null;

if (!$access_token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy token']);
    exit;
}

// Xoá refresh token liên quan trong CSDL
$stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE token = :token");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn']);
    exit;
}

$success = $stmt->execute(['token' => $access_token]);

// Xoá cookie access_token và refresh_token
setcookie('access_token', '', time() - 3600, '/', '', false, true);
setcookie('refresh_token', '', time() - 3600, '/', '', false, true);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Đăng xuất thành công']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xoá token trong hệ thống']);
}

$conn = null;
?>
