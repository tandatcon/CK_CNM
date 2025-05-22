<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once __DIR__ . '/../includes/JwtHandler.php'; // Thêm lớp JwtHandler

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();
    
    // Khởi tạo JwtHandler
    $jwtHandler = new JwtHandler($conn);
    
    try {
        // Xác thực token và tự động làm mới nếu cần
        $decoded = $jwtHandler->validateToken();
        
        // Kiểm tra quyền admin (role = 1)
        if ($jwtHandler->getUserRole() != 1) { // Sửa thành != để phù hợp với kiểu dữ liệu
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
            exit;
        }

        // Cập nhật giờ bắt đầu dịch vụ
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $giohientai = date("H:i");
        
        $stmt = $conn->prepare("UPDATE datdichvu SET giobatdau = :giobatdau , trangthai = 2
        WHERE id_nhanvien = :user_id AND trangthai = 1 AND ngayhen = CURDATE()");
        $stmt->execute([
            'user_id' => $jwtHandler->getUserId(),
            'giobatdau' => $giohientai
        ]);
        $affectedRows = $stmt->rowCount();

        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật giờ bắt đầu thành công']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Bạn đã bắt đầu đơn dịch vụ này']);
        }

    } catch (Exception $e) {
        // Xử lý lỗi từ JwtHandler
        http_response_code($e->getCode() ?: 401);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'error_code' => strtoupper(str_replace(' ', '_', $e->getMessage()))
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Lỗi máy chủ';
    error_log('Server Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg, 'error_code' => 'SERVER_ERROR']);
}

ob_end_flush();
$conn = null;