<?php

// Bypassing network ports completely by using the direct MAMP Unix Socket
$db_name = 'mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=shop_db;charset=utf8mb4';
$user_name = 'root';
$user_password = 'root'; 

try {
    $conn = new PDO($db_name, $user_name, $user_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>