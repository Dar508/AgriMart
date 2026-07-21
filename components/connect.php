<?php

// MAMP Unix Socket Configuration
$db_host = 'localhost';
$db_name = 'shop_db';
$user_name = 'root';
$user_password = 'root'; 
$socket_path = '/Applications/MAMP/tmp/mysql/mysql.sock';

// DSN string using Unix socket
$dsn = "mysql:unix_socket={$socket_path};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on DB errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    $conn = new PDO($dsn, $user_name, $user_password, $options);
} catch (PDOException $e) {
    // Hide raw database details from public error screens in production
    error_log("Database Connection Error: " . $e->getMessage());
    die("Unable to connect to the database. Please make sure MAMP MySQL server is running.");
}

?>