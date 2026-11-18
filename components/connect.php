<?php
$db_config = require __DIR__ . "/db.local.php";
$db_host = $db_config["host"];
$db_port = $db_config["port"];
$db_name = $db_config["name"];
$user_name = $db_config["user"];
$user_password = $db_config["password"];

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
