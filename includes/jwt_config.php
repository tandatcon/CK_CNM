<?php
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    
    return [
        'secret_key' => $_ENV['SECRET_KEY'],
        'issuer' => 'your_domain',
        'audience' => 'your_domain',
        'expires_in' => 20
    ];
    

?>