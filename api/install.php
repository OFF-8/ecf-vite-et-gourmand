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

    foreach (preg_split('/;\s*[\r\n]+/', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }

        if (isCloudEnvironment() && preg_match('/^(CREATE DATABASE|USE)\s/i', $statement)) {
            continue;
        }

        $lastError = null;
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $pdo = installConnect($params, $options);
                $pdo->exec($statement);
                $lastError = null;
                break;
            } catch (PDOException $e) {
                $lastError = $e;
                if (!str_contains($e->getMessage(), 'gone away')
                    && !str_contains($e->getMessage(), 'Lost connection')) {
                    throw $e;
                }
            }
        }

        if ($lastError !== null) {
            throw $lastError;
        }
    }
}

$messages = [];
$erreur = $erreur ?? '';
$diag = [];

if ($params) {
    $diag = [
        'host' => $params['host'],
        'port' => $params['port'],
        'dbname' => $params['dbname'],
        'user' => $params['user'],
        'database_url_set' => env('DATABASE_URL') !== null,
        'tcp_proxy' => str_contains($params['host'], 'proxy.rlwy.net'),
    ];
}

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

        <?php if ($diag): ?>
            <div class="alert alert-warning">
                <strong>Diagnostic connexion</strong>
                <ul class="mb-0">
                    <li>Hôte : <code><?= htmlspecialchars($diag['host']) ?></code></li>
                    <li>Port : <code><?= htmlspecialchars($diag['port']) ?></code></li>
                    <li>Base : <code><?= htmlspecialchars($diag['dbname']) ?></code></li>
                    <li>Utilisateur : <code><?= htmlspecialchars($diag['user']) ?></code></li>
                    <li><code>DATABASE_URL</code> défini : <?= $diag['database_url_set'] ? 'oui' : '<strong>non</strong>' ?></li>
                    <li>TCP Proxy Railway : <?= $diag['tcp_proxy'] ? 'oui' : '<strong>non — activez-le</strong>' ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (($diag['host'] ?? '') === '127.0.0.1'): ?>
            <p><strong>Problème :</strong> l'app se connecte à <code>127.0.0.1</code> (local). Le secret <code>DATABASE_URL</code> n'est pas lu par Fly.</p>
        <?php elseif (!($diag['tcp_proxy'] ?? false)): ?>
            <p><strong>Problème :</strong> l'hôte n'est pas <code>*.proxy.rlwy.net</code>. Activez le <strong>TCP Proxy</strong> sur Railway (Settings → Networking).</p>
        <?php elseif (($diag['dbname'] ?? '') === 'vite_et_gourmand'): ?>
            <p><strong>Problème :</strong> base <code>vite_et_gourmand</code> inexistante sur Railway. Utilisez <code>/railway</code> dans <code>DATABASE_URL</code> et supprimez <code>DB_NAME</code>.</p>
        <?php elseif (($diag['port'] ?? '') === '3306'): ?>
            <p><strong>Problème probable :</strong> port <code>3306</code>. Avec TCP Proxy, Railway utilise un port dédié (ex. <code>18432</code>), pas 3306.</p>
        <?php endif; ?>

        <h2>Vercel / Fly.io</h2>
        <pre>DATABASE_URL=mysql://root:TON_MDP@shuttle.proxy.rlwy.net:18432/railway
DB_SSL=0
(supprimez DB_NAME si présent)</pre>
        <p>La fin de l'URL doit être <code>/railway</code> — pas <code>/vite_et_gourmand</code>.</p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endforeach; ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>index.php">Aller à l'accueil</a>
    <?php endif; ?>
</body>
</html>
