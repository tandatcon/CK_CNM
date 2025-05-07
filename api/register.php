<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';
$full_name = $data['full_name'] ?? '';
$role = $data['role'] ?? 'user';

if (empty($phone) || empty($password) || empty($full_name)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ số điện thoại, mật khẩu và tên"]);
    exit;
}

$phoneRegex = '/^0\d{9}$/';
if (!preg_match($phoneRegex, $phone)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Số điện thoại không hợp lệ! Phải có 10 số, bắt đầu bằng 0."]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Mật khẩu phải có ít nhất 6 ký tự"]);
    exit;
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id FROM user WHERE sdt = :sdt");
    $stmt->execute(['sdt' => $phone]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Số điện thoại đã được đăng ký"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (sdt, pw, name, role) VALUES (:sdt, :pw, :name, :role)");
    $stmt->execute([
        'sdt' => $phone,
        'pw' => $hashedPassword,
        'name' => $full_name,
        'role' => $role
    ]);

    echo json_encode(["success" => true, "message" => "Đăng ký thành công"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
}

$conn = null;
?>