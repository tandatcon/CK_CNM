<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();

    // Nhận dữ liệu từ request POST
    $input = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra dữ liệu đầu vào
    if (!$input || !isset($input['token'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu đầu vào không hợp lệ hoặc thiếu token']);
        exit;
    }

    // Giải mã JWT
    $secret_key = 'cabaymaublutopaz'; // Phải khớp với khóa trong login.php
    $decoded = JWT::decode($input['token'], new Key($secret_key, 'HS256'));

    // Kiểm tra vai trò
    if ($decoded->role !== 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Không phải khách hàng']);
        exit;
    }

    // Lấy user_id từ JWT
    $user_id = $decoded->user_id;

    // Kiểm tra user_id có tồn tại trong bảng user
    $stmt = $conn->prepare("SELECT id FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
        exit;
    }

    // Kiểm tra các trường bắt buộc
    $required_fields = ['full_name', 'phone', 'diemhen', 'hospital_id', 'appointment_date', 'appointment_time', 'condition'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Trường $field là bắt buộc"]);
            exit;
        }
    }

    // Kiểm tra hospital_id có tồn tại
    $hospital_id = $input['hospital_id'];
    $stmt = $conn->prepare("SELECT id_benhvien FROM hospitals WHERE id_benhvien = ?");
    $stmt->execute([$hospital_id]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bệnh viện không tồn tại']);
        exit;
    }

    // Kiểm tra định dạng phone
    if (!preg_match('/^0\d{9}$/', $input['phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
        exit;
    }

    // Kiểm tra guardian_phone (nếu có)
    if (!empty($input['guardian_phone']) && !preg_match('/^0\d{9}$/', $input['guardian_phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số điện thoại người được đặt hộ không hợp lệ']);
        exit;
    }

    // Kiểm tra appointment_date
    $appointment_date = $input['appointment_date'];
    if (strtotime($appointment_date) <= strtotime(date('Y-m-d'))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ngày khám phải sau ngày hiện tại']);
        exit;
    }

    // Chuẩn bị dữ liệu để lưu
    $full_name = trim($input['full_name']);
    $phone = trim($input['phone']);
    $diemhen = trim($input['diemhen']);
    $appointment_time = $input['appointment_time'];
    $condition = trim($input['condition']);
    $guardian_relation = isset($input['guardian_relation']) ? trim($input['guardian_relation']) : '';
    $guardian_name = isset($input['guardian_name']) ? trim($input['guardian_name']) : '';
    $guardian_phone = isset($input['guardian_phone']) ? trim($input['guardian_phone']) : '';

    // Lưu vào bảng orders
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, full_name, phone, diemhen, hospital_id, appointment_date, appointment_time, 
            condition, guardian_relation, guardian_name, guardian_phone
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $full_name,
        $phone,
        $diemhen,
        $hospital_id,
        $appointment_date,
        $appointment_time,
        $condition,
        $guardian_relation,
        $guardian_name,
        $guardian_phone
    ]);

    // Trả về phản hồi thành công
    echo json_encode(['success' => true, 'message' => 'Đặt dịch vụ thành công']);

} catch (Exception $e) {
    http_response_code(500);
    error_log('JWT Error: ' . $e->getMessage()); // Ghi log vào php_error_log
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc lỗi server: ' . $e->getMessage()]);
}

// Đóng kết nối
$conn = null;
?>