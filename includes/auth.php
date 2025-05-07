<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyJWT() {
    $config = require __DIR__ . '/jwt_config.php';
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return false;
    }

    $jwt = $matches[1];
    try {
        JWT::decode($jwt, new Key($config['secret_key'], 'HS256'));
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>