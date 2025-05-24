<?php
putenv('APP_ENV=development');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';
require_once __DIR__ . '/../includes/JwtHandler.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

ob_start();
$csrf = new CsrfMiddleware;
try {
    // Kết nối CSDL và khởi tạo JwtHandler
    $conn = getDBConnection();
    $csrf->verifyToken();
    $jwtHandler = new JwtHandler($conn);

    try {
        // Xác thực token
        $decoded = $jwtHandler->validateToken();
        
        // Kiểm tra quyền (role = 2)
        if ($jwtHandler->getUserRole() != 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập', 'error_code' => 'FORBIDDEN']);
            exit;
        }

        // 1. Lấy danh sách đơn chưa phân công (3 ngày tiếp theo)
        $stmt = $conn->prepare("SELECT id, id_benhvien, ngayhen, giohen 
                              FROM datdichvu 
                              WHERE ngayhen BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 3 DAY
                              AND trangthai = '0'");
        $stmt->execute();
        $dondichvus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [
            'lich_phan_cong' => [],
            'don_khong_phan_cong' => [],
            'lich_chua_pc' => []
        ];

        foreach ($dondichvus as $don) {
            $results['lich_chua_pc'][] = [
                'id_don' => $don['id'],
                'id_benhvien' => $don['id_benhvien'],
                'ngayhen' => $don['ngayhen'],
                'giohen' => $don['giohen']
            ];

            // 2. Tìm nhân viên phù hợp (có kinh nghiệm BV và chưa có lịch vào ngày này)
            $stmt = $conn->prepare("SELECT id, hoten 
                                  FROM nhanvien 
                                  WHERE knBV = :benhvien_id
                                  AND id NOT IN (
                                      SELECT id_nhanvien 
                                      FROM datdichvu 
                                      WHERE ngayhen = :ngayhen
                                  )
                                  LIMIT 1");
            $stmt->execute([
                'benhvien_id' => $don['id_benhvien'],
                'ngayhen' => $don['ngayhen']
            ]);
            $nhanvien = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($nhanvien) {
                // 3. Phân công cho nhân viên
                $stmt = $conn->prepare("UPDATE datdichvu 
                                      SET id_nhanvien = :nhanvien_id, trangthai = '1' 
                                      WHERE id = :don_id");
                $stmt->execute([
                    'nhanvien_id' => $nhanvien['id'],
                    'don_id' => $don['id']
                ]);

                $results['lich_phan_cong'][] = [
                    'id_don' => $don['id'],
                    'id_nhanvien' => $nhanvien['id'],
                    'ten_nhanvien' => $nhanvien['hoten'],
                    'id_benhvien' => $don['id_benhvien'],
                    'ngayhen' => $don['ngayhen']
                ];
            } else {
                $results['don_khong_phan_cong'][] = [
                    'id_don' => $don['id'],
                    'id_benhvien' => $don['id_benhvien'],
                    'ngayhen' => $don['ngayhen']
                ];
            }
        }

        // Trả về kết quả
        echo json_encode([
            'success' => true,
            'message' => 'Xếp lịch thành công',
            'data' => $results
        ]);

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
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg, 'error_code' => 'SERVER_ERROR']);
}

ob_end_flush();
$conn = null;