<?php
require_once __DIR__ . '/db_connect.php';

function queryDB($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Lỗi truy vấn: " . $e->getMessage());
    }
}

function executeDB($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return true;
    } catch (Exception $e) {
        throw new Exception("Lỗi thực thi: " . $e->getMessage());
    }
}
?>