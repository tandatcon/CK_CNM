<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once __DIR__ . '/../includes/JwtHandler.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();

try {
    $conn = getDBConnection();
    $jwtHandler = new JwtHandler($conn);
    
    try {
        // Xác thực token (bỏ comment nếu cần)
        // $decoded = $jwtHandler->validateToken();
        
        // Lấy ID nhân viên
        $id_nhanvien = $_GET['idNV'] ?? null;
        if (!$id_nhanvien || !is_numeric($id_nhanvien)) {
            throw new Exception('Thiếu ID nhân viên', 400);
        }

        // Truy vấn thông tin đầy đủ
        $stmt = $conn->prepare("
            select * from nhanvien where id_user = :user_id
        ");
        $stmt->execute(['user_id' => $id_nhanvien]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception('Không tìm thấy thông tin đánh giá', 404);
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

    } catch (PDOException $e) {
        throw new Exception('Lỗi cơ sở dữ liệu: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        throw $e;
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => strtoupper(str_replace(' ', '_', $e->getMessage()))
    ]);
    error_log('Error: ' . $e->getMessage());
}

ob_end_flush();
if (isset($conn)) {
    $conn = null;
}