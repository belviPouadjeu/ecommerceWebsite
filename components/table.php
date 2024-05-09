<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$login = "root";
$pass = "";
$dbname = "shop_db";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $login, $pass, $options);

    $queryTable = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(255),  -- Allow NULL values
        remember_token VARCHAR(255) DEFAULT NULL,
        token_expires_at TIMESTAMP NULL DEFAULT NULL
    ) ";

    $conn->exec($queryTable);

    echo "Table created successfully";
} catch(\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
