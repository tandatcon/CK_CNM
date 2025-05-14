<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$token = $_COOKIE['access_token'] ?? '';

if (!$token) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

try {
    $config = require __DIR__ . '/../includes/jwt_config.php';
    $decoded = JWT::decode($token, new Key($config['secret_key'], 'HS256'));
    
    echo json_encode([
        'loggedIn' => true,
        'full_name' => $decoded->full_name,
        'role' => $decoded->role
    ]);
} catch (Exception $e) {
    echo json_encode(['loggedIn' => false]);
}
?>
