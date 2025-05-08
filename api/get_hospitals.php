<?php
require_once __DIR__ . '/../includes/db_connect.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id_benhvien, ten_benhvien, diachi FROM hospitals ORDER BY ten_benhvien");
    $stmt->execute();
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $hospitals]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}

$conn = null;
?>