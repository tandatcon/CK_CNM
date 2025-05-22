<?php
putenv('APP_ENV=development');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once __DIR__ . '/../includes/JwtHandler.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();

try {
    $conn = getDBConnection();
    $jwtHandler = new JwtHandler($conn);

    try {
        // Xác thực token - tự động xử lý refresh token nếu cần
        $decoded = $jwtHandler->validateToken();
        
        // Kiểm tra quyền admin (role = 1)
        if ($jwtHandler->getUserRole() != 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
            exit;
        }

        // Lấy dữ liệu đầu vào
        $data = json_decode(file_get_contents("php://input"), true);
        $orderId = $data['id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số ID đơn hàng', 'error_code' => 'MISSING_ORDER_ID']);
            exit;
        }

        // Xử lý thời gian
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $currentTime = date("H:i");

        // Kiểm tra đơn hàng
        $stmt = $conn->prepare("SELECT giobatdau FROM datdichvu 
                              WHERE id = :id AND id_nhanvien = :user_id 
                              AND trangthai IN (1, 2) AND ngayhen = CURDATE()");
        $stmt->execute(['id' => $orderId, 'user_id' => $jwtHandler->getUserId()]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || !$order['giobatdau']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Chưa bắt đầu dịch vụ hoặc đơn hàng không tồn tại', 'error_code' => 'NO_START_TIME']);
            exit;
        }

        // Tính toán thời gian và chi phí (giữ nguyên logic gốc)
        list($startHour, $startMinute) = explode(':', $order['giobatdau']);
        list($endHour, $endMinute) = explode(':', $currentTime);

        $startMinutes = ($startHour * 60) + $startMinute;
        $endMinutes = ($endHour * 60) + $endMinute;
        $totalMinutes = $endMinutes - $startMinutes;

        if ($totalMinutes < 0) {
            $totalMinutes += 24 * 60;
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        $priceFirst6HoursPerMinute = 25000 / 60;
        $priceAfter6HoursPerMinute = 20000 / 60;
        
        if ($totalMinutes <= 6 * 60) {
            $tongtien = $totalMinutes * $priceFirst6HoursPerMinute;
        } else {
            $tongtien = (6 * 60 * $priceFirst6HoursPerMinute) + (($totalMinutes - 6 * 60) * $priceAfter6HoursPerMinute);
        }
        $tongtien = round($tongtien);

        // Cập nhật CSDL
        $stmt = $conn->prepare("UPDATE datdichvu 
                              SET gioketthuc = :gioketthuc, tongchiphi = :tongtien, trangthai = 3 
                              WHERE id = :id AND id_nhanvien = :user_id 
                              AND trangthai IN (1, 2) AND ngayhen = CURDATE()");
        $stmt->execute([
            'id' => $orderId,
            'user_id' => $jwtHandler->getUserId(),
            'gioketthuc' => $currentTime,
            'tongtien' => $tongtien
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Cập nhật giờ kết thúc thành công: $currentTime, Tổng tiền: " . number_format($tongtien) . " VND",
                'totalTime' => "$totalHours giờ $remainingMinutes phút",
                'totalCost' => $tongtien
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không thể kết thúc dịch vụ hoặc đơn hàng không tồn tại', 'error_code' => 'NO_AFFECTED_ROWS']);
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