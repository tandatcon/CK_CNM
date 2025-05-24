<?php
putenv('APP_ENV=development');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once __DIR__ . '/../includes/JwtHandler.php'; // Thêm file JwtHandler

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
        // Xác thực token
        $decoded = $jwtHandler->validateToken();
        
        // Kiểm tra quyền admin (role = 1)
        if ($jwtHandler->getUserRole() !== 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
            exit;
        }
        
        $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($order_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ', 'error_code' => 'INVALID_ORDER_ID']);
            exit;
        }

        $stmt = $conn->prepare("SELECT 
        a.id, a.id_nguoikham, a.namsinh, a.gt, 
        a.id_benhvien, b.ten_benhvien, 
        a.diemhen, a.ngayhen, a.giohen, 
        a.tinhtrang_nguoikham, a.tongchiphi, 
        a.trangthai, a.loai, a.quanhe_ho, 
        a.ten_ho, a.sdt_ho, a.id_nhanvien, 
        a.giodichvu, a.lydo_tuchoi, 
        u.name, u.sdt, v.hoten, v.gtnv, 
        (SELECT AVG(sao) FROM danhgia WHERE id_nhanvien = v.id_user) as sao,
        v.namsinhnv
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        JOIN user u ON a.id_nguoikham = u.id
        JOIN nhanvien v ON a.id_nhanvien = v.id_user
        
        WHERE a.id = :order_id 
        AND a.id_nguoikham = :user_id");

        $stmt->execute(['order_id' => $order_id, 'user_id' => $jwtHandler->getUserId()]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        }

    } catch (Exception $e) {
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