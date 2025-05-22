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
    // 1. Initialize database connection and JWT handler
    $conn = getDBConnection();
    $jwt = new JwtHandler($conn);

    // 2. Authenticate and validate token
    $decoded = $jwt->validateToken();
    
    // 3. Check user role (1 for staff)
    if ($jwt->getUserRole() !== 1) {
        throw new Exception('Chỉ có nhân viên mới có quyền truy cập!', 403);
    }

    $staff_id = $jwt->getUserId();

    // 4. Query today's appointments assigned to this staff
    $stmt = $conn->prepare("
        SELECT 
            a.id, a.id_nguoikham, a.namsinh, a.gt, 
            a.id_benhvien, b.ten_benhvien, 
            a.diemhen, a.ngayhen, a.giohen, 
            a.tinhtrang_nguoikham, a.tongchiphi, 
            a.trangthai, a.loai, a.quanhe_ho, 
            a.ten_ho, a.sdt_ho, a.giobatdau, 
            a.gioketthuc
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        WHERE a.id_nhanvien = :staff_id 
        AND a.trangthai IN (1,2,3) 
        AND a.ngayhen = CURDATE()
        ORDER BY a.giohen ASC
    ");
    
    $stmt->execute(['staff_id' => $staff_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Return response
    if (empty($appointments)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'Không tìm thấy đơn hàng hôm nay!',
            'error_code' => 'NO_APPOINTMENTS'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => $appointments
        ]);
    }

} catch (Exception $e) {
    // 6. Error handling
    $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($statusCode);
    
    $errorMessage = (getenv('APP_ENV') === 'development') 
        ? $e->getMessage() 
        : ($statusCode === 500 ? 'Server error' : $e->getMessage());
    
    error_log(sprintf(
        'Staff Appointments Error %s: %s in %s on line %d',
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
    // 7. Clean up
    ob_end_flush();
    if (isset($conn)) {
        $conn = null;
    }
}
?>