<?php

/**
 * Installation BDD — point d'entrée Vercel / Fly.io (api/install.php).
 */
define('APP_ROOT', dirname(__DIR__));

chdir(APP_ROOT);

require_once APP_ROOT . '/config/env.php';
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/db-params.php';

header('Content-Type: text/html; charset=UTF-8');

$installKey = env('INSTALL_KEY', 'vitegourmand2026');
if (($_GET['key'] ?? '') !== $installKey) {
    http_response_code(403);
    exit('<h1>Installation</h1><p>URL : <code>/install.php?key=vitegourmand2026</code></p>');
}

try {
    $params = getDatabaseParams();
} catch (RuntimeException $e) {
    $params = null;
    $erreur = $e->getMessage();
}

$options = getDatabasePdoOptions();
$options[PDO::ATTR_TIMEOUT] = 30;

function installConnect(array $params, array $options): PDO
{
    $dsn = "mysql:host={$params['host']};port={$params['port']};dbname={$params['dbname']};charset=utf8mb4;allowPublicKeyRetrieval=true";

    return new PDO($dsn, $params['user'], $params['password'], $options);
}

function installIsDone(PDO $pdo): bool
{
    return (bool) $pdo->query("SHOW TABLES LIKE 'role'")->fetch();
}

function installRunSql(array $params, array $options, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Fichier introuvable : $path");
    }

    $sql = preg_replace('/^--.*$/m', '', $sql);
    $pdo = installConnect($params, $options);

    foreach (preg_split('/;\s*[\r\n]+/', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }

        if (isCloudEnvironment() && preg_match('/^(CREATE DATABASE|USE)\s/i', $statement)) {
            continue;
        }

        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'gone away')) {
                $pdo = installConnect($params, $options);
                $pdo->exec($statement);
            } else {
                throw $e;
            }
        }
    }
}

$messages = [];
$erreur = $erreur ?? '';

if ($params) {
    try {
        try {
            $pdo = installConnect($params, $options);
            if (installIsDone($pdo)) {
                $messages[] = "La base <strong>{$params['dbname']}</strong> est déjà installée.";
            } else {
                throw new PDOException('Base vide');
            }
        } catch (PDOException) {
            installRunSql($params, $options, APP_ROOT . '/sql/creation_bdd.sql');
            $messages[] = 'Schéma créé dans la base <strong>' . htmlspecialchars($params['dbname']) . '</strong>.';
            installRunSql($params, $options, APP_ROOT . '/sql/fixtures.sql');
            $messages[] = 'Données importées.';
            $messages[] = 'Installation terminée.';
        }
    } catch (Throwable $e) {
        $erreur = $e->getMessage();
    }
}

$baseUrl = getBasePath();
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
        <p>Railway utilise la base <code>railway</code> (pas <code>vite_et_gourmand</code>).</p>
        <p><strong>Fly.io :</strong></p>
        <pre>fly secrets set DATABASE_URL="mysql://root:PASS@xxx.proxy.rlwy.net:PORT/railway"
fly secrets unset DB_NAME
fly deploy</pre>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endforeach; ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>index.php">Aller à l'accueil</a>
    <?php endif; ?>
</body>
</html>
