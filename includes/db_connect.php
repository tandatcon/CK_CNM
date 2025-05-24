<?php
    function getDBConnection()
    {
        $host = 'localhost';
        $dbname = 'da_cnm';
        $username = 'root';
        $password = '';

        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $conn;
        } catch (PDOException $e) {
            throw new Exception("Kết nối thất bại: " . $e->getMessage());
        }
    }
?>