<?php
putenv('APP_ENV=development');

require_once __DIR__ . '/../includes/auth.php';
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
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();

try {
    $conn = getDBConnection();

    if (!isset($_COOKIE['access_token'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập hoặc thiếu token', 'error_code' => 'MISSING_TOKEN']);
        exit;
    }

    $config = require __DIR__ . '/../includes/jwt_config.php';
    $access_token = $_COOKIE['access_token'];
    $decoded = null;

    try {
        $decoded = JWT::decode($access_token, new Key($config['secret_key'], 'HS256'));
    } catch (ExpiredException $e) {
        error_log("Access token expired at: " . date('Y-m-d H:i:s', $e->getPayload()->exp));
        $refresh_token = $_COOKIE['refresh_token'] ?? '';
        error_log("Refresh token: " . ($refresh_token ?: "empty"));

        if (empty($refresh_token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Thiếu refresh token', 'error_code' => 'MISSING_REFRESH_TOKEN']);
            exit;
        }

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

        $stmt = $conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = :id");
        $stmt->execute(['id' => $refresh_data['id_user']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại", "error_code" => "USER_NOT_FOUND"]);
            exit;
        }

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
        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600);

        $stmt = $conn->prepare("UPDATE refresh_tokens SET token = :token, expires_at = :expires_at WHERE id_user = :user_id");
        $stmt->execute([
            'token' => $new_refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $refresh_expiry),
            'user_id' => $user['id']
        ]);

        setcookie('access_token', $new_access_token, [
            'expires' => time() + $config['expires_in'],
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('refresh_token', $new_refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        $decoded = (object) $payload;
    }

    if (!isset($decoded->role) || $decoded->role != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
        exit;
    }

    // Lấy id đơn hàng từ request
    $data = json_decode(file_get_contents("php://input"), true);
    $orderId = $data['id'] ?? null;

    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu tham số ID đơn hàng', 'error_code' => 'MISSING_ORDER_ID']);
        exit;
    }

    // Đặt múi giờ Việt Nam
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $gioketthuc = date("H:i"); // Giờ hiện tại (01:30 sáng 18/05/2025)

    // Lấy giobatdau từ CSDL
    $stmt = $conn->prepare("SELECT giobatdau FROM datdichvu WHERE id = :id AND id_nhanvien = :user_id AND trangthai IN (1, 2) AND ngayhen = CURDATE()");
    $stmt->execute(['id' => $orderId, 'user_id' => $decoded->user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || !$order['giobatdau']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Chưa bắt đầu dịch vụ hoặc đơn hàng không tồn tại', 'error_code' => 'NO_START_TIME']);
        exit;
    }

    $giobatdau = $order['giobatdau'];

    // Tính tổng thời gian (chuyển thành phút), định dạng HH:mm
    list($startHour, $startMinute) = explode(':', $giobatdau);
    list($endHour, $endMinute) = explode(':', $gioketthuc);

    $startMinutes = ($startHour * 60) + $startMinute;
    $endMinutes = ($endHour * 60) + $endMinute;
    $totalMinutes = $endMinutes - $startMinutes;

    // Xử lý nếu qua ngày
    if ($totalMinutes < 0) {
        $totalMinutes += 24 * 60; // Cộng 24 giờ nếu kết thúc vào ngày hôm sau
    }

    // Tính giờ và phút chính xác (không làm tròn)
    $totalHours = floor($totalMinutes / 60); // Lấy nguyên giờ
    $remainingMinutes = $totalMinutes % 60;  // Lấy phút còn lại
    $totalTimeDecimal = $totalHours + ($remainingMinutes / 60); // Thời gian dưới dạng thập phân (giờ)

    // Tính tổng tiền dựa trên phút
    $priceFirst6HoursPerMinute = 25000 / 60; // 416,67 VND/phút cho 6 giờ đầu
    $priceAfter6HoursPerMinute = 23000 / 60; // 383,33 VND/phút sau 6 giờ
    $tongtien = 0;

    if ($totalMinutes <= 6 * 60) {
        $tongtien = $totalMinutes * $priceFirst6HoursPerMinute;
    } else {
        $tongtien = (6 * 60 * $priceFirst6HoursPerMinute) + (($totalMinutes - 6 * 60) * $priceAfter6HoursPerMinute);
    }

    // Làm tròn tổng tiền về số nguyên
    $tongtien = round($tongtien);

    // Cập nhật gioketthuc và tongtien
    $stmt = $conn->prepare("UPDATE datdichvu SET gioketthuc = :gioketthuc, tongchiphi = :tongtien, trangthai = 3 WHERE id = :id AND id_nhanvien = :user_id AND trangthai IN (1, 2) AND ngayhen = CURDATE()");
    $stmt->execute([
        'id' => $orderId,
        'user_id' => $decoded->user_id,
        'gioketthuc' => $gioketthuc,
        'tongtien' => $tongtien
    ]);
    $affectedRows = $stmt->rowCount();

    if ($affectedRows > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Cập nhật giờ kết thúc thành công: $gioketthuc, Tổng tiền: " . number_format($tongtien) . " VND",
            'totalTime' => "$totalHours giờ $remainingMinutes phút",
            'totalCost' => $tongtien
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể kết thúc dịch vụ hoặc đơn hàng không tồn tại', 'error_code' => 'NO_AFFECTED_ROWS']);
    }

} catch (Exception $e) {
    http_response_code(500);
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Token không hợp lệ hoặc lỗi máy chủ';
    error_log('JWT Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg, 'error_code' => 'SERVER_ERROR']);
}

ob_end_flush();
$conn = null;
?>