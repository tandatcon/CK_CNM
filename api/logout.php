<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/JwtHandler.php'; // Sử dụng JwtHandler thay thế

ob_start();

try {
    // 1. Khởi tạo kết nối CSDL và JWT Handler
    $conn = getDBConnection();
    $jwt = new JwtHandler($conn);

    // 2. Xác thực người dùng
    $decoded = $jwt->validateToken();
    $user_id = $jwt->getUserId();

    // 3. Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
    $conn->beginTransaction();

    try {
        // 4. Xóa tất cả refresh token của người dùng
        $stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE id_user = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        // 5. Xóa cookie
        $cookieOptions = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false, // true nếu dùng HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        setcookie('access_token', '', $cookieOptions);
        setcookie('refresh_token', '', $cookieOptions);

        // 6. Commit transaction nếu thành công
        $conn->commit();

        // 7. Trả về kết quả thành công
        echo json_encode([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);

    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollBack();
        throw new Exception('Lỗi hệ thống khi xử lý đăng xuất', 500);
    }

} catch (Exception $e) {
    // 8. Xử lý lỗi tập trung
    $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($statusCode);
    
    $errorMessage = (getenv('APP_ENV') === 'development') 
        ? $e->getMessage() 
        : ($statusCode === 500 ? 'Lỗi máy chủ' : 'Lỗi xác thực');
    
    error_log('Lỗi đăng xuất: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'error_code' => $e->getCode() ?: 'SERVER_ERROR'
    ]);
} finally {
    // 9. Dọn dẹp tài nguyên
    ob_end_flush();
    if (isset($conn)) {
        $conn = null;
    }
}
?>