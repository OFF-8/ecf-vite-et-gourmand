<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/app.php';

session_start();

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'vite_et_gourmand');
$user = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$useSsl = env('DB_SSL', 'auto');
$isLocal = in_array($host, ['localhost', '127.0.0.1', 'db'], true);

if ($useSsl === '1' || ($useSsl === 'auto' && !$isLocal && env('VERCEL') === '1')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        $options
    );
} catch (PDOException $e) {
    if (env('VERCEL') === '1' && $host === 'localhost') {
        die('Erreur de connexion : configurez DB_HOST, DB_USER, DB_PASSWORD sur Vercel (Settings → Environment Variables).');
    }
    die('Erreur de connexion à la base de données.');
}
