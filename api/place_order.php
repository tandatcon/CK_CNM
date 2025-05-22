<?php
putenv('APP_ENV=development');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/JwtHandler.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start();

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();
    
    // 1. Xử lý Token bằng JwtHandler
    $jwt = new JwtHandler($conn);
    try {
        $decoded = $jwt->validateToken();
        
        // Kiểm tra vai trò (0 là khách hàng)
        if ($jwt->getUserRole() !== 0) {
            throw new Exception('Chỉ khách hàng được phép đặt dịch vụ', 403);
        }
        
        $user_id = $jwt->getUserId();
        
    } catch (Exception $e) {
        throw $e; // Chuyển tiếp exception để xử lý chung
    }

    // 2. Phần xử lý nghiệp vụ đặt dịch vụ (giữ nguyên)
    // Nhận dữ liệu từ request POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Dữ liệu đầu vào không hợp lệ', 400);
    }

    // Kiểm tra các trường bắt buộc
    $required_fields = ['full_name', 'phone', 'diemhen', 'hospital_id', 'appointment_date', 'appointment_time', 'condition'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            throw new Exception("Trường $field là bắt buộc", 400);
        }
    }

    // Kiểm tra user_id và hospital_id
    $hospital_id = (int)$input['hospital_id'];
    $stmt = $conn->prepare("SELECT u.id, h.id_benhvien FROM user u LEFT JOIN hospitals h ON h.id_benhvien = ? WHERE u.id = ?");
    $stmt->execute([$hospital_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result || !$result['id']) {
        throw new Exception('Người dùng không tồn tại', 404);
    }
    if (!$result['id_benhvien']) {
        throw new Exception('Bệnh viện không tồn tại', 400);
    }

    // Kiểm tra định dạng dữ liệu
    $phone = trim($input['phone']);
    if (!preg_match('/^0\d{9}$/', $phone)) {
        throw new Exception('Số điện thoại không hợp lệ', 400);
    }

    $guardian_phone = isset($input['guardian_phone']) ? trim($input['guardian_phone']) : '';
    if ($guardian_phone && !preg_match('/^0\d{9}$/', $guardian_phone)) {
        throw new Exception('Số điện thoại người được đặt hộ không hợp lệ', 400);
    }

    $appointment_date = trim($input['appointment_date']);
    if (strtotime($appointment_date) <= strtotime(date('Y-m-d'))) {
        throw new Exception('Ngày khám phải sau ngày hiện tại', 400);
    }

    $appointment_time = trim($input['appointment_time']);
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $appointment_time)) {
        throw new Exception('Giờ hẹn không hợp lệ (HH:MM, 00:00-23:59)', 400);
    }

    // Chuẩn bị dữ liệu để lưu
    $full_name = trim($input['full_name']);
    $phone = trim($input['phone']);
    $diemhen = trim($input['diemhen']);
    $appointment_time = $input['appointment_time'];
    $condition = trim($input['condition']);
    $quanhe_ho = isset($input['guardian_relation']) ? trim($input['guardian_relation']) : '';
    $ten_ho = isset($input['guardian_name']) ? trim($input['guardian_name']) : '';
    $sdt_ho = isset($input['guardian_phone']) ? trim($input['guardian_phone']) : '';
    $namsinh = $input['namsinh'];
    $gt = $input['gt'];
    $trangthai = "0";

    // Kiểm tra namsinh hợp lệ
    if ($namsinh && ($namsinh < 1900 || $namsinh > date('Y'))) {
        throw new Exception('Năm sinh không hợp lệ', 400);
    }

    // Lưu vào bảng datdichvu
    if ($quanhe_ho != '') {
        $stmt = $conn->prepare("
            INSERT INTO datdichvu (
                id_nguoikham, quanhe_ho, ten_ho, namsinh, gt, sdt_ho, id_benhvien, diemhen, ngayhen, giohen, tinhtrang_nguoikham, trangthai
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $quanhe_ho,
            $ten_ho,
            $namsinh,
            $gt,
            $sdt_ho,
            $hospital_id,
            $diemhen,
            $appointment_date,
            $appointment_time,
            $condition,
            $trangthai,
        ]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO datdichvu (
                id_nguoikham, gt, namsinh, id_benhvien, diemhen, ngayhen, giohen, tinhtrang_nguoikham, trangthai
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $gt,
            $namsinh,
            $hospital_id,
            $diemhen,
            $appointment_date,
            $appointment_time,
            $condition,
            $trangthai,
        ]);
    }

    // Lấy ID đơn hàng vừa tạo
    $order_id = $conn->lastInsertId();

    // Trả về phản hồi thành công
    echo json_encode([
        'success' => true, 
        'message' => 'Đặt dịch vụ thành công',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode() ?: 'SERVER_ERROR'
    ]);
    error_log('Error [' . $e->getCode() . ']: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
} finally {
    ob_end_flush();
    if (isset($conn)) {
        $conn = null;
    }
}
?>