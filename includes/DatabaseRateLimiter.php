<?php
// File: DatabaseRateLimiter.php

class DatabaseRateLimiter {
    private $db;
    private $defaultLimits = [
        'login' => ['limit' => 5, 'window' => 60],    // 5 lần/phút
        'api' => ['limit' => 100, 'window' => 60],    // 100 lần/phút
        'review' => ['limit' => 3, 'window' => 3600]  // 3 lần/giờ
    ];

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    public function check($identifier, $type = 'api') {
        if (!isset($this->defaultLimits[$type])) {
            throw new InvalidArgumentException("Invalid rate limit type");
        }

        $limit = $this->defaultLimits[$type]['limit'];
        $window = $this->defaultLimits[$type]['window'];
        $key = "{$type}:{$identifier}";
        $hashedKey = hash('sha256', $key);

        // Xóa các bản ghi hết hạn
        $this->cleanupExpired();

        // Kiểm tra và cập nhật rate limit
        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (id, count, expires_at) 
            VALUES (:id, 1, DATE_ADD(NOW(), INTERVAL :window SECOND))
            ON DUPLICATE KEY UPDATE 
            count = IF(expires_at < NOW(), 1, count + 1),
            expires_at = IF(expires_at < NOW(), DATE_ADD(NOW(), INTERVAL :window SECOND), expires_at)
        ");
        
        $stmt->execute([
            ':id' => $hashedKey,
            ':window' => $window
        ]);

        // Lấy số lần hiện tại
        $stmt = $this->db->prepare("SELECT count FROM rate_limits WHERE id = ?");
        $stmt->execute([$hashedKey]);
        $currentCount = $stmt->fetchColumn();

        return [
            'allowed' => $currentCount <= $limit,
            'remaining' => max(0, $limit - $currentCount),
            'retry_after' => $this->getRetryAfter($hashedKey)
        ];
    }

    private function cleanupExpired() {
        // Dọn dẹp các bản ghi hết hạn (chạy 10% số lần để giảm tải database)
        if (rand(1, 10) === 1) {
            $this->db->exec("DELETE FROM rate_limits WHERE expires_at < NOW()");
        }
    }

    private function getRetryAfter($hashedKey) {
        $stmt = $this->db->prepare("
            SELECT TIMESTAMPDIFF(SECOND, NOW(), expires_at) 
            FROM rate_limits 
            WHERE id = ?
        ");
        $stmt->execute([$hashedKey]);
        return $stmt->fetchColumn();
    }
}
?>