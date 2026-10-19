<?php
// Connect PHP to the MySQL database "shoptrack"
$host = 'localhost';
$db   = 'shoptrack';
$user = 'root';   // XAMPP default user
$pass = '';       // XAMPP default password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
