<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'da_cnm';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(array(
        "success" => false,
        "message" => "Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage()
    ));
    exit;
}

// Lấy token từ header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Token không được cung cấp"
    ));
    http_response_code(401);
    exit;
}

$jwt = $matches[1];
$key = "your_secret_key";

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

    // Kiểm tra vai trò
    if (!in_array($decoded->role, ['user', 'admin'])) {
        echo json_encode(array(
            "success" => false,
            "message" => "Bạn không có quyền đặt dịch vụ"
        ));
        http_response_code(403); // Forbidden
        exit;
    }

    // Lấy dữ liệu từ POST request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $service_id = isset($data['service_id']) ? $data['service_id'] : '';
    $booking_date = isset($data['booking_date']) ? $data['booking_date'] : '';

    if (empty($service_id) || empty($booking_date)) {
        echo json_encode(array(
            "success" => false,
            "message" => "Thông tin dịch vụ hoặc ngày đặt không được để trống"
        ));
        exit;
    }

    // Lưu đơn đặt dịch vụ vào cơ sở dữ liệu
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, booking_date) VALUES (:user_id, :service_id, :booking_date)");
    $stmt->execute([
        'user_id' => $decoded->user_id,
        'service_id' => $service_id,
        'booking_date' => $booking_date
    ]);

    echo json_encode(array(
        "success" => true,
        "message" => "Đặt dịch vụ thành công"
    ));

} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => "Token không hợp lệ: " . $e->getMessage()
    ));
    http_response_code(401);
    exit;
}

$conn = null;
?>