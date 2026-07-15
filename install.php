<?php

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/app.php';

header('Content-Type: text/html; charset=UTF-8');

$installKey = env('INSTALL_KEY', 'vitegourmand2026');
if (($_GET['key'] ?? '') !== $installKey) {
    http_response_code(403);
    exit('<h1>Installation</h1><p>Clé requise : <code>/install.php?key=...</code></p>');
}

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'vite_et_gourmand');
$user = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');

$databaseUrl = env('DATABASE_URL') ?? env('MYSQL_PUBLIC_URL') ?? env('MYSQL_URL');
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if (is_array($parts)) {
        $host = $parts['host'] ?? $host;
        $port = (string) ($parts['port'] ?? $port);
        $user = $parts['user'] ?? $user;
        $password = $parts['pass'] ?? $password;
    }
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$isLocal = in_array($host, ['localhost', '127.0.0.1', 'db'], true);
if (!$isLocal || env('VERCEL') === '1') {
    if (PHP_VERSION_ID >= 80400) {
        $options[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
    } elseif (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

function connectPdo(string $host, string $port, string $user, string $password, ?string $dbname, array $options): PDO
{
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    if ($dbname) {
        $dsn .= ";dbname=$dbname";
    }

    return new PDO($dsn, $user, $password, $options);
}

function isInstalled(PDO $pdo): bool
{
    $stmt = $pdo->query("SHOW TABLES LIKE 'role'");

    return (bool) $stmt->fetch();
}

function runSqlFile(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Fichier introuvable : $path");
    }

    $sql = preg_replace('/^--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', $sql)));

    foreach ($statements as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

$messages = [];
$erreur = '';

try {
    try {
        $pdo = connectPdo($host, $port, $user, $password, $dbname, $options);
        if (isInstalled($pdo)) {
            $messages[] = "La base <strong>$dbname</strong> est déjà installée.";
        } else {
            throw new PDOException('Base vide');
        }
    } catch (PDOException) {
        $pdo = connectPdo($host, $port, $user, $password, null, $options);
        runSqlFile($pdo, __DIR__ . '/sql/creation_bdd.sql');
        $messages[] = 'Schéma créé (creation_bdd.sql).';
        $pdo = connectPdo($host, $port, $user, $password, $dbname, $options);
        runSqlFile($pdo, __DIR__ . '/sql/fixtures.sql');
        $messages[] = 'Données importées (fixtures.sql).';
        $messages[] = 'Installation terminée.';
    }
} catch (Throwable $e) {
    $erreur = $e->getMessage();
}

$baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation — Vite &amp; Gourmand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1>Installation de la base de données</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <p>Vérifiez sur Vercel : <code>DATABASE_URL</code>, <code>DB_NAME=vite_et_gourmand</code>, <code>DB_SSL=1</code></p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endforeach; ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>index.php">Aller à l'accueil</a>
    <?php endif; ?>
</body>
</html>
