<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost'); // Điều chỉnh đúng origin
header('Access-Control-Allow-Credentials: true'); // Cho phép gửi cookie
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


// Lấy token từ cookie
$token = isset($_COOKIE['access_token']) ? $_COOKIE['access_token'] : '';

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu toke : trang user']);
    exit;
}


try {
    $secret_key = 'cabaymaublutopaz'; // Thay bằng khóa bí mật từ api/login.php
    // Giải mã token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    // Kiểm tra role nếu cần
    if ($decoded->role !== 0) { // Chỉ cho phép nếu là khách hàng
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Không phải khách hàng']);
        exit;
    }


    // Kết nối đến DB và lấy thông tin người dùng
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

}catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Phiên bản đăng nhập đã hết hạn. Vui lòng đăng nhập lại !',
        'error_code' => 'TOKEN_EXPIRED'
    ]);}
 catch (Exception $e) {
    http_response_code(500);
    error_log('JWT Error: ' . $e->getMessage()); // Ghi log vào php_error_log
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc lỗi server']);
}

$conn = null;
?>
