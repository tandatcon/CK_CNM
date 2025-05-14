<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost'); // Chỉ cho phép origin cụ thể
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();

    // Kiểm tra cookie access_token
    if (!isset($_COOKIE['access_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu token', 'error_code' => 'MISSING_TOKEN']);
        exit;
    }

    // Giải mã JWT
    $config = require __DIR__ . '/../includes/jwt_config.php';
    $decoded = JWT::decode($_COOKIE['access_token'], new Key($config['secret_key'], 'HS256'));

    // Kiểm tra vai trò
    if ($decoded->role !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Chỉ khách hàng được phép đặt dịch vụ', 'error_code' => 'FORBIDDEN']);
        exit;
    }

    // Lấy user_id từ JWT
    $user_id = $decoded->user_id;

    // Nhận dữ liệu từ request POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu đầu vào không hợp lệ', 'error_code' => 'INVALID_INPUT']);
        exit;
    }

    // Kiểm tra các trường bắt buộc
    $required_fields = ['full_name', 'phone', 'diemhen', 'hospital_id', 'appointment_date', 'appointment_time', 'condition'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Trường $field là bắt buộc", 'error_code' => 'MISSING_FIELD']);
            exit;
        }
    }

    // Kiểm tra user_id và hospital_id
    $hospital_id = (int)$input['hospital_id'];
    $stmt = $conn->prepare("SELECT u.id, h.id_benhvien FROM user u LEFT JOIN hospitals h ON h.id_benhvien = ? WHERE u.id = ?");
    $stmt->execute([$hospital_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result || !$result['id']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại', 'error_code' => 'USER_NOT_FOUND']);
        exit;
    }
    if (!$result['id_benhvien']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bệnh viện không tồn tại', 'error_code' => 'INVALID_HOSPITAL']);
        exit;
    }

    // Kiểm tra định dạng dữ liệu
    $phone = trim($input['phone']);
    if (!preg_match('/^0\d{9}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ', 'error_code' => 'INVALID_PHONE']);
        exit;
    }

    $guardian_phone = isset($input['guardian_phone']) ? trim($input['guardian_phone']) : '';
    if ($guardian_phone && !preg_match('/^0\d{9}$/', $guardian_phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số điện thoại người được đặt hộ không hợp lệ', 'error_code' => 'INVALID_GUARDIAN_PHONE']);
        exit;
    }

    $appointment_date = trim($input['appointment_date']);
    if (strtotime($appointment_date) <= strtotime(date('Y-m-d'))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ngày khám phải sau ngày hiện tại', 'error_code' => 'INVALID_DATE']);
        exit;
    }

    $appointment_time = trim($input['appointment_time']);
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $appointment_time)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Giờ hẹn không hợp lệ (HH:MM, 00:00-23:59)', 'error_code' => 'INVALID_TIME']);
        exit;
    }

    // Kiểm tra trùng lịch hẹn
    // $stmt = $conn->prepare("
    //     SELECT COUNT(*) FROM datdichvu 
    //     WHERE id_benhvien = ? AND ngayhen = ? AND giohen = ? AND trangthai NOT IN ('CANCELLED')
    // ");
    // $stmt->execute([$hospital_id, $appointment_date, $appointment_time]);
    // if ($stmt->fetchColumn() > 0) {
    //     http_response_code(409);
    //     echo json_encode(['success' => false, 'message' => 'Lịch hẹn đã được đặt, vui lòng chọn thời gian khác', 'error_code' => 'APPOINTMENT_CONFLICT']);
    //     exit;
    // }

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
    $trangthai ="0";

    // Kiểm tra namsinh hợp lệ
    if ($namsinh && ($namsinh < 1900 || $namsinh > date('Y'))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Năm sinh không hợp lệ', 'error_code' => 'INVALID_BIRTH_YEAR']);
        exit;
    }

    // Lưu vào bảng datdichvu
    // Lưu vào bảng orders
    if ($quanhe_ho!=''){
        $stmt = $conn->prepare("
        INSERT INTO datdichvu (
            id_nguoikham,quanhe_ho,ten_ho,namsinh,gt,sdt_ho,id_benhvien, diemhen, ngayhen, giohen, tinhtrang_nguoikham,trangthai) 
            VALUES ( ?, ?, ?, ?,?,?,?,?,?,?,?,?)
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
    }else{
    $stmt = $conn->prepare("
        INSERT INTO datdichvu (id_nguoikham,gt,namsinh,id_benhvien , diemhen, ngayhen, giohen, tinhtrang_nguoikham,trangthai) VALUES (?,?, ?, ?, ?, ?, ?, ?,?)
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
    echo json_encode(['success' => true, 'message' => 'Đặt dịch vụ thành công']);

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng làm mới token hoặc đăng nhập lại.',
        'error_code' => 'TOKEN_EXPIRED'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server. Vui lòng thử lại sau.',
        'error_code' => 'SERVER_ERROR'
    ]);
}

// Đóng kết nối
$conn = null;
?>