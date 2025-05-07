
<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require '../vendor/autoload.php';

$secret_key = "your_secret_key"; // để bí mật nha

function createJWT($user_id, $phone) {
    global $secret_key;
    $payload = [
        "iat" => time(),
        "exp" => time() + (60 * 60), // 1 giờ
        "data" => [
            "id" => $user_id,
            "phone" => $phone
        ]
    ];
    return JWT::encode($payload, $secret_key, 'HS256');
}

