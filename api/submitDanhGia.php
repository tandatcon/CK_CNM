<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/JwtHandler.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = getDBConnection();
    $jwtHandler = new JwtHandler($db);
    
    // Xác thực người dùng
    $decoded = $jwtHandler->validateToken();
    $id_khachhang = $jwtHandler->getUserId();

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate
    if (empty($input['id_nhanvien']) || empty($input['sao'])) {
        throw new Exception('Thiếu thông tin bắt buộc', 400);
    }

    // Kiểm tra giá trị sao hợp lệ (1-5)
    if (!is_numeric($input['sao']) || $input['sao'] < 1 || $input['sao'] > 5) {
        throw new Exception('Đánh giá sao phải từ 1 đến 5', 400);
    }

    // Kiểm tra đã đánh giá chưa
    $stmt = $db->prepare("SELECT id FROM danhgia WHERE id_nhanvien = ? AND id_khachhang = ?");
    $stmt->execute([$input['id_nhanvien'], $id_khachhang]);
    
    if ($stmt->fetch()) {
        throw new Exception('Bạn đã đánh giá nhân viên này rồi', 400);
    }

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // Thêm đánh giá mới
        $stmt = $db->prepare("INSERT INTO danhgia (id_nhanvien, id_khachhang, sao, danhgia) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $input['id_nhanvien'],
            $id_khachhang,
            $input['sao'],
            $input['danhgia'] ?? null
        ]);

        // Cập nhật điểm trung bình và số lượt đánh giá trong bảng nhanvien
        $stmt = $db->prepare("
            UPDATE nhanvien 
            SET 
                saoTB = (
                    SELECT AVG(sao) 
                    FROM danhgia 
                    WHERE id_nhanvien = ?
                ),
                luotdanhgia = (
                    SELECT COUNT(id) 
                    FROM danhgia 
                    WHERE id_nhanvien = ?
                )
            WHERE id_user = ?
        ");
        $stmt->execute([
            $input['id_nhanvien'],
            $input['id_nhanvien'],
            $input['id_nhanvien']
        ]);

        // Commit transaction nếu thành công
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá đã được ghi nhận và cập nhật điểm trung bình',
            'data' => [
                'id_nhanvien' => $input['id_nhanvien'],
                'sao' => $input['sao'],
                'danhgia' => $input['danhgia'] ?? null
            ]
        ]);

    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("PDO Error: ".$e->getMessage()); // Ghi log lỗi SQL
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("General Error: ".$e->getMessage()); // Ghi log lỗi tổng
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}