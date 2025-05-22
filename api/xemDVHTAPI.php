<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/JwtHandler.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();

try {
    // 1. Khởi tạo kết nối và JWT Handler
    $conn = getDBConnection();
    $jwt = new JwtHandler($conn);

    // 2. Xác thực token và kiểm tra quyền
    $decoded = $jwt->validateToken();
    
    if ($jwt->getUserRole() !== 0) {
        throw new Exception('Chỉ khách hàng được phép xem đơn đã hoàn tất', 403);
    }

    $user_id = $jwt->getUserId();

    // 3. Truy vấn danh sách đơn hoàn tất (trangthai = 3)
    
    $stmt = $conn->prepare("
        SELECT 
            a.id, a.id_nguoikham, a.namsinh, a.gt, 
            a.id_benhvien, b.ten_benhvien, 
            a.diemhen, a.ngayhen, a.giohen, 
            a.tinhtrang_nguoikham, a.tongchiphi, a.trangthai
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        WHERE a.id_nguoikham = :user_id 
        AND a.trangthai = :trangthai
        ORDER BY a.ngayhen DESC, a.giohen DESC
    ");
    
    $stmt->execute([
        'user_id' => $user_id,
        'trangthai' => 3 // Đơn đã hoàn tất
    ]);
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Trả về kết quả
    if (empty($orders)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'Không có đơn dịch vụ đã hoàn tất'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    }

} catch (Exception $e) {
    // 5. Xử lý lỗi tập trung
    $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($statusCode);
    
    $errorMessage = (getenv('APP_ENV') === 'development') 
        ? $e->getMessage() 
        : ($statusCode === 500 ? 'Lỗi máy chủ' : $e->getMessage());
    
    error_log(sprintf(
        'Error %s: %s in %s on line %d',
        $statusCode,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'error_code' => $e->getCode() ?: 'SERVER_ERROR'
    ]);
} finally {
    // 6. Dọn dẹp tài nguyên
    ob_end_flush();
    if (isset($conn)) {
        $conn = null;
    }
}
?>