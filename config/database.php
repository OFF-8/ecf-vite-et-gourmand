<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/app.php';

session_start();

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'vite_et_gourmand');
$user = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données.');
}
