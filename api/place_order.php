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
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start(); // Bắt đầu output buffering để tránh lỗi header

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();

    // Kiểm tra cookie access_token
    if (!isset($_COOKIE['access_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu token', 'error_code' => 'MISSING_TOKEN']);
        exit;
    }

    $config = require __DIR__ . '/../includes/jwt_config.php';
    $access_token = $_COOKIE['access_token'];
    $decoded = null;

    try {
        // Thử giải mã access_token
        $decoded = JWT::decode($access_token, new Key($config['secret_key'], 'HS256'));
    } catch (ExpiredException $e) {
        // Access token hết hạn, thử làm mới
        error_log("Access token expired at: " . date('Y-m-d H:i:s', $e->getPayload()->exp));
        $refresh_token = $_COOKIE['refresh_token'] ?? '';
        error_log("Refresh token: " . ($refresh_token ?: "empty"));

        if (empty($refresh_token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Thiếu refresh token', 'error_code' => 'MISSING_REFRESH_TOKEN']);
            exit;
        }

        // Kiểm tra refresh_token trong CSDL
        $stmt = $conn->prepare("SELECT id_user, expires_at FROM refresh_tokens WHERE token = :token AND expires_at > NOW()");
        $stmt->execute(['token' => $refresh_token]);
        $refresh_data = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Refresh data: " . json_encode($refresh_data) . ", NOW: " . date('Y-m-d H:i:s'));

        if (!$refresh_data) {
            error_log("Refresh token not found or expired");
            $stmt = $conn->prepare("DELETE FROM refresh_tokens WHERE token = :token");
            $stmt->execute(['token' => $refresh_token]);
            setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/', 'domain' => 'localhost', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Refresh token không hợp lệ hoặc hết hạn", "error_code" => "INVALID_REFRESH_TOKEN"]);
            exit;
        }

        // Lấy thông tin người dùng từ refresh_data
        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $refresh_data['id_user']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại", "error_code" => "USER_NOT_FOUND"]);
            exit;
        }

        // Tạo access_token mới
        $payload = [
            "iss" => $config['issuer'],
            "aud" => $config['audience'],
            "iat" => time(),
            "exp" => time() + $config['expires_in'],
            "user_id" => $user['id'],
            "phone" => $user['sdt'],
            "name" => $user['name'],
            "role" => $user['role']
        ];
        $new_access_token = JWT::encode($payload, $config['secret_key'], 'HS256');

        // Tạo refresh_token mới
        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600);

        // Cập nhật refresh_token trong CSDL
        $stmt = $conn->prepare("UPDATE refresh_tokens SET token = :token, expires_at = :expires_at WHERE id_user = :user_id");
        $stmt->execute([
            'token' => $new_refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry),
            'user_id' => $user['id']
        ]);

        // Đặt lại cookie
        if (!setcookie('access_token', $new_access_token, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ])) {
            error_log("Failed to set new access_token cookie");
        }
        if (!setcookie('refresh_token', $new_refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ])) {
            error_log("Failed to set new refresh_token cookie");
        }

        // Gán lại decoded với thông tin mới
        $decoded = (object)$payload;
    }

    // Kiểm tra vai trò
    if ($decoded->role !== 0) { // Sửa !== thành != vì role là string trong JWT
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Năm sinh không hợp lệ', 'error_code' => 'INVALID_BIRTH_YEAR']);
        exit;
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
    echo json_encode(['success' => true, 'message' => 'Đặt dịch vụ thành công']);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server. Vui lòng thử lại sau.',
        'error_code' => 'SERVER_ERROR'
    ]);
}

ob_end_flush(); // Kết thúc output buffering
$conn = null;
?>