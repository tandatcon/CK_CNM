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
    
    if ($jwt->getUserRole() !== 2) {
        throw new Exception('Chỉ khách hàng được phép xem chi tiết đơn hàng', 403);
    }

    $user_id = $jwt->getUserId();

    // 3. Validate và lấy order_id
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($order_id <= 0) {
        throw new Exception('Mã đơn hàng không hợp lệ', 400);
    }

    // 4. Truy vấn chi tiết đơn hàng
    $stmt = $conn->prepare("SELECT 
        a.id, a.id_nguoikham, a.namsinh, a.gt, 
        a.id_benhvien, b.ten_benhvien, 
        a.diemhen, a.ngayhen, a.giohen, 
        a.tinhtrang_nguoikham, a.tongchiphi, 
        a.trangthai, a.loai, a.quanhe_ho, 
        a.ten_ho, a.sdt_ho, a.id_nhanvien, 
        a.giodichvu, a.lydo_tuchoi, 
        u.name, u.sdt
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        JOIN user u ON a.id_nguoikham = u.id
        WHERE a.id = :order_id 
        ");
    
    $stmt->execute(['order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. Trả về kết quả
    if (!$order) {
        throw new Exception('Không tìm thấy đơn hàng', 404);
    }

    echo json_encode([
        'success' => true,
        'data' => $order
    ]);

} catch (Exception $e) {
    // 6. Xử lý lỗi tập trung
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
    // 7. Dọn dẹp tài nguyên
    ob_end_flush();
    if (isset($conn)) {
        $conn = null;
    }
}
?>