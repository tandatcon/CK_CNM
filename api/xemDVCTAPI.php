<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Nhận token và id từ URL
$token = $_GET['token'] ?? '';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu token']);
    exit;
}

if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ']);
    exit;
}

$secret_key = 'cabaymaublutopaz'; // Nên lấy từ jwt_config.php

try {
    // Giải mã token
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    // Kiểm tra vai trò
    if (!isset($decoded->role) || $decoded->role !== 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }

    // Truy vấn chi tiết đơn hàng
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT a.id, a.id_nguoikham, a.namsinh, a.gt, a.id_benhvien, b.ten_benhvien, 
               a.diemhen, a.ngayhen, a.giohen, a.tinhtrang_nguoikham, a.tongchiphi, a.trangthai,a.loai,a.quanhe_ho, a.ten_ho, a.sdt_ho,c.name,c.sdt,a.lydo_tuchoi
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        JOIN user c on a.id_nguoikham=c.id
        WHERE a.id = :id AND a.id_nguoikham = :user_id
    ");
    $stmt->execute(['id' => $order_id, 'user_id' => $decoded->user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        echo json_encode(['success' => true, 'data' => $order]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc bạn không có quyền xem']);
    }

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!',
        'error_code' => 'TOKEN_EXPIRED'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    $msg = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Token không hợp lệ hoặc lỗi máy chủ';
    error_log('JWT Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $msg]);
}