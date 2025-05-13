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

// Nhận token từ URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu token']);
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

    // Truy vấn danh sách đơn hoàn tất (trangthai = 4)
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT a.id, a.id_nguoikham, a.namsinh, a.gt, a.id_benhvien, b.ten_benhvien, 
               a.diemhen, a.ngayhen, a.giohen, a.tinhtrang_nguoikham, a.tongchiphi, a.trangthai
        FROM datdichvu a 
        JOIN hospitals b ON a.id_benhvien = b.id_benhvien 
        WHERE a.id_nguoikham = :user_id AND a.trangthai = :trangthai
    ");
    $stmt->execute(['user_id' => $decoded->user_id, 'trangthai' => 4]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($orders && count($orders) > 0) {
        echo json_encode(['success' => true, 'data' => $orders]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không có đơn dịch vụ đã hoàn tất']);
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