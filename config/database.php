<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/app.php';

session_start();

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'vite_et_gourmand');
$user = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');

// Railway / hébergeurs cloud : URL unique (prioritaire)
$databaseUrl = env('DATABASE_URL') ?? env('MYSQL_PUBLIC_URL') ?? env('MYSQL_URL');
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if (is_array($parts)) {
        $host = $parts['host'] ?? $host;
        $port = (string) ($parts['port'] ?? $port);
        $dbname = ltrim($parts['path'] ?? '', '/') ?: $dbname;
        $user = $parts['user'] ?? $user;
        $password = $parts['pass'] ?? $password;
    }
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$useSsl = env('DB_SSL', 'auto');
$isLocal = in_array($host, ['localhost', '127.0.0.1', 'db'], true);

if ($useSsl !== '0' && ($useSsl === '1' || ($useSsl === 'auto' && !$isLocal))) {
    if (PHP_VERSION_ID >= 80400) {
        $options[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
    } elseif (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        $options
    );
} catch (PDOException $e) {
    if (env('VERCEL') === '1' && ($host === 'localhost' || !env('DB_HOST') && !$databaseUrl)) {
        die('Erreur de connexion : ajoutez DATABASE_URL ou DB_HOST sur Vercel (Settings → Environment Variables).');
    }
    die('Erreur de connexion à la base de données.');
}
