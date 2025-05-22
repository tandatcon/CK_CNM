<?php
putenv('APP_ENV=development');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start();

try {
    // Kết nối cơ sở dữ liệu
    $conn = getDBConnection();

    // Lấy danh sách đơn chưa phân công (3 ngày tiếp theo)
    $stmt = $conn->prepare("SELECT id, id_benhvien, ngayhen, giobatdau, gioketthuc,giohen
                          FROM datdichvu
                          WHERE ngayhen BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 3 DAY
                          AND trangthai = '0'");
    $stmt->execute();
    $dondichvus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chuẩn bị dữ liệu trả về
    $lich_chua_pc = array_map(function($don) {
        return [
            'id_don' => $don['id'],
            'id_benhvien' => $don['id_benhvien'],
            'ngayhen' => $don['ngayhen'],
            'giobatdau' => $don['giobatdau'],
            'gioketthuc' => $don['gioketthuc'],
            'giohen' => $don['giohen']
        ];
    }, $dondichvus);

    // Trả về kết quả
    echo json_encode([
        'success' => true,
        'message' => 'Lấy danh sách đơn chưa phân công thành công',
        'data' => [
            'lich_chua_pc' => $lich_chua_pc
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Lỗi máy chủ';
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg, 'error_code' => 'SERVER_ERROR']);
}

ob_end_flush();
$conn = null;