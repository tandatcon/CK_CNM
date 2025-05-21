<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once 'auth_middleware.php'; // Đường dẫn tới file chứa hàm authenticateUser

try {
    // Gọi hàm authenticateUser để kiểm tra và làm mới token nếu cần
    $decoded = authenticateUser(); // Hàm này sẽ xử lý AT hết hạn và trả về thông tin người dùng

    // Kết nối CSDL
    $conn = getDBConnection();

    // Xóa refresh token liên quan trong CSDL
    $stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE id_user = :user_id");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn']);
        exit;
    }

    $success = $stmt->execute(['user_id' => $decoded->user_id]);

    // Xóa cookie access_token và refresh_token
    setcookie('access_token', '', time() - 3600, '/', '', false, true);
    setcookie('refresh_token', '', time() - 3600, '/', '', false, true);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Đăng xuất thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa token trong hệ thống']);
    }

    $conn = null;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực: ' . $e->getMessage()]);
}
?>