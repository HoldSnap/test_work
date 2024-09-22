<?php
global $pdo;

$dsn = 'mysql:host=127.0.0.1;dbname=' . $_ENV['MYSQL_DATABASE'];
$dbUser = $_ENV['MYSQL_USER'];
$dbPassword = $_ENV['MYSQL_PASSWORD'];

$pdo = new PDO($dsn, $dbUser, $dbPassword);
$pdo->exec("SET time_zone = '+03:00'");

$createTableQuery = "
    CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$pdo->exec($createTableQuery);
