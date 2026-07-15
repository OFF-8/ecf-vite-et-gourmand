<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/db-params.php';

session_start();

try {
    $params = getDatabaseParams();
    $pdo = new PDO(
        buildDatabaseDsn(),
        $params['user'],
        $params['password'],
        getDatabasePdoOptions()
    );
} catch (RuntimeException $e) {
    die($e->getMessage());
} catch (PDOException $e) {
    $msg = $e->getMessage();
    if (str_contains($msg, 'timed out')) {
        die('Erreur MySQL : timeout. Railway → URL avec proxy.rlwy.net');
    }
    if (str_contains($msg, 'No such file or directory') || str_contains($msg, 'getaddrinfo')) {
        die('Erreur MySQL : DATABASE_URL invalide. Utilisez proxy.rlwy.net (pas railway.internal).');
    }
    if (str_contains($msg, 'Access denied')) {
        die('Erreur MySQL : mot de passe incorrect. Copiez MYSQL_PUBLIC_URL depuis Railway (Connect → Public Network).');
    }
    if (str_contains($msg, 'Unknown database')) {
        die('Erreur MySQL : base inexistante. DATABASE_URL doit finir par /railway');
    }
    die('Erreur de connexion à la base de données : ' . $msg);
}
