<?php
$db_host = '127.0.0.1';
$db_port = 8889; // XAMPP MySQL port from /Applications/XAMPP/xamppfiles/etc/my.cnf
$db_name = 'shop_db';
$user_name = 'root';
$user_password = '';   // XAMPP default is empty

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user_name, $user_password, $options);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Unable to connect to the database. Please make sure XAMPP MySQL is running.");
}
?>
