<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtHandler {
    private $conn;
    private $config;
    private $access_token;
    private $refresh_token;
    private $decoded;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->config = require __DIR__ . '/../includes/jwt_config.php';
        $this->access_token = $_COOKIE['access_token'] ?? '';
        $this->refresh_token = $_COOKIE['refresh_token'] ?? '';
    }

    public function validateToken() {
        try {
            if ($this->access_token) {
                $this->decoded = JWT::decode($this->access_token, new Key($this->config['secret_key'], 'HS256'));
            } elseif ($this->refresh_token) {
                $this->decoded = $this->refreshTokens();
            } else {
                throw new Exception('Chưa đăng nhập hoặc thiếu token', 401);
            }
        } catch (ExpiredException $e) {
            if ($this->refresh_token) {
                $this->decoded = $this->refreshTokens();
            } else {
                throw new Exception('Phiên đăng nhập hết hạn', 401);
            }
        } catch (SignatureInvalidException $e) {
            $this->clearTokens();
            throw new Exception('Token không hợp lệ', 401);
        } catch (Exception $e) {
            throw new Exception('Lỗi xác thực: ' . $e->getMessage(), 401);
        }

        return $this->decoded;
    }

    private function refreshTokens() {
        $stmt = $this->conn->prepare("SELECT id_user, expires_at FROM refresh_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$this->refresh_token]);
        $refresh_data = $stmt->fetch();

        if (!$refresh_data) {
            $this->clearTokens();
            throw new Exception('Refresh token không hợp lệ hoặc hết hạn', 401);
        }

        $stmt = $this->conn->prepare("SELECT id, sdt, name, role FROM user WHERE id = ?");
        $stmt->execute([$refresh_data['id_user']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Người dùng không tồn tại', 404);
        }

        $payload = [
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'iat' => time(),
            'exp' => time() + $this->config['expires_in'],
            'user_id' => $user['id'],
            'phone' => $user['sdt'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        
        $new_access_token = JWT::encode($payload, $this->config['secret_key'], 'HS256');
        $new_refresh_token = bin2hex(random_bytes(32));
        $refresh_expiry = time() + (30 * 24 * 3600);

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("UPDATE refresh_tokens SET token = ?, expires_at = ? WHERE id_user = ?");
            $stmt->execute([$new_refresh_token, date('Y-m-d H:i:s', $refresh_expiry), $user['id']]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        $this->setCookies($new_access_token, $new_refresh_token, $refresh_expiry);
        return (object)$payload;
    }

    private function setCookies($access_token, $refresh_token, $refresh_expiry) {
        setcookie('access_token', $access_token, [
            'expires' => time() + $this->config['expires_in'],
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        setcookie('refresh_token', $refresh_token, [
            'expires' => $refresh_expiry,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private function clearTokens() {
        setcookie('access_token', '', ['expires' => time() - 3600, 'path' => '/']);
        setcookie('refresh_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }

    public function getUserId() {
        return $this->decoded->user_id ?? null;
    }

    public function getUserRole() {
        return $this->decoded->role ?? null;
    }
}
?>